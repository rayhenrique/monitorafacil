<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvatNominalCitizen extends Model
{
    protected $fillable = [
        'cidadao_pec_id',
        'source',
        'care_contacts',
        'total_contacts',
        'registration_eligible',
        'cns',
        'cpf',
        'responsible_cns_cpf',
        'birth_date',
        'name',
        'age',
        'race_color',
        'gender',
        'cnes',
        'facility_name',
        'ine',
        'team_name',
        'professional_cns',
        'professional_name',
        'microarea',
        'mici_updated',
        'mici_date',
        'micdt_updated',
        'micdt_date',
        'has_micdt',
        'is_linked',
        'vulnerability_type',
        'social_benefit',
        'is_accompanied',
        'last_visit_date',
        'address',
        'year',
        'month',
    ];

    protected function casts(): array
    {
        return [
            'cidadao_pec_id' => 'integer',
            'care_contacts' => 'integer',
            'total_contacts' => 'integer',
            'registration_eligible' => 'boolean',
            'birth_date' => 'date',
            'age' => 'integer',
            'mici_updated' => 'boolean',
            'mici_date' => 'date',
            'micdt_updated' => 'boolean',
            'micdt_date' => 'date',
            'has_micdt' => 'boolean',
            'is_linked' => 'boolean',
            'is_accompanied' => 'boolean',
            'last_visit_date' => 'date',
            'year' => 'integer',
            'month' => 'integer',
        ];
    }

    /**
     * Retorna o CNS mascarado para LGPD (ex: ***.**7.268.5**.***).
     */
    public function getMaskedCnsAttribute(): string
    {
        if (empty($this->cns)) {
            return '---';
        }

        $c = preg_replace('/\D/', '', $this->cns);
        if (strlen($c) < 15) {
            return substr($c, 0, 3).'***'.substr($c, -3);
        }

        return '***.**'.substr($c, 4, 1).'.'.substr($c, 5, 3).'.'.substr($c, 8, 1).'**.'.substr($c, 11, 3);
    }

    /**
     * Retorna o CPF mascarado para LGPD (ex: ***.577.44*.-*).
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

        return '***.'.substr($c, 3, 3).'.'.substr($c, 6, 2).'*.-*';
    }

    /**
     * Retorna o CNS do profissional formatado com pontos (ex: 708.001.879.979.721).
     */
    public function getFormattedProfessionalCnsAttribute(): string
    {
        if (empty($this->professional_cns)) {
            return '---';
        }

        $c = preg_replace('/\D/', '', $this->professional_cns);
        if (strlen($c) === 15) {
            return substr($c, 0, 3).'.'.substr($c, 3, 3).'.'.substr($c, 6, 3).'.'.substr($c, 9, 3).'.'.substr($c, 12, 3);
        }

        return $this->professional_cns;
    }
}
