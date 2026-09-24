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
 * @property int $practice_a_eligible
 * @property int $practice_a_compliant
 * @property float $practice_a_score
 * @property int $practice_b_eligible
 * @property int $practice_b_compliant
 * @property float $practice_b_score
 * @property int $practice_c_eligible
 * @property int $practice_c_compliant
 * @property float $practice_c_score
 * @property int $practice_d_eligible
 * @property int $practice_d_compliant
 * @property float $practice_d_score
 * @property float $final_score
 * @property array<int, int>|null $monthly_counts
 * @property Carbon|null $as_of
 * @property string $calculation_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class C7CohortSnapshot extends Model
{
    protected $fillable = [
        'year',
        'quarter',
        'ine',
        'team_name',
        'team_type',
        'cohort_total',
        'evaluated_total',
        'practice_a_eligible',
        'practice_a_compliant',
        'practice_a_score',
        'practice_b_eligible',
        'practice_b_compliant',
        'practice_b_score',
        'practice_c_eligible',
        'practice_c_compliant',
        'practice_c_score',
        'practice_d_eligible',
        'practice_d_compliant',
        'practice_d_score',
        'final_score',
        'monthly_counts',
        'as_of',
        'calculation_version',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'cohort_total' => 'integer',
            'evaluated_total' => 'integer',
            'practice_a_eligible' => 'integer',
            'practice_a_compliant' => 'integer',
            'practice_a_score' => 'float',
            'practice_b_eligible' => 'integer',
            'practice_b_compliant' => 'integer',
            'practice_b_score' => 'float',
            'practice_c_eligible' => 'integer',
            'practice_c_compliant' => 'integer',
            'practice_c_score' => 'float',
            'practice_d_eligible' => 'integer',
            'practice_d_compliant' => 'integer',
            'practice_d_score' => 'float',
            'final_score' => 'float',
            'monthly_counts' => 'array',
            'as_of' => 'date',
        ];
    }
}
