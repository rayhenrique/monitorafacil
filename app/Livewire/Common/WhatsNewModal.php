<?php

namespace App\Livewire\Common;

use App\Services\VersionService;
use Illuminate\View\View;
use Livewire\Component;

class WhatsNewModal extends Component
{
    public bool $show = false;

    /** @var array<string, mixed> */
    public array $release = [];

    public function mount(): void
    {
        $this->release = VersionService::getLatestRelease();
        $this->show = VersionService::shouldShowModal(auth()->user());
    }

    public function acknowledge(): void
    {
        if (auth()->check()) {
            VersionService::markAsSeen(auth()->user());
        }

        $this->show = false;
    }

    public function viewFullHistory(): mixed
    {
        if (auth()->check()) {
            VersionService::markAsSeen(auth()->user());
        }

        $this->show = false;

        return redirect()->route('help.whats-new');
    }

    public function render(): View
    {
        return view('livewire.common.whats-new-modal');
    }
}
