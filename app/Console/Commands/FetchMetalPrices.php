<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\MetalPriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchMetalPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metal-prices:fetch {--store= : Restrict the fetch to a single store id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consulta el precio spot diario de oro y plata y lo persiste por gramo';

    /**
     * Execute the console command.
     */
    public function handle(MetalPriceService $prices): int
    {
        $storeId = $this->option('store');

        $store = $storeId ? Store::find($storeId) : null;

        if ($storeId && ! $store) {
            $this->error("No se encontró la sucursal {$storeId}.");

            return self::FAILURE;
        }

        try {
            $result = $prices->refreshAll($store);
        } catch (Throwable $exception) {
            $this->reportFailure([$exception->getMessage()]);

            return self::FAILURE;
        }

        if ($result['updated'] === 0) {
            $this->reportFailure($result['failed']);

            return self::FAILURE;
        }

        $this->info("Precios actualizados: {$result['updated']} registro(s).");

        if ($result['failed'] !== []) {
            $this->warn('Metales sin actualizar (se conserva el último precio válido): '.implode(', ', $result['failed']));
        }

        return self::SUCCESS;
    }

    /**
     * Emit an alert when no price could be refreshed.
     *
     * @param  array<int, string>  $details
     */
    protected function reportFailure(array $details): void
    {
        $message = 'No se pudieron actualizar los precios del metal: '.implode(', ', $details);

        $this->error($message);

        Log::warning($message);

        activity('precios')
            ->withProperties(['failed' => $details])
            ->log('Fallo al actualizar el precio del metal desde el proveedor');
    }
}
