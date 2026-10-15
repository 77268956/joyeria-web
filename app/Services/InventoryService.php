<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Enums\MaterialLotStatus;
use App\Enums\ProductStatus;
use App\Models\InventoryMovement;
use App\Models\MaterialLot;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * The single authorised entry point to alter stock.
 *
 * Every stock change inserts an immutable row into `inventory_movements`
 * and updates the derived balance (product status or lot weight) inside
 * the same database transaction. No other code may write to these fields.
 */
class InventoryService
{
    /**
     * Register a new piece entering inventory.
     */
    public function registerProductEntry(Product $product, ?Model $reference = null, ?string $reason = null, ?int $userId = null): InventoryMovement
    {
        return DB::transaction(function () use ($product, $reference, $reason, $userId): InventoryMovement {
            $movement = $this->recordProductMovement($product, InventoryMovementType::Entry, 1, $reference, $reason, $userId);
            $this->setProductStatus($product, ProductStatus::Available);

            return $movement;
        });
    }

    /**
     * Register a piece entering inventory through a metal purchase.
     */
    public function registerPurchaseEntry(Product $product, ?Model $reference = null, ?string $reason = null, ?int $userId = null): InventoryMovement
    {
        return DB::transaction(function () use ($product, $reference, $reason, $userId): InventoryMovement {
            $movement = $this->recordProductMovement($product, InventoryMovementType::PurchaseEntry, 1, $reference, $reason, $userId);
            $this->setProductStatus($product, ProductStatus::Available);

            return $movement;
        });
    }

    /**
     * Adjust a single piece in or out of stock with a mandatory reason.
     */
    public function adjustProduct(Product $product, int $quantityDelta, string $reason, ?int $userId = null, ?Model $reference = null): InventoryMovement
    {
        if (! in_array($quantityDelta, [-1, 1], true)) {
            throw new InvalidArgumentException('El ajuste de una pieza debe ser +1 (alta) o -1 (baja).');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo del ajuste es obligatorio.');
        }

        return DB::transaction(function () use ($product, $quantityDelta, $reason, $userId, $reference): InventoryMovement {
            $movement = $this->recordProductMovement($product, InventoryMovementType::Adjustment, $quantityDelta, $reference, $reason, $userId);

            $this->setProductStatus(
                $product,
                $quantityDelta > 0 ? ProductStatus::Available : ProductStatus::Withdrawn
            );

            return $movement;
        });
    }

    /**
     * Send a piece to melting, removing it from available stock.
     */
    public function sendProductToMelting(Product $product, string $reason, ?Model $reference = null, ?int $userId = null): InventoryMovement
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo del envío a fundición es obligatorio.');
        }

        return DB::transaction(function () use ($product, $reason, $reference, $userId): InventoryMovement {
            $movement = $this->recordProductMovement($product, InventoryMovementType::ToMelting, -1, $reference, $reason, $userId);
            $this->setProductStatus($product, ProductStatus::Melting);

            return $movement;
        });
    }

    /**
     * Register a piece leaving inventory through a sale.
     */
    public function registerSale(Product $product, Model $sale, ?int $userId = null): InventoryMovement
    {
        return DB::transaction(function () use ($product, $sale, $userId): InventoryMovement {
            $movement = $this->recordProductMovement($product, InventoryMovementType::Sale, -1, $sale, "Venta {$sale->number}", $userId);
            $this->setProductStatus($product, ProductStatus::Sold);

            return $movement;
        });
    }

    /**
     * Reverse a sold piece back into available stock when a sale is voided.
     */
    public function registerSaleVoid(Product $product, Model $sale, ?int $userId = null): InventoryMovement
    {
        return DB::transaction(function () use ($product, $sale, $userId): InventoryMovement {
            $movement = $this->recordProductMovement($product, InventoryMovementType::SaleVoid, 1, $sale, "Anulación de venta {$sale->number}", $userId);
            $this->setProductStatus($product, ProductStatus::Available);

            return $movement;
        });
    }

    /**
     * Reserve a piece for a layaway without removing it from stock on hand.
     */
    public function reserveForLayaway(Product $product, Model $layaway, ?int $userId = null): InventoryMovement
    {
        return DB::transaction(function () use ($product, $layaway, $userId): InventoryMovement {
            $movement = $this->recordProductMovement($product, InventoryMovementType::LayawayReserve, 0, $layaway, "Apartado {$layaway->number}", $userId);
            $this->setProductStatus($product, ProductStatus::Reserved);

            return $movement;
        });
    }

    /**
     * Release a reserved piece back to available stock when a layaway is cancelled.
     */
    public function releaseLayaway(Product $product, Model $layaway, ?int $userId = null): InventoryMovement
    {
        return DB::transaction(function () use ($product, $layaway, $userId): InventoryMovement {
            $movement = $this->recordProductMovement($product, InventoryMovementType::LayawayRelease, 0, $layaway, "Liberación de apartado {$layaway->number}", $userId);
            $this->setProductStatus($product, ProductStatus::Available);

            return $movement;
        });
    }

    /**
     * Create a raw-material lot and register its opening weight.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createMaterialLot(
        array $attributes,
        ?int $userId = null,
        InventoryMovementType $movementType = InventoryMovementType::Entry,
        ?string $reason = null,
        ?Model $reference = null,
    ): MaterialLot {
        $initialWeight = (string) $attributes['initial_weight_g'];

        return DB::transaction(function () use ($attributes, $initialWeight, $userId, $movementType, $reason, $reference): MaterialLot {
            $lot = MaterialLot::create(array_merge($attributes, [
                'current_weight_g' => $initialWeight,
                'status' => MaterialLotStatus::Open,
            ]));

            $this->recordLotMovement($lot, $movementType, $initialWeight, $reference, $reason ?? 'Alta de lote', $userId);

            return $lot->refresh();
        });
    }

    /**
     * Add or remove weight from an existing lot (signed delta in grams).
     */
    public function adjustLotWeight(MaterialLot $lot, string $weightDeltaG, string $reason, ?int $userId = null): InventoryMovement
    {
        if (bccomp($weightDeltaG, '0', 3) === 0) {
            throw new InvalidArgumentException('La variación de peso del lote no puede ser cero.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo del ajuste de lote es obligatorio.');
        }

        return DB::transaction(function () use ($lot, $weightDeltaG, $reason, $userId): InventoryMovement {
            $newWeight = bcadd((string) $lot->current_weight_g, $weightDeltaG, 3);

            if (bccomp($newWeight, '0', 3) < 0) {
                throw new InvalidArgumentException('El peso del lote no puede quedar por debajo de cero.');
            }

            $lot->current_weight_g = $newWeight;
            $lot->save();

            return $this->recordLotMovement($lot, InventoryMovementType::Adjustment, $weightDeltaG, null, $reason, $userId);
        });
    }

    /**
     * Send a lot to melting, zeroing its remaining weight.
     */
    public function sendLotToMelting(MaterialLot $lot, string $reason, ?int $userId = null): InventoryMovement
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo del envío a fundición es obligatorio.');
        }

        return DB::transaction(function () use ($lot, $reason, $userId): InventoryMovement {
            $current = (string) $lot->current_weight_g;
            $delta = bcmul($current, '-1', 3);

            $lot->current_weight_g = '0.000';
            $lot->status = MaterialLotStatus::SentToMelting;
            $lot->save();

            return $this->recordLotMovement($lot, InventoryMovementType::ToMelting, $delta, null, $reason, $userId);
        });
    }

    /**
     * Insert an immutable piece-level movement.
     */
    protected function recordProductMovement(
        Product $product,
        InventoryMovementType $type,
        int $quantityDelta,
        ?Model $reference,
        ?string $reason,
        ?int $userId,
    ): InventoryMovement {
        return InventoryMovement::create([
            'store_id' => $product->store_id,
            'product_id' => $product->id,
            'material_lot_id' => null,
            'type' => $type,
            'quantity_delta' => $quantityDelta,
            'weight_delta_g' => '0.000',
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'reason' => $reason,
            'user_id' => $this->resolveUserId($userId),
        ]);
    }

    /**
     * Insert an immutable lot-level movement.
     */
    protected function recordLotMovement(
        MaterialLot $lot,
        InventoryMovementType $type,
        string $weightDeltaG,
        ?Model $reference,
        ?string $reason,
        ?int $userId,
    ): InventoryMovement {
        return InventoryMovement::create([
            'store_id' => $lot->store_id,
            'product_id' => null,
            'material_lot_id' => $lot->id,
            'type' => $type,
            'quantity_delta' => 0,
            'weight_delta_g' => $weightDeltaG,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'reason' => $reason,
            'user_id' => $this->resolveUserId($userId),
        ]);
    }

    /**
     * Persist the derived status of a piece.
     */
    protected function setProductStatus(Product $product, ProductStatus $status): void
    {
        $product->status = $status;
        $product->save();
    }

    /**
     * Resolve the acting user, falling back to the authenticated session.
     */
    protected function resolveUserId(?int $userId): int
    {
        $resolved = $userId ?? Auth::id();

        if ($resolved === null) {
            throw new RuntimeException('Se requiere un usuario para registrar movimientos de inventario.');
        }

        return (int) $resolved;
    }
}
