<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvatDimensionDistribution extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'year',
        'quarter',
        'quarter_label',
        'team_type',
        'dimension_code',
        'dimension_name',
        'regular_count',
        'sufficient_count',
        'good_count',
        'optimal_count',
        'total_teams',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'regular_count' => 'integer',
            'sufficient_count' => 'integer',
            'good_count' => 'integer',
            'optimal_count' => 'integer',
            'total_teams' => 'integer',
        ];
    }
}
