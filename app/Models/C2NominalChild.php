<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class C2NominalChild extends Model
{
    protected $table = 'c2_nominal_children';

    protected $fillable = [
        'year',
        'quarter',
        'cidadao_pec_id',
        'cns',
        'cpf',
        'name',
        'mother_name',
        'birth_date',
        'age_months',
        'race_color',
        'cnes',
        'facility_name',
        'district',
        'ine',
        'team_name',
        'professional_cns',
        'professional_name',
        'month_ref',
        'microarea',
        'mici_updated',
        'micdt_updated',
        'is_accompanied',
        'practice_a',
        'practice_b',
        'practice_c',
        'practice_d',
        'practice_e',
        'practice_a_met',
        'practice_b_met',
        'practice_c_met',
        'practice_d_met',
        'practice_e_met',
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
            'age_months' => 'integer',
            'mici_updated' => 'boolean',
            'micdt_updated' => 'boolean',
            'is_accompanied' => 'boolean',
            'practice_a' => 'integer',
            'practice_b' => 'integer',
            'practice_c' => 'integer',
            'practice_d' => 'integer',
            'practice_e' => 'integer',
            'practice_a_met' => 'boolean',
            'practice_b_met' => 'boolean',
            'practice_c_met' => 'boolean',
            'practice_d_met' => 'boolean',
            'practice_e_met' => 'boolean',
            'score_percent' => 'float',
        ];
    }
}
