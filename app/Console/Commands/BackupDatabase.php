<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--keep-days= : Días de retención (anula la configuración)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo de la base de datos y elimina los respaldos antiguos';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backups): int
    {
        try {
            $path = $backups->backup();
        } catch (Throwable $exception) {
            $this->components->error('No se pudo generar el respaldo: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Respaldo creado: {$path}");

        $keepDays = $this->option('keep-days');
        $removed = $backups->prune($keepDays !== null ? (int) $keepDays : null);

        if ($removed > 0) {
            $this->components->info("Se eliminaron {$removed} respaldo(s) antiguo(s).");
        }

        return self::SUCCESS;
    }
}
