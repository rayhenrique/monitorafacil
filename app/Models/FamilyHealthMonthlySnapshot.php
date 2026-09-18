<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyHealthMonthlySnapshot extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'year',
        'month',
        'quarter',
        'month_in_quarter',
        'ine',
        'team_name',
        'team_type',
        'indicator_code',
        'numerator',
        'denominator',
        'score_percent',
        'performance_level',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'quarter' => 'integer',
            'month_in_quarter' => 'integer',
            'numerator' => 'integer',
            'denominator' => 'integer',
            'score_percent' => 'float',
        ];
    }
}
