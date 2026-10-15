<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup disk
    |--------------------------------------------------------------------------
    |
    | Backups are written to a private disk. The default `local` disk maps to
    | storage/app/private, which is never served by the web server.
    |
    */

    'disk' => env('BACKUP_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Backup directory
    |--------------------------------------------------------------------------
    |
    | Relative path (inside the disk root) where database dumps are stored.
    |
    */

    'path' => env('BACKUP_PATH', 'backups'),

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | How many days of backups to keep. Older files are removed automatically
    | after each successful backup.
    |
    */

    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | mysqldump binary
    |--------------------------------------------------------------------------
    |
    | Only used for the MySQL driver. On Windows/Laragon point this at the
    | full path of mysqldump.exe when it is not on the PATH.
    |
    */

    'mysqldump' => [
        'binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
        'timeout' => (int) env('BACKUP_TIMEOUT', 600),
    ],

];
