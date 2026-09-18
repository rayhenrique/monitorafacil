<?php

namespace App\Livewire\Settings;

use App\Enums\SyncStatus;
use App\Models\SyncLog;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AuditLogs extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    public ?int $selectedLogId = null;

    public bool $showDetailModal = false;

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function viewDetails(int $id): void
    {
        $this->selectedLogId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedLogId = null;
    }

    public function render(): View
    {
        $query = SyncLog::query()->orderByDesc('started_at');

        if ($this->statusFilter === 'success') {
            $query->where('status', SyncStatus::Success);
        } elseif ($this->statusFilter === 'failed') {
            $query->where('status', SyncStatus::Failed);
        } elseif ($this->statusFilter === 'running') {
            $query->where('status', SyncStatus::Running);
        }

        $logs = $query->paginate(15);

        $selectedLog = $this->selectedLogId !== null
            ? SyncLog::query()->find($this->selectedLogId)
            : null;

        return view('livewire.settings.audit-logs', [
            'logs' => $logs,
            'selectedLog' => $selectedLog,
        ]);
    }
}
