<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Livewire\FamilyHealth\FamilyHealthOverview;
use App\Livewire\FamilyHealth\IndicatorDetail;
use App\Livewire\Help\FillingGuide;
use App\Livewire\Help\WhatsNew;
use App\Livewire\Settings\AuditLogs;
use App\Livewire\Settings\CnesImport;
use App\Livewire\Settings\DataProcessing;
use App\Livewire\Settings\EsusConnection;
use App\Livewire\Settings\MunicipalitySettings;
use App\Livewire\Settings\UsersManager;
use App\Livewire\TerritorialBonding\TerritorialBondingOverview;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('vinculo-e-acompanhamento')->name('territorial-bonding.')->group(function (): void {
        Route::get('/', TerritorialBondingOverview::class)->name('overview');
    });

    Route::prefix('saude-da-familia')->name('family-health.')->group(function (): void {
        Route::get('/', FamilyHealthOverview::class)->name('overview');
        Route::get('/{indicator}', IndicatorDetail::class)->name('indicator');
    });

    Route::prefix('configuracoes')->name('settings.')->group(function (): void {
        Route::get('/usuarios', UsersManager::class)->name('users');
        Route::get('/municipio', MunicipalitySettings::class)->name('municipality');
        Route::get('/logs-auditoria', AuditLogs::class)->name('audit-logs');
        Route::get('/conexao-esus', EsusConnection::class)->name('esus-connection');
        Route::get('/processar-dados', DataProcessing::class)->name('data-processing');
        Route::get('/importar-cnes-xml', CnesImport::class)->name('cnes-import');
    });

    Route::prefix('ajuda')->name('help.')->group(function (): void {
        Route::get('/', fn () => redirect()->route('help.guide'));
        Route::get('/guia-preenchimento', FillingGuide::class)->name('guide');
        Route::get('/novidades', WhatsNew::class)->name('whats-new');
    });
});
