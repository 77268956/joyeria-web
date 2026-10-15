<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('metal-prices:fetch')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onFailure(function (): void {
        logger()->error('El comando metal-prices:fetch falló al actualizar los precios.');
    });

Schedule::command('backup:database')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->onFailure(function (): void {
        logger()->error('El comando backup:database falló al generar el respaldo.');
    });
