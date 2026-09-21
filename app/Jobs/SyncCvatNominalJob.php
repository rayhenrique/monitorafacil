<?php

namespace App\Jobs;

use App\Services\CvatNominalDwService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class SyncCvatNominalJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(public int $year, public int $month) {}

    public function handle(CvatNominalDwService $service): void
    {
        $result = $service->syncFromPec(null, $this->year, $this->month);
        if (! $result['success']) {
            throw new RuntimeException($result['message']);
        }
    }
}
