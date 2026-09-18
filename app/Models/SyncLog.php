<?php

namespace App\Models;

use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    /** @var list<string> */
    protected $fillable = ['status', 'started_at', 'finished_at', 'error_message'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => SyncStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
