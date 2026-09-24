<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $year
 * @property int $quarter
 * @property int $cidadao_pec_id
 * @property string|null $cns
 * @property string|null $cpf
 * @property string $name
 * @property string|null $social_name
 * @property Carbon|null $birth_date
 * @property int $age_years
 * @property string|null $phone
 * @property string|null $race_color
 * @property string|null $cnes
 * @property string|null $facility_name
 * @property string|null $district
 * @property string|null $ine
 * @property string|null $team_name
 * @property string|null $team_type
 * @property string|null $professional_cns
 * @property string|null $professional_name
 * @property string|null $microarea
 * @property string|null $month_ref
 * @property bool $mici_updated
 * @property bool $is_accompanied
 * @property int $practice_a
 * @property bool $practice_a_met
 * @property Carbon|null $last_consultation_date
 * @property int $practice_b
 * @property bool $practice_b_met
 * @property Carbon|null $last_anthropometry_date
 * @property float|null $last_weight
 * @property float|null $last_height
 * @property int $practice_c
 * @property bool $practice_c_met
 * @property Carbon|null $last_visit_date
 * @property int $practice_d
 * @property bool $practice_d_met
 * @property Carbon|null $last_vaccine_date
 * @property string|null $last_vaccine_name
 * @property float $score_percent
 * @property string $calculation_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class C6NominalElderly extends Model
{
    protected $table = 'c6_nominal_elderly';

    protected $fillable = [
        'year',
        'quarter',
        'cidadao_pec_id',
        'cns',
        'cpf',
        'name',
        'social_name',
        'birth_date',
        'age_years',
        'phone',
        'race_color',
        'cnes',
        'facility_name',
        'district',
        'ine',
        'team_name',
        'team_type',
        'professional_cns',
        'professional_name',
        'microarea',
        'month_ref',
        'mici_updated',
        'is_accompanied',
        'practice_a',
        'practice_a_met',
        'last_consultation_date',
        'practice_b',
        'practice_b_met',
        'last_anthropometry_date',
        'last_weight',
        'last_height',
        'practice_c',
        'practice_c_met',
        'last_visit_date',
        'practice_d',
        'practice_d_met',
        'last_vaccine_date',
        'last_vaccine_name',
        'score_percent',
        'calculation_version',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'cidadao_pec_id' => 'integer',
            'birth_date' => 'date',
            'last_consultation_date' => 'date',
            'last_anthropometry_date' => 'date',
            'last_visit_date' => 'date',
            'last_vaccine_date' => 'date',
            'age_years' => 'integer',
            'mici_updated' => 'boolean',
            'is_accompanied' => 'boolean',
            'practice_a' => 'integer',
            'practice_a_met' => 'boolean',
            'practice_b' => 'integer',
            'practice_b_met' => 'boolean',
            'practice_c' => 'integer',
            'practice_c_met' => 'boolean',
            'practice_d' => 'integer',
            'practice_d_met' => 'boolean',
            'last_weight' => 'float',
            'last_height' => 'float',
            'score_percent' => 'float',
        ];
    }
}
