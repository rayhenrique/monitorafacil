<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('esus:sync-snapshot')
    ->dailyAt('03:00')
    ->timezone(config('esus.schedule_timezone'))
    ->withoutOverlapping();

// Processamento analítico agendado é SEMPRE o completo (scope=all)
Schedule::command('esus:process-data --scope=all')
    ->dailyAt('03:30')
    ->timezone(config('esus.schedule_timezone'))
    ->withoutOverlapping();
