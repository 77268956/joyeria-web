<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use App\Enums\ProductStatus;
use App\Enums\SaleStatus;
use App\Models\CashSession;
use App\Models\DiscountAuthorization;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Registers and voids sales inside a single atomic transaction.
 *
 * All money is recomputed server-side from the catalogue prices; amounts
 * sent by the client are never trusted. Inventory changes are delegated to
 * InventoryService, the only authorised writer of stock.
 */
class SaleService
{
    public function __construct(
        protected DocumentSequenceService $sequences,
        protected InventoryService $inventory,
        protected DiscountAuthorizationService $authorizations,
    ) {}

    /**
     * Register a sale for the given cashier and their open shift.
     *
     * @param  array{
     *     items: array<int, array{product_id: int, discount?: string}>,
     *     payments: array<int, array{method: string, amount: string, received?: string, reference?: string}>,
     *     customer_id?: int|null,
     *     discount_total?: string,
     *     discount_reason?: string|null,
     *     notes?: string|null
     * }  $data
     */
    public function create(User $user, CashSession $session, array $data): Sale
    {
        if (! $session->isOpen()) {
            throw new RuntimeException('No hay un turno de caja abierto para registrar la venta.');
        }

        return DB::transaction(function () use ($user, $session, $data): Sale {
            $lines = $this->buildLines($session, $data['items']);

            $subtotal = '0.00';
            $lineDiscounts = '0.00';

            foreach ($lines as $line) {
                $subtotal = bcadd($subtotal, $line['unit_price'], 2);
                $lineDiscounts = bcadd($lineDiscounts, $line['discount'], 2);
            }

            $globalDiscount = $this->money($data['discount_total'] ?? '0');
            $discountTotal = bcadd($lineDiscounts, $globalDiscount, 2);
            $total = bcsub($subtotal, $discountTotal, 2);

            $supervisor = $this->assertDiscountIsAllowed(
                $user,
                $session,
                $subtotal,
                $discountTotal,
                $data['discount_reason'] ?? null,
                $data['supervisor_pin'] ?? null,
                isset($data['discount_authorization_id']) ? (int) $data['discount_authorization_id'] : null,
            );
            $payments = $this->normalisePayments($data['payments']);
            $this->assertPaymentsMatch($payments, $total);

            $notes = $data['notes'] ?? null;
            if ($supervisor !== null) {
                $authNote = "Descuento autorizado por: {$supervisor->name}";
                $notes = $notes ? "{$notes} | {$authNote}" : $authNote;
            }

            $sale = Sale::create([
                'store_id' => $session->store_id,
                'number' => $this->sequences->nextNumber($session->store_id, DocumentType::Sale),
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => $user->id,
                'cash_session_id' => $session->id,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'total' => $total,
                'status' => SaleStatus::Completed,
                'notes' => $notes,
            ]);

            foreach ($lines as $line) {
                $this->persistItem($sale, $line, $user);
            }

            foreach ($payments as $payment) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'cash_session_id' => $session->id,
                    'method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'received' => $payment['received'],
                    'reference' => $payment['reference'],
                ]);
            }

            return $sale->load(['items', 'payments']);
        });
    }

    /**
     * Void a sale, returning every piece to available stock.
     */
    public function void(Sale $sale, string $reason, User $user): Sale
    {
        if ($sale->isVoided()) {
            throw new RuntimeException('La venta ya está anulada.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo de la anulación es obligatorio.');
        }

        return DB::transaction(function () use ($sale, $reason, $user): Sale {
            $sale->load('items.product');

            foreach ($sale->items as $item) {
                if ($item->product !== null) {
                    $this->inventory->registerSaleVoid($item->product, $sale, $user->id);
                }
            }

            $sale->forceFill([
                'status' => SaleStatus::Voided,
                'voided_at' => now(),
                'voided_by' => $user->id,
                'void_reason' => $reason,
            ])->save();

            return $sale->refresh();
        });
    }

    /**
     * Lock the requested pieces and build the priced sale lines.
     *
     * @param  array<int, array{product_id: int, discount?: string}>  $items
     * @return array<int, array{product: Product, unit_price: string, discount: string, line_total: string}>
     */
    protected function buildLines(CashSession $session, array $items): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('La venta debe incluir al menos una pieza.');
        }

        $ids = array_map(static fn (array $item): int => (int) $item['product_id'], $items);

        if (count($ids) !== count(array_unique($ids))) {
            throw new InvalidArgumentException('Una misma pieza no puede aparecer dos veces en la venta.');
        }

        $products = Product::withoutGlobalScopes()
            ->where('store_id', $session->store_id)
            ->whereIn('id', $ids)
            ->with(['metal', 'purity'])
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $lines = [];

        foreach ($items as $item) {
            $product = $products->get((int) $item['product_id']);

            if ($product === null) {
                throw new InvalidArgumentException('Una de las piezas de la venta no existe.');
            }

            if ($product->status !== ProductStatus::Available) {
                throw new RuntimeException("La pieza '{$product->name}' ya no está disponible para vender.");
            }

            if (bccomp((string) $product->weight_g, '0', 3) <= 0) {
                throw new InvalidArgumentException("La pieza '{$product->name}' no tiene un peso válido para vender.");
            }

            $unitPrice = $this->money($product->price);
            $discount = $this->money($item['discount'] ?? '0');

            if (bccomp($discount, '0', 2) < 0) {
                throw new InvalidArgumentException("El descuento de '{$product->name}' no puede ser negativo.");
            }

            if (bccomp($discount, $unitPrice, 2) > 0) {
                throw new InvalidArgumentException("El descuento de '{$product->name}' no puede superar su precio.");
            }

            $lines[] = [
                'product' => $product,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'line_total' => bcsub($unitPrice, $discount, 2),
            ];
        }

        return $lines;
    }

    /**
     * Persist a sale line and move the piece out of inventory.
     *
     * @param  array{product: Product, unit_price: string, discount: string, line_total: string}  $line
     */
    protected function persistItem(Sale $sale, array $line, User $user): void
    {
        $product = $line['product'];

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'name_snapshot' => $product->name,
            'metal_snapshot' => $product->metal?->name ?? 'N/A',
            'purity_snapshot' => $product->purity?->label,
            'weight_g' => (string) $product->weight_g,
            'unit_price' => $line['unit_price'],
            'discount' => $line['discount'],
            'line_total' => $line['line_total'],
        ]);

        $this->inventory->registerSale($product, $sale, $user->id);
    }

    /**
     * Validate that the discount does not exceed the seller's limit, or verify supervisor approval.
     */
    protected function assertDiscountIsAllowed(
        User $user,
        CashSession $session,
        string $subtotal,
        string $discountTotal,
        ?string $reason,
        ?string $supervisorPin = null,
        ?int $discountAuthorizationId = null
    ): ?User {
        if (bccomp($discountTotal, '0', 2) <= 0) {
            return null;
        }

        if (blank($reason)) {
            throw new InvalidArgumentException('El motivo del descuento es obligatorio.');
        }

        $maxPercent = (string) $this->settings($session->store_id)->max_discount_percent;

        if (bccomp($subtotal, '0', 2) <= 0) {
            return null;
        }

        $discountPercent = bcmul(bcdiv($discountTotal, $subtotal, 6), '100', 4);

        if (bccomp($discountPercent, $maxPercent, 2) <= 0 || $user->can('products.edit_price')) {
            return null;
        }

        // Supervisor authorization required:
        // 1. In-person PIN
        if (! blank($supervisorPin)) {
            return $this->authorizations->authorizeByPin($supervisorPin, $session->store_id);
        }

        // 2. Approved remote authorization ID
        if ($discountAuthorizationId !== null) {
            $auth = DiscountAuthorization::find($discountAuthorizationId);
            if ($auth === null || ! $auth->isUsable() || $auth->store_id !== $session->store_id) {
                throw new RuntimeException('La autorización de descuento no es válida o ha expirado.');
            }
            $this->authorizations->markUsed($auth);

            return $auth->authorizer;
        }

        $displayPercent = number_format((float) $discountPercent, 2);

        throw new RuntimeException("El descuento ({$displayPercent}%) supera el máximo permitido ({$maxPercent}%). Requiere autorización de gerencia.");
    }

    /**
     * Normalise the payment payload into monetary strings.
     *
     * @param  array<int, array{method: string, amount: string, received?: string, reference?: string}>  $payments
     * @return array<int, array{method: string, amount: string, received: string|null, reference: string|null}>
     */
    protected function normalisePayments(array $payments): array
    {
        if ($payments === []) {
            throw new InvalidArgumentException('La venta debe registrar al menos un pago.');
        }

        return array_map(function (array $payment): array {
            $amount = $this->money($payment['amount']);

            if (bccomp($amount, '0', 2) <= 0) {
                throw new InvalidArgumentException('Cada pago debe ser mayor a cero.');
            }

            $received = isset($payment['received']) ? $this->money($payment['received']) : null;

            if (($payment['method'] === PaymentMethod::Cash->value) && $received !== null && bccomp($received, $amount, 2) < 0) {
                throw new InvalidArgumentException('El efectivo recibido no puede ser menor al monto en efectivo.');
            }

            return [
                'method' => $payment['method'],
                'amount' => $amount,
                'received' => $received,
                'reference' => $payment['reference'] ?? null,
            ];
        }, array_values($payments));
    }

    /**
     * Ensure the payments settle the sale exactly.
     *
     * @param  array<int, array{amount: string}>  $payments
     */
    protected function assertPaymentsMatch(array $payments, string $total): void
    {
        $paid = '0.00';

        foreach ($payments as $payment) {
            $paid = bcadd($paid, $payment['amount'], 2);
        }

        if (bccomp($paid, $total, 2) !== 0) {
            throw new InvalidArgumentException('La suma de los pagos debe ser igual al total de la venta.');
        }
    }

    /**
     * Resolve the store settings row (created on demand with column defaults).
     */
    protected function settings(int $storeId): SystemSetting
    {
        return SystemSetting::firstOrCreate(['store_id' => $storeId]);
    }

    /**
     * Normalise a monetary value to a two-decimal string without floats.
     */
    protected function money(string|int|float|null $value): string
    {
        return bcadd((string) ($value ?? '0'), '0', 2);
    }
}
