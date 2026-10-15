<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class RestoreDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore {file : Ruta del respaldo dentro del disco de respaldos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restaura la base de datos desde un archivo de respaldo';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backups): int
    {
        $file = (string) $this->argument('file');

        if (! $this->confirm("Esto sobrescribirá la base de datos actual con «{$file}». ¿Continuar?", false)) {
            $this->components->warn('Restauración cancelada.');

            return self::SUCCESS;
        }

        try {
            $backups->restore($file);
        } catch (Throwable $exception) {
            $this->components->error('No se pudo restaurar el respaldo: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Base de datos restaurada desde: {$file}");

        return self::SUCCESS;
    }
}
