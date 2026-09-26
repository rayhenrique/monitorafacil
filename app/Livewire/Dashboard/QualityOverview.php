<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardSnapshotService;
use Illuminate\View\View;
use Livewire\Component;

class QualityOverview extends Component
{
    public int $year;

    public int $quarter;

    /**
     * @return array<int, array{code: string, name: string, description: string, icon: string, optimal: int, good: int, sufficient: int, regular: int, evaluated_teams: int, has_data: bool}>
     */
    public function getFamilyHealthIndicators(): array
    {
        return [
            [
                'code' => 'C1',
                'name' => 'Mais Acesso',
                'description' => 'Ampliação do Acesso à Atenção Básica.',
                'icon' => 'clinic',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'evaluated_teams' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'C2',
                'name' => 'Crianças',
                'description' => 'Cuidado no Desenvolvimento Infantil.',
                'icon' => 'child',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'evaluated_teams' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'C3',
                'name' => 'Cuidado da Gestante e Puérpera',
                'description' => 'Cuidado à Gestante e Puérpera na Atenção Primária à Saúde (APS).',
                'icon' => 'maternal',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'evaluated_teams' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'C4',
                'name' => 'Diabéticos',
                'description' => 'Acompanhamento de Diabetes Mellitus.',
                'icon' => 'diabetes',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'evaluated_teams' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'C5',
                'name' => 'Hipertensos',
                'description' => 'Acompanhamento de Hipertensão Arterial.',
                'icon' => 'heart',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'evaluated_teams' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'C6',
                'name' => 'Idosos',
                'description' => 'Atenção à Saúde do Idoso.',
                'icon' => 'elderly',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'evaluated_teams' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'C7',
                'name' => 'Mulheres',
                'description' => 'Saúde da Mulher e Prevenção.',
                'icon' => 'women',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'evaluated_teams' => 0,
                'has_data' => false,
            ],
        ];
    }

    /**
     * @return array<int, array{code: string, name: string, description: string, icon: string, optimal: int, good: int, sufficient: int, regular: int, has_data: bool}>
     */
    public function getOralHealthIndicators(): array
    {
        return [
            [
                'code' => 'B1',
                'name' => 'Primeira Consulta Programada',
                'description' => 'Acesso da população à primeira consulta odontológica programática.',
                'icon' => 'calendar',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'B2',
                'name' => 'Tratamento Concluído',
                'description' => 'Tratamentos concluídos em relação às primeiras consultas programáticas.',
                'icon' => 'teeth',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'B3',
                'name' => 'Taxa de Exodontias',
                'description' => 'Exodontias sobre o total de procedimentos preventivos e curativos. Quanto menor, melhor.',
                'icon' => 'tooth-minus',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'B4',
                'name' => 'Escovação Supervisionada',
                'description' => 'Crianças de 6 a 12 anos alcançadas pela escovação dental supervisionada.',
                'icon' => 'toothbrush',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'B5',
                'name' => 'Procedimentos Preventivos',
                'description' => 'Procedimentos preventivos sobre o total de procedimentos odontológicos individuais.',
                'icon' => 'shield',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'B6',
                'name' => 'Restauração Atraumática (ART)',
                'description' => 'ART sobre o total de procedimentos restauradores.',
                'icon' => 'sparkles',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'has_data' => false,
            ],
        ];
    }

    /**
     * @return array<int, array{code: string, name: string, description: string, icon: string, optimal: int, good: int, sufficient: int, regular: int, has_data: bool}>
     */
    public function getEMultiIndicators(): array
    {
        return [
            [
                'code' => 'M1',
                'name' => 'Atendimentos por pessoa',
                'description' => 'Média de atendimentos realizados pela eMulti por pessoa, nos últimos 4 meses.',
                'icon' => 'users-multi',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'has_data' => false,
            ],
            [
                'code' => 'M2',
                'name' => 'Ações interprofissionais',
                'description' => 'Ações de cuidado compartilhadas entre a eMulti e outros profissionais da APS.',
                'icon' => 'collaboration',
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'has_data' => false,
            ],
        ];
    }

    public function render(DashboardSnapshotService $snapshots): View
    {
        $operatorCnes = (auth()->user()?->isOperator() && auth()->user()->cnes) ? auth()->user()->cnes : null;

        $performance = $snapshots->familyHealthPerformance($this->year, $this->quarter, $operatorCnes);
        $familyHealth = array_map(function (array $indicator) use ($performance): array {
            $code = strtolower($indicator['code']);

            return isset($performance[$code])
                ? array_replace($indicator, $performance[$code])
                : $indicator;
        }, $this->getFamilyHealthIndicators());

        $oralPerformance = $snapshots->oralHealthPerformance($this->year, $this->quarter, $operatorCnes);
        $oralHealth = array_map(function (array $indicator) use ($oralPerformance): array {
            $code = strtolower($indicator['code']);

            return isset($oralPerformance[$code])
                ? array_replace($indicator, $oralPerformance[$code])
                : $indicator;
        }, $this->getOralHealthIndicators());

        return view('livewire.dashboard.quality-overview', [
            'familyHealth' => $familyHealth,
            'oralHealth' => $oralHealth,
            'eMulti' => $this->getEMultiIndicators(),
            'operatorFacility' => auth()->user()?->isOperator() ? (auth()->user()->facility_name ?: ('CNES ' . auth()->user()->cnes)) : null,
        ]);
    }
}
