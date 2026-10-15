<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupCommandTest extends TestCase
{
    protected function useMysqlDriver(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'joyeria_web',
                'username' => 'root',
                'password' => 'secret',
            ],
            'backup.disk' => 'local',
            'backup.path' => 'backups',
        ]);
    }

    public function test_backup_database_stores_a_mysql_dump(): void
    {
        Storage::fake('local');
        Process::fake(['*' => Process::result('-- SQL DUMP --')]);
        $this->useMysqlDriver();

        $this->artisan('backup:database')->assertSuccessful();

        $files = Storage::disk('local')->files('backups');

        $this->assertCount(1, $files);
        $this->assertStringEndsWith('.sql', $files[0]);
        $this->assertStringContainsString('SQL DUMP', Storage::disk('local')->get($files[0]));

        Process::assertRan(fn ($process): bool => str_contains($process->command[0] ?? '', 'mysqldump'));
    }

    public function test_backup_prunes_files_older_than_retention(): void
    {
        Storage::fake('local');
        Process::fake(['*' => Process::result('-- SQL DUMP --')]);
        $this->useMysqlDriver();
        config(['backup.retention_days' => 30]);

        $old = 'backups/joyeriaweb_2020-01-01_000000.sql';
        Storage::disk('local')->put($old, 'viejo');
        touch(Storage::disk('local')->path($old), now()->subDays(60)->getTimestamp());

        $this->artisan('backup:database')->assertSuccessful();

        Storage::disk('local')->assertMissing($old);
        $this->assertCount(1, Storage::disk('local')->files('backups'));
    }

    public function test_backup_copies_a_sqlite_database_file(): void
    {
        Storage::fake('local');

        $database = tempnam(sys_get_temp_dir(), 'joyeria_db_');
        file_put_contents($database, 'SQLite format 3 fake');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
            'backup.disk' => 'local',
            'backup.path' => 'backups',
        ]);

        $this->artisan('backup:database')->assertSuccessful();

        $files = Storage::disk('local')->files('backups');

        $this->assertCount(1, $files);
        $this->assertStringEndsWith('.sqlite', $files[0]);
        $this->assertSame('SQLite format 3 fake', Storage::disk('local')->get($files[0]));
    }

    public function test_backup_restore_copies_the_dump_back_into_place(): void
    {
        Storage::fake('local');

        $database = tempnam(sys_get_temp_dir(), 'joyeria_db_');
        file_put_contents($database, 'estado actual');

        $dump = 'backups/joyeriaweb_2020-01-01_000000.sqlite';
        Storage::disk('local')->put($dump, 'contenido restaurado');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
            'backup.disk' => 'local',
            'backup.path' => 'backups',
        ]);

        $this->artisan('backup:restore', ['file' => $dump])
            ->expectsConfirmation("Esto sobrescribirá la base de datos actual con «{$dump}». ¿Continuar?", 'yes')
            ->assertSuccessful();

        $this->assertSame('contenido restaurado', file_get_contents($database));
    }

    public function test_backup_restore_can_be_cancelled(): void
    {
        Storage::fake('local');

        $database = tempnam(sys_get_temp_dir(), 'joyeria_db_');
        file_put_contents($database, 'estado actual');

        $dump = 'backups/joyeriaweb_2020-01-01_000000.sqlite';
        Storage::disk('local')->put($dump, 'contenido restaurado');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
            'backup.disk' => 'local',
            'backup.path' => 'backups',
        ]);

        $this->artisan('backup:restore', ['file' => $dump])
            ->expectsConfirmation("Esto sobrescribirá la base de datos actual con «{$dump}». ¿Continuar?", 'no')
            ->assertSuccessful();

        $this->assertSame('estado actual', file_get_contents($database));
    }
}
