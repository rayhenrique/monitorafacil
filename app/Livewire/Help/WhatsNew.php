<?php

namespace App\Livewire\Help;

use App\Services\VersionService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class WhatsNew extends Component
{
    public string $filter = 'all'; // 'all' | 'novo' | 'melhoria' | 'correcao'

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function render(): View
    {
        $allReleases = VersionService::getAllReleases();

        // Se houver filtro específico, podemos manter o destaque ou filtrar
        $releases = $allReleases;

        if ($this->filter !== 'all') {
            $releases = array_values(array_filter($allReleases, function ($release) {
                foreach ($release['highlights'] as $item) {
                    if ($item['type'] === $this->filter) {
                        return true;
                    }
                }

                return false;
            }));
        }

        return view('livewire.help.whats-new', [
            'latestVersion' => VersionService::getLatestVersion(),
            'releases' => $releases,
            'totalReleases' => count($allReleases),
        ]);
    }
}
