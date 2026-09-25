<?php

declare(strict_types=1);

namespace App\Models\OralHealth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OralHealthNominalCitizen extends Model
{
    use HasFactory;

    protected $table = 'oral_health_nominal_citizens';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'year',
        'quarter',
        'month',
        'cidadao_pec_id',
        'cns',
        'cpf',
        'name',
        'mother_name',
        'birth_date',
        'age_years',
        'gender',
        'race_color',
        'cnes',
        'facility_name',
        'ine',
        'team_name',
        'microarea',
        'district',
        'professional_cns',
        'professional_name',
        'mici_updated',
        'micdt_updated',
        'is_linked',
        'b1_count',
        'b2_count',
        'b3_count',
        'b4_count',
        'b4_eligible',
        'b5_count',
        'b6_count',
        'first_consultation_date',
        'treatment_completed_date',
        'last_brushing_date',
        'last_attendance_date',
        'last_professional_name',
        'last_professional_cbo',
        'treatment_status',
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
            'month' => 'integer',
            'cidadao_pec_id' => 'integer',
            'birth_date' => 'date',
            'age_years' => 'integer',
            'mici_updated' => 'boolean',
            'micdt_updated' => 'boolean',
            'is_linked' => 'boolean',
            'b1_count' => 'integer',
            'b2_count' => 'integer',
            'b3_count' => 'integer',
            'b4_count' => 'integer',
            'b4_eligible' => 'boolean',
            'b5_count' => 'integer',
            'b6_count' => 'integer',
            'first_consultation_date' => 'date',
            'treatment_completed_date' => 'date',
            'last_brushing_date' => 'date',
            'last_attendance_date' => 'date',
        ];
    }

    /**
     * Retorna o CNS mascarado para proteção de dados / LGPD.
     */
    public function getMaskedCnsAttribute(): string
    {
        if (empty($this->cns)) {
            return '---';
        }

        $c = preg_replace('/\D/', '', $this->cns);
        if (strlen($c) < 15) {
            return substr($c, 0, 3) . '***' . substr($c, -3);
        }

        return '***.' . substr($c, 3, 1) . '.' . substr($c, 4, 3) . '.' . substr($c, 7, 1) . '**.' . substr($c, 10, 3);
    }

    /**
     * Retorna o CPF mascarado para proteção de dados / LGPD.
     */
    public function getMaskedCpfAttribute(): string
    {
        if (empty($this->cpf)) {
            return '---';
        }

        $c = preg_replace('/\D/', '', $this->cpf);
        if (strlen($c) < 11) {
            return '***.***.***-**';
        }

        return '***.' . substr($c, 3, 3) . '.' . substr($c, 6, 2) . '*-**';
    }
}
