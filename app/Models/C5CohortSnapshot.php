<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $year
 * @property int $quarter
 * @property string|null $ine
 * @property string|null $team_name
 * @property string|null $team_type
 * @property int $cohort_total
 * @property int $evaluated_total
 * @property array<int, int>|null $monthly_counts
 * @property Carbon|null $as_of
 * @property string $calculation_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class C5CohortSnapshot extends Model
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
