<?php

namespace App\Livewire\Dashboard;

use Illuminate\View\View;
use Livewire\Component;

class QualityOverview extends Component
{
    public int $year;

    public int $quarter;

    /**
     * @return array<int, array{code: string, name: string, description: string, icon: string, optimal: int, good: int, sufficient: int, regular: int}>
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
            ],
        ];
    }

    /**
     * @return array<int, array{code: string, name: string, description: string, icon: string, optimal: int, good: int, sufficient: int, regular: int}>
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
            ],
        ];
    }

    public function render(): View
    {
        return view('livewire.dashboard.quality-overview', [
            'familyHealth' => $this->getFamilyHealthIndicators(),
            'oralHealth' => $this->getOralHealthIndicators(),
        ]);
    }
}
