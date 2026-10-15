<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\LayawayStatus;
use App\Enums\ProductStatus;
use App\Enums\SaleStatus;
use App\Models\CashSession;
use App\Models\Layaway;
use App\Models\LayawayPayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Owns the layaway lifecycle: reserving a piece, collecting instalments and
 * delivering (or cancelling) the reservation.
 *
 * Instalments are stored as immutable `layaway_payments` rows tied to the
 * cash shift that received them. None of the amounts are trusted from the
 * client: totals and balances are always recomputed server-side.
 * See docs/05-esquema-bd.md §8 and docs/03-flujo-app.md §5.4.
 */
class LayawayService
{
    public function __construct(
        protected DocumentSequenceService $sequences,
        protected InventoryService $inventory,
    ) {}

    /**
     * Open a layaway, reserving the piece and registering the initial instalment.
     *
     * @param  array{product_id: int, customer_id: int, due_date: string, initial_amount?: string, initial_method?: string, reference?: string}  $data
     */
    public function create(User $user, CashSession $session, array $data): Layaway
    {
        return DB::transaction(function () use ($user, $session, $data): Layaway {
            $product = Product::withoutGlobalScopes()
                ->where('store_id', $session->store_id)
                ->with(['metal', 'purity'])
                ->lockForUpdate()
                ->find((int) $data['product_id']);

            if ($product === null) {
                throw new InvalidArgumentException('La pieza indicada no existe.');
            }

            if ($product->status !== ProductStatus::Available) {
                throw new RuntimeException("La pieza '{$product->name}' no está disponible para apartar.");
            }

            $total = $this->money($product->price);

            if (bccomp($total, '0', 2) <= 0) {
                throw new InvalidArgumentException("La pieza '{$product->name}' no tiene un precio válido.");
            }

            $initial = $this->money($data['initial_amount'] ?? '0');

            if (bccomp($initial, '0', 2) < 0) {
                throw new InvalidArgumentException('El abono inicial no puede ser negativo.');
            }

            if (bccomp($initial, $total, 2) > 0) {
                throw new InvalidArgumentException('El abono inicial no puede superar el precio total.');
            }

            $layaway = Layaway::create([
                'store_id' => $session->store_id,
                'number' => $this->sequences->nextNumber($session->store_id, DocumentType::Layaway),
                'customer_id' => (int) $data['customer_id'],
                'product_id' => $product->id,
                'user_id' => $user->id,
                'total_price' => $total,
                'paid_total' => $initial,
                'balance' => bcsub($total, $initial, 2),
                'due_date' => $data['due_date'],
                'status' => LayawayStatus::Active,
            ]);

            $this->inventory->reserveForLayaway($product, $layaway, $user->id);

            if (bccomp($initial, '0', 2) > 0) {
                $this->recordPayment($layaway, $session, $user, $data['initial_method'], $initial, $data['reference'] ?? null);
            }

            return $layaway->refresh();
        });
    }

    /**
     * Register an instalment and recompute the outstanding balance.
     *
     * @param  array{method: string, amount: string, reference?: string|null}  $data
     */
    public function addPayment(Layaway $layaway, CashSession $session, User $user, array $data): Layaway
    {
        return DB::transaction(function () use ($layaway, $session, $user, $data): Layaway {
            $locked = Layaway::withoutGlobalScopes()->lockForUpdate()->find($layaway->id);

            if ($locked === null || ! $locked->isActive()) {
                throw new RuntimeException('El apartado no está activo.');
            }

            $amount = $this->money($data['amount']);

            if (bccomp($amount, '0', 2) <= 0) {
                throw new InvalidArgumentException('El abono debe ser mayor a cero.');
            }

            if (bccomp($amount, (string) $locked->balance, 2) > 0) {
                throw new InvalidArgumentException('El abono no puede superar el saldo pendiente.');
            }

            $this->recordPayment($locked, $session, $user, $data['method'], $amount, $data['reference'] ?? null);

            $paid = bcadd((string) $locked->paid_total, $amount, 2);
            $locked->paid_total = $paid;
            $locked->balance = bcsub((string) $locked->total_price, $paid, 2);
            $locked->save();

            return $locked->refresh();
        });
    }

    /**
     * Deliver a fully paid layaway, generating the final sale.
     */
    public function complete(Layaway $layaway, CashSession $session, User $user): Layaway
    {
        return DB::transaction(function () use ($layaway, $session, $user): Layaway {
            $locked = Layaway::withoutGlobalScopes()->lockForUpdate()->find($layaway->id);

            if ($locked === null || ! $locked->isActive()) {
                throw new RuntimeException('Solo se pueden entregar apartados activos.');
            }

            if (! $locked->isFullyPaid()) {
                throw new RuntimeException('El apartado aún tiene saldo pendiente.');
            }

            $product = Product::withoutGlobalScopes()
                ->with(['metal', 'purity'])
                ->lockForUpdate()
                ->find($locked->product_id);

            if ($product === null) {
                throw new RuntimeException('La pieza del apartado ya no existe.');
            }

            $sale = Sale::create([
                'store_id' => $locked->store_id,
                'number' => $this->sequences->nextNumber($locked->store_id, DocumentType::Sale),
                'customer_id' => $locked->customer_id,
                'user_id' => $user->id,
                'cash_session_id' => $session->id,
                'subtotal' => (string) $locked->total_price,
                'discount_total' => '0.00',
                'total' => (string) $locked->total_price,
                'status' => SaleStatus::Completed,
                'notes' => "Entrega de apartado {$locked->number}",
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'name_snapshot' => $product->name,
                'metal_snapshot' => $product->metal?->name ?? 'N/A',
                'purity_snapshot' => $product->purity?->label,
                'weight_g' => (string) $product->weight_g,
                'unit_price' => (string) $locked->total_price,
                'discount' => '0.00',
                'line_total' => (string) $locked->total_price,
            ]);

            $this->inventory->registerSale($product, $sale, $user->id);

            $locked->status = LayawayStatus::Completed;
            $locked->completed_sale_id = $sale->id;
            $locked->save();

            return $locked->refresh();
        });
    }

    /**
     * Cancel an active layaway, releasing the piece back to available stock.
     */
    public function cancel(Layaway $layaway, User $user, string $reason): Layaway
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo de la cancelación es obligatorio.');
        }

        return DB::transaction(function () use ($layaway, $user, $reason): Layaway {
            $locked = Layaway::withoutGlobalScopes()->lockForUpdate()->find($layaway->id);

            if ($locked === null || ! $locked->isActive()) {
                throw new RuntimeException('Solo se pueden cancelar apartados activos.');
            }

            $product = Product::withoutGlobalScopes()->lockForUpdate()->find($locked->product_id);

            if ($product !== null && $product->status === ProductStatus::Reserved) {
                $this->inventory->releaseLayaway($product, $locked, $user->id);
            }

            $locked->status = LayawayStatus::Cancelled;
            $locked->cancel_reason = $reason;
            $locked->save();

            return $locked->refresh();
        });
    }

    /**
     * Persist an immutable instalment row.
     */
    protected function recordPayment(Layaway $layaway, CashSession $session, User $user, string $method, string $amount, ?string $reference): LayawayPayment
    {
        return LayawayPayment::create([
            'layaway_id' => $layaway->id,
            'cash_session_id' => $session->id,
            'method' => $method,
            'amount' => $amount,
            'reference' => $reference,
            'user_id' => $user->id,
            'created_at' => now(),
        ]);
    }

    /**
     * Normalise a monetary value to a two-decimal string without floats.
     */
    protected function money(string|int|float|null $value): string
    {
        return bcadd((string) ($value ?? '0'), '0', 2);
    }
}
