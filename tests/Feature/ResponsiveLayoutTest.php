<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ResponsiveLayoutTest extends TestCase
{
    public function test_global_responsive_contract_is_defined(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('.app-page', $css);
        $this->assertStringContainsString('.app-modal-panel', $css);
        $this->assertStringContainsString('scrollbar-gutter: stable', $css);
        $this->assertStringContainsString('@media (pointer: coarse)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }

    #[DataProvider('authenticatedPageProvider')]
    public function test_authenticated_pages_use_the_canonical_responsive_container(string $view): void
    {
        $markup = file_get_contents(resource_path("views/{$view}.blade.php"));

        $this->assertStringContainsString('class="app-page', $markup);
    }

    public static function authenticatedPageProvider(): array
    {
        return [
            ['dashboard'],
            ['livewire/family-health/overview'],
            ['livewire/family-health/indicator-detail'],
            ['livewire/territorial-bonding/overview'],
            ['livewire/territorial-bonding/nominal-list'],
            ['livewire/settings/users-manager'],
            ['livewire/settings/municipality-settings'],
            ['livewire/settings/audit-logs'],
            ['livewire/settings/esus-connection'],
            ['livewire/settings/data-processing'],
            ['livewire/settings/cnes-import'],
            ['livewire/help/filling-guide'],
            ['livewire/help/whats-new'],
        ];
    }

    public function test_dense_tables_and_mobile_dialogs_keep_overflow_local(): void
    {
        $indicator = file_get_contents(resource_path('views/livewire/family-health/indicator-detail.blade.php'));
        $nominal = file_get_contents(resource_path('views/livewire/territorial-bonding/nominal-list.blade.php'));

        $this->assertStringContainsString('overflow-x-auto', $indicator);
        $this->assertStringContainsString('min-w-[44rem]', $indicator);
        $this->assertStringContainsString('app-modal-panel', $indicator);
        $this->assertStringContainsString('min-w-[1300px]', $nominal);
        $this->assertStringContainsString('aria-modal="true"', $nominal);
    }
}
