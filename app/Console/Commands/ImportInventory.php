<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\User;
use App\Services\InventoryImportService;
use Illuminate\Console\Command;
use Throwable;

class ImportInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:import
        {file : Ruta del archivo .xlsx o .csv}
        {--user= : Correo o ID del usuario que registra los movimientos}
        {--store= : ID de la tienda destino}
        {--dry-run : Valida y simula la importación sin guardar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa el inventario inicial desde un archivo Excel (.xlsx) o CSV';

    /**
     * Execute the console command.
     */
    public function handle(InventoryImportService $importer): int
    {
        $path = $this->resolvePath((string) $this->argument('file'));

        if (! is_file($path)) {
            $this->components->error("No se encontró el archivo: {$path}");

            return self::FAILURE;
        }

        $user = $this->resolveUser();
        if ($user === null) {
            $this->components->error('No hay ningún usuario disponible para registrar los movimientos.');

            return self::FAILURE;
        }

        $storeId = $this->resolveStoreId($user);
        if ($storeId === 0) {
            $this->components->error('No se pudo determinar la tienda destino. Usa --store=ID.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->components->warn('MODO SIMULACIÓN: no se guardará ningún cambio.');
        }

        $this->components->info("Importando «{$path}» en la tienda #{$storeId} como usuario #{$user->id}…");

        try {
            $result = $importer->import($path, $storeId, (int) $user->id, $dryRun);
        } catch (Throwable $exception) {
            $this->components->error('La importación falló: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->components->twoColumnDetail('Piezas importadas', (string) $result['imported']);
        $this->components->info("Filas vacías omitidas: {$result['skipped']}");

        if ($result['errors'] !== []) {
            $this->newLine();
            $this->components->error(count($result['errors']).' fila(s) con errores:');

            $this->table(
                ['Fila', 'Motivo'],
                array_map(fn (array $error): array => [$error['row'], $exception = $error['message']], $result['errors']),
            );

            return self::FAILURE;
        }

        $this->components->info($dryRun ? 'Simulación completada sin errores.' : 'Importación completada sin errores.');

        return self::SUCCESS;
    }

    protected function resolvePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    protected function resolveUser(): ?User
    {
        $identifier = (string) $this->option('user');

        if ($identifier !== '') {
            return is_numeric($identifier)
                ? User::query()->find((int) $identifier)
                : User::query()->where('email', $identifier)->first();
        }

        return User::query()->orderBy('id')->first();
    }

    protected function resolveStoreId(User $user): int
    {
        $option = (string) $this->option('store');

        if ($option !== '') {
            return (int) $option;
        }

        $storeId = (int) ($user->store_id ?? 0);

        return $storeId !== 0 ? $storeId : (int) Store::query()->value('id');
    }
}
