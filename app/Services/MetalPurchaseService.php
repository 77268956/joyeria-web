<?php

namespace App\Services;

use App\Enums\CashMovementType;
use App\Enums\DocumentType;
use App\Enums\InventoryMovementType;
use App\Enums\MetalPurchaseStatus;
use App\Enums\PayoutMethod;
use App\Enums\ProductOrigin;
use App\Enums\PurchaseDestination;
use App\Models\CashSession;
use App\Models\Category;
use App\Models\Customer;
use App\Models\MaterialLot;
use App\Models\Metal;
use App\Models\MetalPurchase;
use App\Models\MetalPurchaseItem;
use App\Models\MetalPurity;
use App\Models\Product;
use App\Models\PurchaseAdjustment;
use App\Models\PurchaseSetting;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Registers, settles and adjusts purchases of precious metal.
 *
 * Every valuation is delegated to PricingCalculator using a frozen snapshot
 * of price, fineness and discount, so a purchase is reproducible through time.
 * Inventory changes go through InventoryService; cash payouts through
 * CashSessionService. The whole flow runs in a single atomic transaction.
 */
class MetalPurchaseService
{
    public function __construct(
        protected DocumentSequenceService $sequences,
        protected InventoryService $inventory,
        protected CashSessionService $cash,
        protected MetalPriceService $prices,
        protected PricingCalculator $pricing,
    ) {}

    /**
     * Register a metal purchase for an identified customer.
     *
     * @param  array{
     *     customer_id: int,
     *     payout_method: string,
     *     notes?: string|null,
     *     items: array<int, array{
     *         metal_id: int,
     *         purity_id: int,
     *         description: string,
     *         gross_weight_g: string,
     *         net_weight_g: string,
     *         weight_source: string,
     *         destination: string,
     *         photo: UploadedFile
     *     }>
     * }  $data
     */
    public function create(User $user, CashSession $session, array $data): MetalPurchase
    {
        if (! $session->isOpen()) {
            throw new RuntimeException('No hay un turno de caja abierto para registrar la compra.');
        }

        $customer = Customer::query()->find($data['customer_id']);

        if ($customer === null) {
            throw new InvalidArgumentException('Debe identificar al cliente que vende el metal.');
        }

        return DB::transaction(function () use ($user, $session, $data): MetalPurchase {
            $items = array_values($data['items']);
            $lines = $this->buildLines($session->store_id, $items);

            $total = '0.00';
            foreach ($lines as $line) {
                $total = bcadd($total, $line['amount'], 2);
            }

            $status = $this->resolveInitialStatus($session, $user, $total);
            $payoutMethod = PayoutMethod::from($data['payout_method']);

            $purchase = MetalPurchase::create([
                'store_id' => $session->store_id,
                'number' => $this->sequences->nextNumber($session->store_id, DocumentType::Purchase),
                'customer_id' => $data['customer_id'],
                'user_id' => $user->id,
                'cash_session_id' => $session->id,
                'status' => $status,
                'total_amount' => $total,
                'payout_method' => $payoutMethod,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $index => $item) {
                $this->persistItem($purchase, $item, $lines[$index]);
            }

            if ($status === MetalPurchaseStatus::Paid) {
                $this->settle($purchase, $user);
            }

            return $purchase->load(['items', 'customer']);
        });
    }

    /**
     * Approve a pending purchase, settling inventory and cash.
     */
    public function approve(MetalPurchase $purchase, User $user, ?string $note = null): MetalPurchase
    {
        if ($purchase->isPendingApproval() === false) {
            throw new RuntimeException('Solo se pueden aprobar compras pendientes de autorización.');
        }

        return DB::transaction(function () use ($purchase, $user, $note): MetalPurchase {
            $purchase->forceFill([
                'status' => MetalPurchaseStatus::Paid,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approval_note' => $note,
            ])->save();

            $this->settle($purchase, $user);

            return $purchase->refresh();
        });
    }

    /**
     * Reject a pending purchase with a mandatory reason.
     */
    public function reject(MetalPurchase $purchase, User $user, string $reason): MetalPurchase
    {
        if ($purchase->isPendingApproval() === false) {
            throw new RuntimeException('Solo se pueden rechazar compras pendientes de autorización.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo del rechazo es obligatorio.');
        }

        $purchase->forceFill([
            'status' => MetalPurchaseStatus::Rejected,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_note' => $reason,
        ])->save();

        return $purchase->refresh();
    }

    /**
     * Register an audited correction of the purchase total.
     *
     * The original purchase row is never edited in place: the adjustment keeps
     * the old and new values, and the cash shift is balanced by the difference.
     */
    public function adjust(MetalPurchase $purchase, User $user, string $newTotal, string $reason): PurchaseAdjustment
    {
        if (! in_array($purchase->status, [MetalPurchaseStatus::Paid, MetalPurchaseStatus::Adjusted], true)) {
            throw new RuntimeException('Solo se pueden ajustar compras ya pagadas.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo del ajuste es obligatorio.');
        }

        return DB::transaction(function () use ($purchase, $user, $newTotal, $reason): PurchaseAdjustment {
            $oldTotal = $this->money($purchase->total_amount);
            $newTotal = $this->money($newTotal);
            $difference = bcsub($newTotal, $oldTotal, 2);

            $adjustment = PurchaseAdjustment::create([
                'metal_purchase_id' => $purchase->id,
                'user_id' => $user->id,
                'reason' => $reason,
                'old_values' => ['total_amount' => $oldTotal],
                'new_values' => ['total_amount' => $newTotal],
                'amount_difference' => $difference,
            ]);

            $purchase->forceFill([
                'status' => MetalPurchaseStatus::Adjusted,
                'total_amount' => $newTotal,
            ])->save();

            if ($purchase->payout_method === PayoutMethod::Cash && bccomp($difference, '0', 2) !== 0) {
                $session = $purchase->cashSession;

                if ($session === null || ! $session->isOpen()) {
                    throw new RuntimeException('El turno de caja de la compra debe estar abierto para ajustar el efectivo.');
                }

                $isShortfall = bccomp($difference, '0', 2) > 0;
                $amount = $isShortfall ? $difference : bcmul($difference, '-1', 2);

                $this->cash->registerMovement(
                    $session,
                    $isShortfall ? CashMovementType::Out : CashMovementType::In,
                    $amount,
                    "Ajuste de compra {$purchase->number}",
                    $user,
                );
            }

            return $adjustment;
        });
    }

    /**
     * Move every bought piece to its destination and settle the cash payout.
     */
    protected function settle(MetalPurchase $purchase, User $user): void
    {
        $purchase->loadMissing('items.metal', 'items.purity');

        foreach ($purchase->items as $item) {
            if ($item->destination === PurchaseDestination::UsedInventory) {
                $this->sendToUsedInventory($purchase, $item, $user);
            } else {
                $this->sendToMelting($purchase, $item, $user);
            }
        }

        if ($purchase->payout_method === PayoutMethod::Cash) {
            $session = $purchase->cashSession;

            if ($session === null || ! $session->isOpen()) {
                throw new RuntimeException('El turno de caja de la compra debe estar abierto para pagar en efectivo.');
            }

            $this->cash->registerMovement(
                $session,
                CashMovementType::Out,
                $this->money($purchase->total_amount),
                "Compra de metales {$purchase->number}",
                $user,
            );
        }
    }

    /**
     * Create a used-inventory piece and register its entry movement.
     */
    protected function sendToUsedInventory(MetalPurchase $purchase, MetalPurchaseItem $item, User $user): void
    {
        $product = Product::create([
            'store_id' => $purchase->store_id,
            'sku' => Product::generateSku($purchase->store_id),
            'barcode' => Product::generateBarcode($purchase->store_id),
            'name' => $item->description,
            'category_id' => Category::firstOrCreate(
                ['slug' => 'ocasion'],
                ['name' => 'Ocasión', 'is_active' => true],
            )->id,
            'metal_id' => $item->metal_id,
            'purity_id' => $item->purity_id,
            'weight_g' => (string) $item->net_weight_g,
            'cost' => $this->money($item->amount),
            'price' => $this->money($item->amount),
            'origin' => ProductOrigin::UsedPurchase,
            'is_public' => false,
        ]);

        $this->inventory->registerPurchaseEntry($product, $purchase, "Compra {$purchase->number}", $user->id);

        $item->forceFill(['product_id' => $product->id])->save();
    }

    /**
     * Create a raw-material lot for melting and register its entry movement.
     */
    protected function sendToMelting(MetalPurchase $purchase, MetalPurchaseItem $item, User $user): void
    {
        $lot = $this->inventory->createMaterialLot([
            'store_id' => $purchase->store_id,
            'code' => MaterialLot::generateCode($purchase->store_id),
            'metal_id' => $item->metal_id,
            'purity_id' => $item->purity_id,
            'initial_weight_g' => (string) $item->net_weight_g,
            'notes' => "Compra {$purchase->number}: {$item->description}",
        ], $user->id, InventoryMovementType::PurchaseEntry, "Compra {$purchase->number}", $purchase);

        $item->forceFill(['material_lot_id' => $lot->id])->save();
    }

    /**
     * Persist a purchase line using frozen valuation snapshots.
     *
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $line
     */
    protected function persistItem(MetalPurchase $purchase, array $item, array $line): void
    {
        MetalPurchaseItem::create([
            'metal_purchase_id' => $purchase->id,
            'metal_id' => $item['metal_id'],
            'purity_id' => $item['purity_id'],
            'description' => $item['description'],
            'gross_weight_g' => $line['gross_weight_g'],
            'net_weight_g' => $line['net_weight_g'],
            'weight_source' => $item['weight_source'],
            'fineness_snapshot' => $line['fineness_snapshot'],
            'price_per_gram_snapshot' => $line['price_per_gram_snapshot'],
            'discount_percent_snapshot' => $line['discount_percent_snapshot'],
            'amount' => $line['amount'],
            'photo_path' => $this->storePhoto($item['photo']),
            'destination' => $item['destination'],
        ]);
    }

    /**
     * Validate and price every requested piece (server-side only).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, string>>
     */
    protected function buildLines(int $storeId, array $items): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('La compra debe incluir al menos una pieza.');
        }

        $lines = [];

        foreach ($items as $item) {
            $lines[] = $this->priceLine($storeId, $item);
        }

        return $lines;
    }

    /**
     * Value a single requested piece with frozen snapshots.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, string>
     */
    protected function priceLine(int $storeId, array $item): array
    {
        $metal = Metal::find($item['metal_id']);

        if ($metal === null) {
            throw new InvalidArgumentException('El metal seleccionado no existe.');
        }

        $purity = MetalPurity::where('metal_id', $metal->id)->find($item['purity_id']);

        if ($purity === null) {
            throw new InvalidArgumentException("La pureza seleccionada no corresponde a {$metal->name}.");
        }

        $gross = $this->decimal($item['gross_weight_g'], 3);
        $net = $this->decimal($item['net_weight_g'], 3);

        if (bccomp($gross, '0', 3) <= 0) {
            throw new InvalidArgumentException('El peso bruto debe ser mayor a cero.');
        }

        if (bccomp($net, '0', 3) <= 0) {
            throw new InvalidArgumentException('El peso neto debe ser mayor a cero.');
        }

        if (bccomp($net, $gross, 3) > 0) {
            throw new InvalidArgumentException('El peso neto no puede superar el peso bruto.');
        }

        $fineness = (string) $purity->fineness;
        $pricePerGram = $this->pricePerGram($metal);
        $discount = $this->discountFor($storeId, $metal->id, $purity->id);

        return [
            'gross_weight_g' => $gross,
            'net_weight_g' => $net,
            'fineness_snapshot' => $fineness,
            'price_per_gram_snapshot' => $pricePerGram,
            'discount_percent_snapshot' => $discount,
            'amount' => $this->pricing->purchaseAmount($net, $pricePerGram, $fineness, $discount),
        ];
    }

    /**
     * Price a single piece without persisting anything (live form preview).
     *
     * @return array<string, string>
     */
    public function quote(int $storeId, int $metalId, int $purityId, string $grossWeightG, string $netWeightG): array
    {
        return $this->priceLine($storeId, [
            'metal_id' => $metalId,
            'purity_id' => $purityId,
            'gross_weight_g' => $grossWeightG,
            'net_weight_g' => $netWeightG,
        ]);
    }

    /**
     * Store an uploaded piece photo on the private disk.
     */
    protected function storePhoto(UploadedFile $photo): string
    {
        return $photo->store('purchase-photos', 'local');
    }

    /**
     * Resolve the discount configured for a metal/purity.
     */
    protected function discountFor(int $storeId, int $metalId, ?int $purityId): string
    {
        $setting = PurchaseSetting::withoutGlobalScopes()
            ->where('store_id', $storeId)
            ->where('metal_id', $metalId)
            ->where(function ($query) use ($purityId): void {
                $query->where('purity_id', $purityId)->orWhereNull('purity_id');
            })
            ->orderByRaw('purity_id IS NULL')
            ->first();

        return $setting !== null ? (string) $setting->discount_percent : '0';
    }

    /**
     * Resolve the current price per pure gram for a metal.
     */
    protected function pricePerGram(Metal $metal): string
    {
        $price = $this->prices->currentForMetal($metal);

        if ($price === null) {
            throw new RuntimeException("No hay un precio registrado para {$metal->name}.");
        }

        return (string) $price->price_per_gram;
    }

    /**
     * Decide whether the purchase is paid immediately or needs approval.
     */
    protected function requiresApproval(int $storeId, string $total): bool
    {
        $settings = SystemSetting::firstOrCreate(['store_id' => $storeId]);

        return bccomp($total, $this->money($settings->purchase_approval_limit), 2) > 0;
    }

    /**
     * High-value purchases above the limit await a manager unless the author
     * already holds the approval permission.
     */
    protected function resolveInitialStatus(CashSession $session, User $user, string $total): MetalPurchaseStatus
    {
        if ($this->requiresApproval($session->store_id, $total) && ! $user->can('purchases.approve')) {
            return MetalPurchaseStatus::PendingApproval;
        }

        return MetalPurchaseStatus::Paid;
    }

    /**
     * Normalise a monetary value to a two-decimal string without floats.
     */
    protected function money(string|int|float|null $value): string
    {
        return bcadd((string) ($value ?? '0'), '0', 2);
    }

    /**
     * Normalise a decimal value to the given scale without floats.
     */
    protected function decimal(string|int|float|null $value, int $scale): string
    {
        return bcadd((string) ($value ?? '0'), '0', $scale);
    }
}
