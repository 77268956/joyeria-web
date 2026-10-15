<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Creates and restores database backups without external PHP dependencies.
 *
 * MySQL backups shell out to `mysqldump`/`mysql`; SQLite backups copy the
 * database file. Every artifact is written to a private, non-web-accessible
 * disk (see config/backup.php).
 */
class DatabaseBackupService
{
    public function backup(): string
    {
        $disk = $this->disk();
        $directory = (string) config('backup.path');

        $disk->makeDirectory($directory);

        $filename = $this->filename((string) config('database.default'));

        match ($driver = config('database.default')) {
            'sqlite' => $this->backupSqlite($filename),
            'mysql' => $this->backupMysql($filename),
            default => throw new RuntimeException("Driver de base de datos no soportado para respaldo: {$driver}"),
        };

        return $filename;
    }

    public function restore(string $path): void
    {
        if (! $this->disk()->exists($path)) {
            throw new RuntimeException("No se encontró el respaldo: {$path}");
        }

        match ($driver = config('database.default')) {
            'sqlite' => $this->restoreSqlite($path),
            'mysql' => $this->restoreMysql($path),
            default => throw new RuntimeException("Driver de base de datos no soportado para restauración: {$driver}"),
        };
    }

    /**
     * Delete backups older than the configured retention window.
     */
    public function prune(?int $days = null): int
    {
        $days ??= (int) config('backup.retention_days');
        $threshold = now()->subDays($days)->getTimestamp();

        $removed = 0;

        foreach ($this->disk()->files((string) config('backup.path')) as $file) {
            if ($this->disk()->lastModified($file) < $threshold) {
                $this->disk()->delete($file);
                $removed++;
            }
        }

        return $removed;
    }

    protected function backupSqlite(string $filename): void
    {
        $database = (string) config('database.connections.sqlite.database');

        if ($database === ':memory:' || ! is_file($database)) {
            throw new RuntimeException(
                'No se puede respaldar una base de datos SQLite en memoria. Configura una ruta de archivo real.',
            );
        }

        $source = fopen($database, 'r');

        if ($source === false) {
            throw new RuntimeException("No se pudo abrir la base de datos SQLite: {$database}");
        }

        try {
            $this->disk()->writeStream($filename, $source);
        } finally {
            if (is_resource($source)) {
                fclose($source);
            }
        }
    }

    protected function backupMysql(string $filename): void
    {
        $connection = (array) config('database.connections.mysql');

        $arguments = [
            (string) config('backup.mysqldump.binary'),
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            '--skip-lock-tables',
            (string) $connection['database'],
        ];

        $result = Process::timeout((float) config('backup.mysqldump.timeout'))
            ->env(['MYSQL_PWD' => (string) $connection['password']])
            ->run($arguments);

        if ($result->failed()) {
            throw new RuntimeException('mysqldump falló: '.$result->errorOutput());
        }

        $this->disk()->put($filename, $result->output());
    }

    protected function restoreSqlite(string $path): void
    {
        $database = (string) config('database.connections.sqlite.database');

        if ($database === ':memory:') {
            throw new RuntimeException('No se puede restaurar sobre una base de datos SQLite en memoria.');
        }

        file_put_contents($database, $this->disk()->get($path));
    }

    protected function restoreMysql(string $path): void
    {
        $connection = (array) config('database.connections.mysql');

        $arguments = [
            'mysql',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            (string) $connection['database'],
        ];

        $result = Process::timeout((float) config('backup.mysqldump.timeout'))
            ->env(['MYSQL_PWD' => (string) $connection['password']])
            ->input($this->disk()->get($path))
            ->run($arguments);

        if ($result->failed()) {
            throw new RuntimeException('La restauración con mysql falló: '.$result->errorOutput());
        }
    }

    protected function filename(string $driver): string
    {
        $extension = $driver === 'sqlite' ? 'sqlite' : 'sql';

        $directory = rtrim((string) config('backup.path'), '/');

        return $directory.'/joyeriaweb_'.now()->format('Y-m-d_His').'.'.$extension;
    }

    protected function disk(): Filesystem
    {
        return Storage::disk((string) config('backup.disk'));
    }
}
