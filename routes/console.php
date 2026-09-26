<?php

use App\Services\SettingsService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('esus:sync-snapshot')
    ->dailyAt('03:00')
    ->timezone(config('esus.schedule_timezone', 'America/Maceio'))
    ->withoutOverlapping();

// Rotina Noturna Automática Completa (20 etapas configuráveis pelo módulo Processar Dados)
try {
    $settings = app(SettingsService::class);
    $nightlyEnabled = $settings->get('nightly_routine_enabled', '1') !== '0';
    $nightlyTime = $settings->get('nightly_routine_time', '03:00') ?: '03:00';

    if ($nightlyEnabled) {
        Schedule::command('monitora:nightly-routine')
            ->dailyAt($nightlyTime)
            ->timezone(config('esus.schedule_timezone', 'America/Maceio'))
            ->withoutOverlapping();
    }
} catch (\Throwable) {
    Schedule::command('monitora:nightly-routine')
        ->dailyAt('03:00')
        ->timezone(config('esus.schedule_timezone', 'America/Maceio'))
        ->withoutOverlapping();
}

// Processamento analítico complementar agendado
Schedule::command('esus:process-data --scope=all')
    ->dailyAt('03:45')
    ->timezone(config('esus.schedule_timezone', 'America/Maceio'))
    ->withoutOverlapping();
