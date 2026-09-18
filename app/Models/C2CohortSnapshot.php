<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class C2CohortSnapshot extends Model
{
    protected $fillable = [
        'year', 'quarter', 'ine', 'team_name', 'team_type', 'cohort_total',
        'evaluated_total', 'monthly_counts', 'as_of', 'calculation_version',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'cohort_total' => 'integer',
            'evaluated_total' => 'integer',
            'monthly_counts' => 'array',
            'as_of' => 'date',
        ];
    }
}
