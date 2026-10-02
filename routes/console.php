<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sigel:check-norma-tecnica ' . (now()->year + 1) . ' --guardar')
    ->weeklyOn(1, '08:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/norma-tecnica.log'));

// Backup automático diario a las 23:00
Schedule::command('sigel:backup')
    ->dailyAt('23:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));

// Limpieza semanal de backups antiguos
Schedule::command('backup:clean')
    ->weeklyOn(0, '02:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));
