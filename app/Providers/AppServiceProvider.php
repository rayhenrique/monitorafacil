<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as BladeView;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.app', static function (BladeView $view): void {
            $settingsService = app(SettingsService::class);
            $view->with('settings', $settingsService->all());
            $view->with('lastIndicatorsProcessedAt', $settingsService->getLastIndicatorsProcessedAt());
        });

        View::composer('auth.login', static function (BladeView $view): void {
            $view->with('settings', app(SettingsService::class)->all());
        });
    }
}
