<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsolidationRegistration extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'year',
        'quarter',
        'mici_updated_count',
        'mici_outdated_count',
        'micdt_updated_count',
        'micdt_outdated_count',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'mici_updated_count' => 'integer',
            'mici_outdated_count' => 'integer',
            'micdt_updated_count' => 'integer',
            'micdt_outdated_count' => 'integer',
        ];
    }
}
