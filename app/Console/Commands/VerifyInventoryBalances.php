<?php

namespace App\Console\Commands;

use App\Enums\ProductStatus;
use App\Models\InventoryMovement;
use App\Models\MaterialLot;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class VerifyInventoryBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:verify-balances {--store= : Restrict the check to a single store id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compara los saldos derivados contra los movimientos inmutables de inventario';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $storeId = $this->option('store');

        $issues = array_merge(
            $this->verifyProducts($storeId),
            $this->verifyLots($storeId),
        );

        if ($issues === []) {
            $this->info('Sin inconsistencias: todos los saldos coinciden con los movimientos.');

            return self::SUCCESS;
        }

        $this->warn(count($issues).' inconsistencia(s) detectada(s):');

        foreach ($issues as $issue) {
            $this->line(' - '.$issue);
        }

        return self::FAILURE;
    }

    /**
     * Verify that every piece's derived status matches its net movement quantity.
     *
     * @return array<int, string>
     */
    protected function verifyProducts(?string $storeId): array
    {
        $netByProduct = $this->netQuantityByProduct($storeId);
        $issues = [];

        Product::withoutGlobalScopes()
            ->when($storeId, fn ($query) => $query->where('store_id', $storeId))
            ->orderBy('id')
            ->chunkById(200, function ($products) use ($netByProduct, &$issues): void {
                foreach ($products as $product) {
                    $net = (int) ($netByProduct[$product->id] ?? 0);
                    $inStock = $product->status instanceof ProductStatus
                        ? $product->status->isInStock()
                        : $product->status === ProductStatus::Available->value;

                    if ($net > 0 && ! $inStock) {
                        $issues[] = sprintf(
                            'Pieza %s (ID %d): %d en stock pero estado "%s".',
                            $product->sku,
                            $product->id,
                            $net,
                            $product->status?->value ?? (string) $product->status,
                        );
                    } elseif ($net <= 0 && $inStock) {
                        $issues[] = sprintf(
                            'Pieza %s (ID %d): estado "%s" pero %d movimientos netos.',
                            $product->sku,
                            $product->id,
                            $product->status?->value ?? (string) $product->status,
                            $net,
                        );
                    }
                }
            });

        return $issues;
    }

    /**
     * Verify that every lot's current weight matches the sum of its movements.
     *
     * @return array<int, string>
     */
    protected function verifyLots(?string $storeId): array
    {
        $netByLot = InventoryMovement::withoutGlobalScopes()
            ->when($storeId, fn ($query) => $query->where('store_id', $storeId))
            ->whereNotNull('material_lot_id')
            ->selectRaw('material_lot_id, SUM(weight_delta_g) as net')
            ->groupBy('material_lot_id')
            ->pluck('net', 'material_lot_id');

        $issues = [];

        MaterialLot::withoutGlobalScopes()
            ->when($storeId, fn ($query) => $query->where('store_id', $storeId))
            ->orderBy('id')
            ->chunkById(200, function ($lots) use ($netByLot, &$issues): void {
                foreach ($lots as $lot) {
                    $expected = bcadd((string) ($netByLot[$lot->id] ?? '0'), '0', 3);
                    $current = bcadd((string) $lot->current_weight_g, '0', 3);

                    if (bccomp($expected, $current, 3) !== 0) {
                        $issues[] = sprintf(
                            'Lote %s (ID %d): peso actual %s g pero los movimientos suman %s g.',
                            $lot->code,
                            $lot->id,
                            $current,
                            $expected,
                        );
                    }
                }
            });

        return $issues;
    }

    /**
     * Net quantity delta per product.
     *
     * @return Collection<int, int>
     */
    protected function netQuantityByProduct(?string $storeId)
    {
        return InventoryMovement::withoutGlobalScopes()
            ->when($storeId, fn ($query) => $query->where('store_id', $storeId))
            ->whereNotNull('product_id')
            ->selectRaw('product_id, SUM(quantity_delta) as net')
            ->groupBy('product_id')
            ->pluck('net', 'product_id');
    }
}
