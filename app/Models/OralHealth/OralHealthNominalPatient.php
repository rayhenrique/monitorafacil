<?php

declare(strict_types=1);

namespace App\Models\OralHealth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OralHealthNominalPatient extends Model
{
    use HasFactory;

    protected $table = 'oral_health_nominal_patients';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'year',
        'quarter',
        'indicator_code',
        'cidadao_pec_id',
        'cns',
        'cpf',
        'name',
        'social_name',
        'birth_date',
        'age_years',
        'phone',
        'cnes',
        'facility_name',
        'ine',
        'team_name',
        'professional_name',
        'professional_cbo',
        'first_consultation_date',
        'treatment_completed_date',
        'treatment_status',
        'has_first_consultation',
        'has_treatment_completed',
        'has_supervised_brushing',
        'last_brushing_date',
        'preventive_procedures_count',
        'restorative_procedures_count',
        'art_procedures_count',
        'exodontia_procedures_count',
        'total_procedures_count',
        'score_percent',
        'calculation_version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'cidadao_pec_id' => 'integer',
            'birth_date' => 'date',
            'first_consultation_date' => 'date',
            'treatment_completed_date' => 'date',
            'last_brushing_date' => 'date',
            'age_years' => 'integer',
            'has_first_consultation' => 'boolean',
            'has_treatment_completed' => 'boolean',
            'has_supervised_brushing' => 'boolean',
            'preventive_procedures_count' => 'integer',
            'restorative_procedures_count' => 'integer',
            'art_procedures_count' => 'integer',
            'exodontia_procedures_count' => 'integer',
            'total_procedures_count' => 'integer',
            'score_percent' => 'decimal:2',
        ];
    }
}
