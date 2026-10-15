<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Enums\StockCountStatus;
use App\Models\Product;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Runs a physical inventory count: snapshot the expected pieces, record what
 * is found and, once finished, apply the discrepancies as audited inventory
 * adjustments.
 */
class StockCountService
{
    public function __construct(protected InventoryService $inventory) {}

    /**
     * Open a new count snapshotting every piece physically held in stock.
     */
    public function start(User $user, ?string $notes = null): StockCount
    {
        return DB::transaction(function () use ($user, $notes): StockCount {
            $count = StockCount::create([
                'store_id' => $user->store_id,
                'status' => StockCountStatus::Open,
                'started_by' => $user->id,
                'started_at' => now(),
                'notes' => $notes,
            ]);

            $products = Product::withoutGlobalScopes()
                ->where('store_id', $user->store_id)
                ->whereIn('status', [ProductStatus::Available->value, ProductStatus::Reserved->value])
                ->get(['id']);

            $rows = $products->map(fn (Product $product): array => [
                'stock_count_id' => $count->id,
                'product_id' => $product->id,
                'expected' => true,
                'found' => null,
                'note' => null,
            ])->all();

            if ($rows !== []) {
                StockCountItem::insert($rows);
            }

            return $count->refresh();
        });
    }

    /**
     * Record the physically found state of the pieces in an open count.
     *
     * @param  array<int, array{product_id: int|string, found: bool|int|string|null, note?: string|null}>  $items
     */
    public function record(StockCount $count, array $items): StockCount
    {
        return DB::transaction(function () use ($count, $items): StockCount {
            $locked = StockCount::withoutGlobalScopes()->lockForUpdate()->find($count->id);

            if ($locked === null || ! $locked->isOpen()) {
                throw new RuntimeException('Solo se pueden registrar piezas en un conteo abierto.');
            }

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $found = $this->normaliseFound($item['found'] ?? null);

                $record = StockCountItem::firstOrNew([
                    'stock_count_id' => $locked->id,
                    'product_id' => $productId,
                ]);

                if (! $record->exists) {
                    $record->expected = false;
                }

                $record->found = $found;
                $record->note = $item['note'] ?? null;
                $record->save();
            }

            return $locked->refresh();
        });
    }

    /**
     * Close the count so it can be applied.
     */
    public function finish(StockCount $count, User $user, ?string $notes = null): StockCount
    {
        return DB::transaction(function () use ($count, $user, $notes): StockCount {
            $locked = StockCount::withoutGlobalScopes()->lockForUpdate()->find($count->id);

            if ($locked === null || ! $locked->isOpen()) {
                throw new RuntimeException('Solo se puede finalizar un conteo abierto.');
            }

            $locked->status = StockCountStatus::Finished;
            $locked->finished_at = now();
            $locked->finished_by = $user->id;

            if (filled($notes)) {
                $locked->notes = $notes;
            }

            $locked->save();

            return $locked->refresh();
        });
    }

    /**
     * Apply the count, generating adjustment movements for every difference.
     */
    public function apply(StockCount $count, User $user): StockCount
    {
        return DB::transaction(function () use ($count, $user): StockCount {
            $locked = StockCount::withoutGlobalScopes()->lockForUpdate()->find($count->id);

            if ($locked === null || ! $locked->isFinished()) {
                throw new RuntimeException('Solo se puede aplicar un conteo finalizado.');
            }

            $locked->load('items.product');

            foreach ($locked->items as $item) {
                if ($item->found === null || $item->product === null) {
                    continue;
                }

                if ($item->expected && ! $item->found) {
                    $this->inventory->adjustProduct($item->product, -1, "Faltante en conteo físico #{$locked->id}", $user->id, $locked);
                } elseif (! $item->expected && $item->found) {
                    $this->inventory->adjustProduct($item->product, 1, "Sobrante en conteo físico #{$locked->id}", $user->id, $locked);
                }
            }

            $locked->status = StockCountStatus::Applied;
            $locked->applied_at = now();
            $locked->applied_by = $user->id;
            $locked->save();

            return $locked->refresh();
        });
    }

    /**
     * Convert the recorded value into a tri-state (present/absent/not counted).
     */
    protected function normaliseFound(bool|int|string|null $found): ?bool
    {
        if ($found === null || $found === '') {
            return null;
        }

        return (bool) $found;
    }
}
