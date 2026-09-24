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
 * @property string $sex
 * @property string|null $gender_identity
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
 * @property bool $eligible_practice_a
 * @property bool $practice_a_met
 * @property int $practice_a_count
 * @property Carbon|null $last_cervical_exam_date
 * @property string|null $last_cervical_exam_code
 * @property string|null $last_cervical_exam_desc
 * @property bool $eligible_practice_b
 * @property bool $practice_b_met
 * @property int $practice_b_count
 * @property Carbon|null $last_hpv_vaccine_date
 * @property string|null $last_hpv_vaccine_code
 * @property string|null $last_hpv_vaccine_name
 * @property bool $eligible_practice_c
 * @property bool $practice_c_met
 * @property int $practice_c_count
 * @property Carbon|null $last_sexual_health_date
 * @property string|null $last_sexual_health_code
 * @property string|null $last_sexual_health_detail
 * @property bool $eligible_practice_d
 * @property bool $practice_d_met
 * @property int $practice_d_count
 * @property Carbon|null $last_mammogram_date
 * @property string|null $last_mammogram_code
 * @property string|null $last_mammogram_desc
 * @property float $score_percent
 * @property array<string>|null $pending_practices
 * @property string $calculation_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class C7NominalWoman extends Model
{
    protected $table = 'c7_nominal_women';

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
        'sex',
        'gender_identity',
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
        'eligible_practice_a',
        'practice_a_met',
        'practice_a_count',
        'last_cervical_exam_date',
        'last_cervical_exam_code',
        'last_cervical_exam_desc',
        'eligible_practice_b',
        'practice_b_met',
        'practice_b_count',
        'last_hpv_vaccine_date',
        'last_hpv_vaccine_code',
        'last_hpv_vaccine_name',
        'eligible_practice_c',
        'practice_c_met',
        'practice_c_count',
        'last_sexual_health_date',
        'last_sexual_health_code',
        'last_sexual_health_detail',
        'eligible_practice_d',
        'practice_d_met',
        'practice_d_count',
        'last_mammogram_date',
        'last_mammogram_code',
        'last_mammogram_desc',
        'score_percent',
        'pending_practices',
        'calculation_version',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'cidadao_pec_id' => 'integer',
            'birth_date' => 'date',
            'age_years' => 'integer',
            'mici_updated' => 'boolean',
            'is_accompanied' => 'boolean',
            'eligible_practice_a' => 'boolean',
            'practice_a_met' => 'boolean',
            'practice_a_count' => 'integer',
            'last_cervical_exam_date' => 'date',
            'eligible_practice_b' => 'boolean',
            'practice_b_met' => 'boolean',
            'practice_b_count' => 'integer',
            'last_hpv_vaccine_date' => 'date',
            'eligible_practice_c' => 'boolean',
            'practice_c_met' => 'boolean',
            'practice_c_count' => 'integer',
            'last_sexual_health_date' => 'date',
            'eligible_practice_d' => 'boolean',
            'practice_d_met' => 'boolean',
            'practice_d_count' => 'integer',
            'last_mammogram_date' => 'date',
            'score_percent' => 'float',
            'pending_practices' => 'array',
        ];
    }
}
