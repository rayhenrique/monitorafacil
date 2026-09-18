<?php

namespace App\Models;

use App\Enums\TeamType;
use Illuminate\Database\Eloquent\Model;

class ConsolidationTeam extends Model
{
    /** @var list<string> */
    protected $fillable = ['year', 'quarter', 'type', 'total_active'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'type' => TeamType::class,
            'total_active' => 'integer',
        ];
    }
}
