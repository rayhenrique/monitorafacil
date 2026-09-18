<?php

namespace App\Services;

use App\Enums\TeamType;
use App\Models\ConsolidationTeam;
use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\FamilyHealthMonthlySnapshot;
use App\Models\Setting;
use Illuminate\Support\Collection;

class FamilyHealthService
{
    /**
     * Retorna a lista com os metadados técnicos oficiais de todos os 7 indicadores C1 a C7.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getIndicatorsMetadata(): array
    {
        return [
            'c1' => [
                'code' => 'C1',
                'slug' => 'c1',
                'short_title' => 'Mais Acesso',
                'full_title' => 'Mais Acesso à Atenção Primária à Saúde (APS)',
                'category' => 'Acesso e Acolhimento',
                'target_population' => 'População Geral Atendida',
                'icon' => 'clinic',
                'color' => 'teal',
                'weight' => 1.0,
                'polarity' => 'Faixa Ideal',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica C1 - Mais acesso.pdf',
                'objective' => 'Verificar o percentual de acesso de demanda programada em relação ao total de demandas (espontânea e programada) na APS, monitorando o equilíbrio entre o acolhimento imediato e a longitudinalidade.',
                'numerator_desc' => 'Nº total de atendimentos por demanda programada (consulta agendada programada, cuidado continuado e consulta agendada).',
                'denominator_desc' => 'Nº total de atendimentos por todos os tipos de demandas (espontâneas e programadas).',
                'parameters' => [
                    'regular' => ['min' => 0.0, 'max' => 10.0, 'extra' => '> 70%', 'label' => 'Regular (≤ 10% ou > 70%)', 'points' => '0,25 pt', 'color' => 'red', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                    'sufficient' => ['min' => 10.01, 'max' => 30.0, 'label' => 'Suficiente (> 10% e ≤ 30%)', 'points' => '0,50 pt', 'color' => 'yellow', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'good' => ['min' => 30.01, 'max' => 50.0, 'label' => 'Bom (> 30% e ≤ 50%)', 'points' => '0,75 pt', 'color' => 'green', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'optimal' => ['min' => 50.01, 'max' => 70.0, 'label' => 'Ótimo (> 50% e ≤ 70%)', 'points' => '1,00 pt', 'color' => 'blue', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                ],
                'cbos' => ['2251-42 (Médico ESF)', '2251-70 (Médico Generalista)', '2251-30 (Médico MFC)', '2251-25 (Médico Clínico)', '2252-50 (Médico Ginecologista/Obstetra)', '2235-65 (Enfermeiro ESF)', '2235-05 (Enfermeiro)'],
                'good_practices' => [
                    ['letter' => 'A', 'title' => 'Atendimento em Consulta Agendada Programada', 'desc' => 'Consultas individuais direcionadas aos ciclos de vida e doenças crônicas prioritárias.', 'weight' => '100%'],
                    ['letter' => 'B', 'title' => 'Atendimento em Cuidado Continuado', 'desc' => 'Consultas sequenciais para acompanhamento longitudinal de pacientes com plano terapêutico.', 'weight' => '100%'],
                    ['letter' => 'C', 'title' => 'Atendimento em Consulta Agendada no Dia', 'desc' => 'Atendimentos agendados previamente conforme rotina estabelecida da UBS.', 'weight' => '100%'],
                ],
            ],

            'c2' => [
                'code' => 'C2',
                'slug' => 'c2',
                'short_title' => 'Desenvolvimento Infantil',
                'full_title' => 'Cuidado no Desenvolvimento Infantil na Atenção Primária à Saúde',
                'category' => 'Saúde da Criança',
                'target_population' => 'Crianças com até 2 anos de idade (0 a 24 meses)',
                'icon' => 'child',
                'color' => 'blue',
                'weight' => 2.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica C2 - Cuidado no desenvolvimento infantil.pdf',
                'objective' => 'Avaliar o acesso e monitoramento efetivo das crianças até dois anos de idade em relação aos episódios de cuidados necessários, incentivando a captação precoce e acompanhamento coordenado e contínuo.',
                'numerator_desc' => 'Somatório das boas práticas pontuadas para cada criança com até 2 anos de vida vinculada à equipe (0 a 100 pontos).',
                'denominator_desc' => 'Nº total de crianças com até 2 anos de vida vinculadas à equipe no período avaliado.',
                'parameters' => [
                    'regular' => ['min' => 0.0, 'max' => 25.0, 'label' => 'Regular (≤ 25%)', 'points' => '0,50 pt', 'color' => 'red', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                    'sufficient' => ['min' => 25.01, 'max' => 50.0, 'label' => 'Suficiente (> 25% e ≤ 50%)', 'points' => '1,00 pt', 'color' => 'yellow', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'good' => ['min' => 50.01, 'max' => 75.0, 'label' => 'Bom (> 50% e ≤ 75%)', 'points' => '1,50 pt', 'color' => 'green', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'optimal' => ['min' => 75.01, 'max' => 100.0, 'label' => 'Ótimo (> 75% e ≤ 100%)', 'points' => '2,00 pts', 'color' => 'blue', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                ],
                'cbos' => ['2235 (Enfermeiros)', '2231 / 2251 / 2252 / 2253 (Médicos)', '5151-05 (ACS)', '3222-55 (TACS)'],
                'good_practices' => [
                    ['letter' => 'A', 'title' => 'Primeira Consulta até 30 Dias de Vida', 'desc' => '1ª consulta presencial realizada por médico ou enfermeiro até o 30º dia de vida com indicação de Puericultura.', 'points' => 20],
                    ['letter' => 'B', 'title' => 'Ao Menos 9 Consultas de Puericultura até 2 Anos', 'desc' => 'Mínimo de 9 consultas presenciais ou remotas realizadas por médico ou enfermeiro até os 2 anos com Puericultura.', 'points' => 20],
                    ['letter' => 'C', 'title' => 'Ao Menos 9 Registros Antropométricos Simultâneos', 'desc' => 'Mínimo de 9 registros simultâneos de peso e altura realizados no mesmo dia até os dois anos de vida.', 'points' => 20],
                    ['letter' => 'D', 'title' => 'Duas Visitas Domiciliares do ACS/TACS', 'desc' => 'Primeira visita até 30 dias de vida e a segunda visita até os 6 meses de vida (pontuação integral para eAP tipo 76).', 'points' => 20],
                    ['letter' => 'E', 'title' => 'Esquema Vacinal Completo Recomendado', 'desc' => 'Todas as doses: Pentavalente (3 doses), VIP (3 doses), Tríplice/Tetraviral (2 doses após 12m) e Pneumocócica (2 doses).', 'points' => 20],
                ],
            ],

            'c3' => [
                'code' => 'C3',
                'slug' => 'c3',
                'short_title' => 'Gestação e Puerpério',
                'full_title' => 'Cuidado na Gestação e Puerpério na Atenção Primária à Saúde (APS)',
                'category' => 'Saúde Materna',
                'target_population' => 'Gestantes e Puérperas (até 42 dias pós-parto)',
                'icon' => 'maternal',
                'color' => 'rose',
                'weight' => 1.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica C3 - Cuidado na gestação e puerpério.pdf',
                'objective' => 'Avaliar o acesso e monitoramento integral durante a gestação e puerpério, com incentivo à captação precoce, acompanhamento odontológico, testes rápidos e cuidados no puerpério.',
                'numerator_desc' => 'Somatório das boas práticas pontuadas para a pessoa gestante e puérpera durante cada gestação.',
                'denominator_desc' => 'Nº total de gestantes e puérperas vinculadas à equipe no período avaliado.',
                'parameters' => [
                    'optimal' => ['min' => 75.01, 'max' => 100.0, 'label' => 'Ótimo (> 75% e ≤ 100%)', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'good' => ['min' => 50.01, 'max' => 75.0, 'label' => 'Bom (> 50% e ≤ 75%)', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                    'sufficient' => ['min' => 25.01, 'max' => 50.0, 'label' => 'Suficiente (> 25% e ≤ 50%)', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'regular' => ['min' => 0.0, 'max' => 25.0, 'label' => 'Regular (≤ 25%)', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                ],
                'cbos' => ['2235 (Enfermeiros)', '2231/2251/2252/2253 (Médicos)', '2232 (Cirurgiões-Dentistas)', '5151-05 (ACS)'],
                'good_practices' => [
                    ['letter' => 'A', 'title' => 'Captação Precoce (até 12ª semana)', 'desc' => '1ª consulta pré-natal presencial ou remota realizada até a 12ª semana de idade gestacional.', 'points' => 15],
                    ['letter' => 'B', 'title' => 'Ao Menos 7 Consultas de Pré-Natal', 'desc' => 'Mínimo de 7 consultas presenciais ou remotas realizadas por médico ou enfermeiro.', 'points' => 15],
                    ['letter' => 'C', 'title' => 'Ao Menos 7 Aferições de Pressão Arterial', 'desc' => 'Aferições de PA registradas ao longo das consultas pré-natais.', 'points' => 10],
                    ['letter' => 'D', 'title' => 'Ao Menos 7 Registros de Peso e Altura', 'desc' => 'Avaliação antropométrica com peso e altura simultâneos nas consultas.', 'points' => 10],
                    ['letter' => 'E', 'title' => 'Três Visitas Domiciliares do ACS', 'desc' => 'Mínimo de 3 visitas realizadas após o início do pré-natal.', 'points' => 10],
                    ['letter' => 'F', 'title' => 'Vacina dTpa a partir da 20ª semana', 'desc' => 'Registro da vacina acelular dTpa durante a gestação.', 'points' => 10],
                    ['letter' => 'G', 'title' => 'Exames do 1º Trimestre (Sífilis, HIV, Hep B e C)', 'desc' => 'Testes rápidos ou exames laboratoriais avaliados no 1º trimestre.', 'points' => 10],
                    ['letter' => 'H', 'title' => 'Exames do 3º Trimestre (Sífilis e HIV)', 'desc' => 'Testes rápidos ou exames laboratoriais avaliados no 3º trimestre.', 'points' => 5],
                    ['letter' => 'I', 'title' => 'Consulta no Puerpério (até 42 dias)', 'desc' => 'Ao menos 1 consulta médica ou de enfermagem realizada no puerpério.', 'points' => 5],
                    ['letter' => 'J', 'title' => 'Visita Domiciliar no Puerpério', 'desc' => 'Ao menos 1 visita do ACS realizada nos primeiros 42 dias pós-parto.', 'points' => 5],
                    ['letter' => 'K', 'title' => 'Atividade de Saúde Bucal na Gestação', 'desc' => 'Consulta odontológica ou atividade com cirurgião-dentista ou TSB.', 'points' => 5],
                ],
            ],

            'c4' => [
                'code' => 'C4',
                'slug' => 'c4',
                'short_title' => 'Pessoas com Diabetes',
                'full_title' => 'Cuidado da Pessoa com Diabetes na Atenção Primária à Saúde',
                'category' => 'Condições Crônicas',
                'target_population' => 'Pessoas diagnosticadas com Diabetes (CIAP-2 T89/T90 ou CID-10 E10/E11/E14)',
                'icon' => 'diabetes',
                'color' => 'amber',
                'weight' => 1.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica C4 - Cuidado da pessoa com diabetes.pdf',
                'objective' => 'Monitorar o acompanhamento contínuo e a atenção integral da pessoa com diabetes, prevenindo complicações agudas e crônicas através de boas práticas clínicas e de vigilância.',
                'numerator_desc' => 'Somatório das boas práticas pontuadas para a pessoa com diabetes no período.',
                'denominator_desc' => 'Nº total de pessoas com diabetes vinculadas à equipe no período.',
                'parameters' => [
                    'optimal' => ['min' => 75.01, 'max' => 100.0, 'label' => 'Ótimo (> 75% e ≤ 100%)', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'good' => ['min' => 50.01, 'max' => 75.0, 'label' => 'Bom (> 50% e ≤ 75%)', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                    'sufficient' => ['min' => 25.01, 'max' => 50.0, 'label' => 'Suficiente (> 25% e ≤ 50%)', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'regular' => ['min' => 0.0, 'max' => 25.0, 'label' => 'Regular (≤ 25%)', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                ],
                'cbos' => ['2235 (Enfermeiros)', '2231/2251/2252/2253 (Médicos)', '5151-05 (ACS)', '3222-55 (TACS)'],
                'good_practices' => [
                    ['letter' => 'A', 'title' => 'Consulta Médica ou de Enfermagem no Semestre', 'desc' => 'Pelo menos 1 consulta presencial ou remota nos últimos 6 meses.', 'points' => 25],
                    ['letter' => 'B', 'title' => 'Aferição de Pressão Arterial no Semestre', 'desc' => 'Pelo menos 1 registro de aferição de PA nos últimos 6 meses.', 'points' => 15],
                    ['letter' => 'C', 'title' => 'Registro de Peso e Altura no Ano', 'desc' => 'Ao menos 1 registro simultâneo de peso e altura nos últimos 12 meses.', 'points' => 15],
                    ['letter' => 'D', 'title' => 'Duas Visitas Domiciliares do ACS no Ano', 'desc' => 'Pelo menos 2 visitas com intervalo mínimo de 30 dias nos últimos 12 meses.', 'points' => 15],
                    ['letter' => 'E', 'title' => 'Solicitação ou Avaliação de Hemoglobina Glicada', 'desc' => 'Registro de exame de HbA1c (SIGTAP 02.02.01.050-3 ou ABEX008) nos últimos 12 meses.', 'points' => 15],
                    ['letter' => 'F', 'title' => 'Avaliação dos Pés (Exame do Pé Diabético)', 'desc' => 'Procedimento SIGTAP 03.01.04.009-5 realizado nos últimos 12 meses.', 'points' => 15],
                ],
            ],

            'c5' => [
                'code' => 'C5',
                'slug' => 'c5',
                'short_title' => 'Pessoas com Hipertensão',
                'full_title' => 'Cuidado da Pessoa com Hipertensão na Atenção Primária à Saúde',
                'category' => 'Condições Crônicas',
                'target_population' => 'Pessoas diagnosticadas com Hipertensão (CIAP-2 K86/K87 ou CID-10 I10 a I15, O10, O11)',
                'icon' => 'heart',
                'color' => 'red',
                'weight' => 1.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica C5 - Cuidado da pessoa com hipertensão.pdf',
                'objective' => 'Avaliar o acesso, acompanhamento coordenado e monitoramento integral das pessoas com hipertensão arterial, visando o controle pressórico e redução do risco cardiovascular.',
                'numerator_desc' => 'Somatório das boas práticas pontuadas para a pessoa com hipertensão no período.',
                'denominator_desc' => 'Nº total de pessoas com hipertensão vinculadas à equipe no período.',
                'parameters' => [
                    'optimal' => ['min' => 75.01, 'max' => 100.0, 'label' => 'Ótimo (> 75% e ≤ 100%)', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'good' => ['min' => 50.01, 'max' => 75.0, 'label' => 'Bom (> 50% e ≤ 75%)', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                    'sufficient' => ['min' => 25.01, 'max' => 50.0, 'label' => 'Suficiente (> 25% e ≤ 50%)', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'regular' => ['min' => 0.0, 'max' => 25.0, 'label' => 'Regular (≤ 25%)', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                ],
                'cbos' => ['2235 (Enfermeiros)', '2231/2251/2252/2253 (Médicos)', '5151-05 (ACS)', '3222-55 (TACS)'],
                'good_practices' => [
                    ['letter' => 'A', 'title' => 'Consulta Médica ou de Enfermagem no Semestre', 'desc' => 'Pelo menos 1 consulta presencial ou remota nos últimos 6 meses.', 'points' => 35],
                    ['letter' => 'B', 'title' => 'Aferição de Pressão Arterial no Semestre', 'desc' => 'Pelo menos 1 registro de aferição de PA nos últimos 6 meses.', 'points' => 25],
                    ['letter' => 'C', 'title' => 'Registro de Peso e Altura no Ano', 'desc' => 'Ao menos 1 registro simultâneo de peso e altura nos últimos 12 meses.', 'points' => 20],
                    ['letter' => 'D', 'title' => 'Duas Visitas Domiciliares do ACS no Ano', 'desc' => 'Pelo menos 2 visitas com intervalo mínimo de 30 dias nos últimos 12 meses.', 'points' => 20],
                ],
            ],

            'c6' => [
                'code' => 'C6',
                'slug' => 'c6',
                'short_title' => 'Pessoa Idosa',
                'full_title' => 'Cuidado Integral à Pessoa Idosa na Atenção Primária à Saúde (APS)',
                'category' => 'Ciclos de Vida',
                'target_population' => 'Pessoas com idade igual ou superior a 60 anos (≥ 60 anos)',
                'icon' => 'elderly',
                'color' => 'indigo',
                'weight' => 1.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica C6 - Cuidado da pessoa idosa.pdf',
                'objective' => 'Avaliar o acesso e o cuidado longitudinal das pessoas idosas na APS, estimulando o envelhecimento ativo, vigilância nutricional, prevenção de quedas e imunização.',
                'numerator_desc' => 'Somatório das boas práticas pontuadas para cada pessoa idosa (≥ 60 anos) durante o acompanhamento.',
                'denominator_desc' => 'Nº total de pessoas idosas com 60 anos ou mais vinculadas à equipe no período.',
                'parameters' => [
                    'optimal' => ['min' => 75.01, 'max' => 100.0, 'label' => 'Ótimo (> 75% e ≤ 100%)', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'good' => ['min' => 50.01, 'max' => 75.0, 'label' => 'Bom (> 50% e ≤ 75%)', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                    'sufficient' => ['min' => 25.01, 'max' => 50.0, 'label' => 'Suficiente (> 25% e ≤ 50%)', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'regular' => ['min' => 0.0, 'max' => 25.0, 'label' => 'Regular (≤ 25%)', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                ],
                'cbos' => ['2235 (Enfermeiros)', '2231/2251/2252/2253 (Médicos)', '5151-05 (ACS)', '3222-55 (TACS)'],
                'good_practices' => [
                    ['letter' => 'A', 'title' => 'Consulta Médica ou de Enfermagem Anual', 'desc' => 'Pelo menos 1 consulta presencial ou remota realizada nos últimos 12 meses.', 'points' => 30],
                    ['letter' => 'B', 'title' => 'Avaliação Antropométrica (Peso e Altura) no Ano', 'desc' => 'Pelo menos 1 registro simultâneo de peso e altura nos últimos 12 meses.', 'points' => 25],
                    ['letter' => 'C', 'title' => 'Duas Visitas Domiciliares do ACS no Ano', 'desc' => 'Pelo menos 2 visitas domiciliares com intervalo mínimo de 30 dias em 12 meses.', 'points' => 25],
                    ['letter' => 'D', 'title' => 'Vacinação contra Influenza no Ano', 'desc' => 'Pelo menos 1 dose da vacina influenza registrada nos últimos 12 meses.', 'points' => 20],
                ],
            ],

            'c7' => [
                'code' => 'C7',
                'slug' => 'c7',
                'short_title' => 'Prevenção do Câncer',
                'full_title' => 'Cuidado da Mulher e do Homem Transgênero na Prevenção do Câncer na APS',
                'category' => 'Saúde da Mulher & Prevenção',
                'target_population' => 'Mulheres e homens trans de 9 a 69 anos de idade',
                'icon' => 'women',
                'color' => 'purple',
                'weight' => 1.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica C7 - Cuidado da mulher na prevenção do câncer.pdf',
                'objective' => 'Monitorar o rastreamento organizado do câncer do colo do útero e de mama, a cobertura da vacina HPV em adolescentes e o acesso a ações de saúde sexual e reprodutiva.',
                'numerator_desc' => 'Fórmula ponderada: (A × 20) + (B × 30) + (C × 30) + (D × 20) onde cada letra representa a cobertura da respectiva boa prática.',
                'denominator_desc' => 'População elegível vinculada em cada uma das 4 faixas etárias correspondentes.',
                'parameters' => [
                    'optimal' => ['min' => 75.01, 'max' => 100.0, 'label' => 'Ótimo (> 75% e ≤ 100%)', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'good' => ['min' => 50.01, 'max' => 75.0, 'label' => 'Bom (> 50% e ≤ 75%)', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                    'sufficient' => ['min' => 25.01, 'max' => 50.0, 'label' => 'Suficiente (> 25% e ≤ 50%)', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'regular' => ['min' => 0.0, 'max' => 25.0, 'label' => 'Regular (≤ 25%)', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                ],
                'cbos' => ['2235 (Enfermeiros)', '2231/2251/2252/2253 (Médicos)', '3222 (Técnicos de Enfermagem)'],
                'good_practices' => [
                    ['letter' => 'A', 'title' => 'Rastreamento Câncer Colo de Útero (25 a 64 anos)', 'desc' => 'Citopatológico nos últimos 36 meses ou teste molecular HPV nos últimos 60 meses.', 'weight_pct' => '20%'],
                    ['letter' => 'B', 'title' => 'Vacinação contra HPV para Meninas (9 a 14 anos)', 'desc' => 'Pelo menos 1 dose administrada da vacina HPV na faixa etária.', 'weight_pct' => '30%'],
                    ['letter' => 'C', 'title' => 'Saúde Sexual e Reprodutiva (14 a 69 anos)', 'desc' => 'Pelo menos 1 atendimento sobre planejamento reprodutivo ou saúde sexual nos últimos 12 meses.', 'weight_pct' => '30%'],
                    ['letter' => 'D', 'title' => 'Rastreamento Câncer de Mama (50 a 69 anos)', 'desc' => 'Mamografia bilateral de rastreamento solicitada ou avaliada nos últimos 24 meses.', 'weight_pct' => '20%'],
                ],
            ],
        ];
    }

    /**
     * Retorna os metadados de um indicador pelo código (ex: 'c1', 'c2').
     *
     * @return array<string, mixed>|null
     */
    public static function getIndicatorMeta(string $code): ?array
    {
        $code = strtolower($code);
        $all = self::getIndicatorsMetadata();

        return $all[$code] ?? null;
    }

    /**
     * Calcula a classificação de desempenho com base no parâmetro oficial do Ministério da Saúde.
     */
    public static function calculatePerformanceLevel(string $code, float $score): string
    {
        $code = strtolower($code);

        if ($code === 'c1') {
            if ($score > 50.0 && $score <= 70.0) {
                return 'otimo';
            }
            if ($score > 30.0 && $score <= 50.0) {
                return 'bom';
            }
            if ($score > 10.0 && $score <= 30.0) {
                return 'suficiente';
            }

            return 'regular';
        }

        // Para C2 a C7: Polaridade maior é melhor
        if ($score > 75.0) {
            return 'otimo';
        }
        if ($score > 50.0) {
            return 'bom';
        }
        if ($score > 25.0) {
            return 'suficiente';
        }

        return 'regular';
    }

    /**
     * Retorna a pontuação do conceito no Componente III - Qualidade (Quadro 2 / NT 08/2026).
     * Base: Ótimo = 1,00 | Bom = 0,75 | Suficiente = 0,50 | Regular = 0,25.
     * Multiplica pelo peso do indicador (ex: C1 peso 1.0 = até 1,00 pt; C2 peso 2.0 = até 2,00 pts).
     */
    public static function calculateComponentIIIPoints(string $performanceLevel, float $weight = 1.0): float
    {
        $base = match (strtolower($performanceLevel)) {
            'otimo' => 1.00,
            'bom' => 0.75,
            'suficiente' => 0.50,
            default => 0.25,
        };

        return round($base * $weight, 2);
    }

    /**
     * Retorna a visão geral municipal de todos os indicadores C1 a C7 para o quadrimestre.
     *
     * @return array<string, mixed>
     */
    public function getMunicipalOverview(int $year, int $quarter): array
    {
        $this->ensureBaselineSnapshots($year, $quarter);

        $indicatorsMeta = self::getIndicatorsMetadata();
        $snapshots = FamilyHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNull('ine') // Consolidado municipal
            ->get()
            ->keyBy(fn ($item) => strtolower($item->indicator_code));

        $cards = [];
        $totalScoreSum = 0.0;
        $count = 0;

        foreach ($indicatorsMeta as $slug => $meta) {
            $snap = $snapshots->get($slug);
            $score = $snap ? (float) $snap->score_percent : 0.0;
            $level = self::calculatePerformanceLevel($slug, $score);

            $cards[$slug] = [
                'meta' => $meta,
                'numerator' => $snap ? $snap->numerator : 0,
                'denominator' => $snap ? $snap->denominator : 0,
                'score_percent' => $score,
                'performance_level' => $level,
                'active_search_count' => $snap ? $snap->active_search_count : 0,
            ];

            $totalScoreSum += $score;
            $count++;
        }

        $municipalAverageScore = $count > 0 ? round($totalScoreSum / $count, 1) : 0.0;

        $teamsCount = (int) (ConsolidationTeam::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->where('type', TeamType::Esf->value)
            ->value('total_active') ?? 0);

        if ($teamsCount === 0) {
            $teamsCount = FamilyHealthIndicatorSnapshot::query()
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->whereNotNull('ine')
                ->where('indicator_code', 'c1')
                ->count();
        }

        return [
            'year' => $year,
            'quarter' => $quarter,
            'municipal_average_score' => $municipalAverageScore,
            'active_teams_count' => $teamsCount > 0 ? $teamsCount : 12,
            'indicators' => $cards,
        ];
    }

    /**
     * Retorna os meses que compõem o quadrimestre (Mês 1 ao Mês 4).
     *
     * @return array<int, array{number: int, name: string, label: string}>
     */
    public static function getMonthsForQuarter(int $quarter): array
    {
        return match ($quarter) {
            1 => [
                1 => ['number' => 1, 'name' => 'Janeiro', 'label' => 'Mês 1 (Jan)'],
                2 => ['number' => 2, 'name' => 'Fevereiro', 'label' => 'Mês 2 (Fev)'],
                3 => ['number' => 3, 'name' => 'Março', 'label' => 'Mês 3 (Mar)'],
                4 => ['number' => 4, 'name' => 'Abril', 'label' => 'Mês 4 (Abr)'],
            ],
            2 => [
                5 => ['number' => 5, 'name' => 'Maio', 'label' => 'Mês 1 (Mai)'],
                6 => ['number' => 6, 'name' => 'Junho', 'label' => 'Mês 2 (Jun)'],
                7 => ['number' => 7, 'name' => 'Julho', 'label' => 'Mês 3 (Jul)'],
                8 => ['number' => 8, 'name' => 'Agosto', 'label' => 'Mês 4 (Ago)'],
            ],
            default => [
                9 => ['number' => 9, 'name' => 'Setembro', 'label' => 'Mês 1 (Set)'],
                10 => ['number' => 10, 'name' => 'Outubro', 'label' => 'Mês 2 (Out)'],
                11 => ['number' => 11, 'name' => 'Novembro', 'label' => 'Mês 3 (Nov)'],
                12 => ['number' => 12, 'name' => 'Dezembro', 'label' => 'Mês 4 (Dez)'],
            ],
        };
    }

    /**
     * Retorna os detalhes de um indicador específico (C1 a C7) incluindo equipes e boas práticas.
     *
     * @return array<string, mixed>
     */
    public function getIndicatorDetail(string $code, int $year, int $quarter, ?string $selectedIne = null): array
    {
        $code = strtolower($code);
        $this->ensureBaselineSnapshots($year, $quarter);

        $meta = self::getIndicatorMeta($code);
        if (! $meta) {
            abort(404, "Indicador {$code} não encontrado.");
        }

        // Consolidado municipal
        $municipalSnap = FamilyHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->where('indicator_code', $code)
            ->whereNull('ine')
            ->first();

        // Lista por equipes
        $teamsQuery = FamilyHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->where('indicator_code', $code)
            ->whereNotNull('ine');

        if (in_array($code, ['c1', 'c2'])) {
            $teamsQuery->where(function ($q) {
                $q->whereIn('team_type', ['70', '76'])
                  ->orWhereNull('team_type');
            })
            ->where('team_name', 'not like', 'ESB%')
            ->where('team_name', 'not like', 'esb%')
            ->where('team_name', 'not like', '%E-MULTI%')
            ->where('team_name', 'not like', '%e-multi%')
            ->where('team_name', 'not like', '%EQUIPE AMPLIADA%')
            ->where('team_name', 'not like', '%equipe ampliada%')
            ->where('team_name', 'not like', '%EMAD%')
            ->where('team_name', 'not like', '%EMAP%')
            ->where('team_name', 'not like', '%SEM EQUIPE%')
            ->where('team_name', 'not like', '%INE N%O ENCONTRADO%');
        }

        $teams = $teamsQuery->orderByDesc('score_percent')->get();

        // Se uma equipe foi filtrada
        $currentFocus = null;
        if ($selectedIne) {
            $currentFocus = $teams->firstWhere('ine', $selectedIne);
        }

        $activeSnap = $currentFocus ?? $municipalSnap;

        $detail = [
            'meta' => $meta,
            'municipal_score' => $municipalSnap ? (float) $municipalSnap->score_percent : 0.0,
            'municipal_level' => $municipalSnap ? $municipalSnap->performance_level : 'regular',
            'current' => [
                'numerator' => $activeSnap ? $activeSnap->numerator : 0,
                'denominator' => $activeSnap ? $activeSnap->denominator : 0,
                'score_percent' => $activeSnap ? (float) $activeSnap->score_percent : 0.0,
                'performance_level' => $activeSnap ? $activeSnap->performance_level : 'regular',
                'active_search_count' => $activeSnap ? $activeSnap->active_search_count : 0,
                'good_practices_breakdown' => $activeSnap ? $activeSnap->good_practices_breakdown : [],
            ],
            'teams' => $teams,
            'active_search_list' => $this->generateActiveSearchSample($code, $activeSnap ? $activeSnap->active_search_count : 15, $selectedIne),
        ];

        // Lógica específica para Indicadores com Acompanhamento Mensal e Avaliação Quadrimestral (C1 e C2 - NT 08/2026)
        if (in_array($code, ['c1', 'c2'])) {
            $weight = (float) ($meta['weight'] ?? 1.0);
            $monthsConfig = self::getMonthsForQuarter($quarter);

            // 1. Acompanhamento Mensal do Escopo Ativo (Equipe Selecionada ou Consolidado Municipal)
            $monthlySnapshots = FamilyHealthMonthlySnapshot::query()
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->where('indicator_code', $code)
                ->when($selectedIne, fn ($q) => $q->where('ine', $selectedIne), fn ($q) => $q->whereNull('ine'))
                ->get()
                ->keyBy('month');

            $monthlyEvolution = [];
            $sumScores = 0.0;
            $countedMonths = 0;

            foreach ($monthsConfig as $mNum => $cfg) {
                $snap = $monthlySnapshots->get($mNum);
                $mScore = $snap ? (float) $snap->score_percent : 0.0;
                $mLevel = $snap ? $snap->performance_level : self::calculatePerformanceLevel($code, $mScore);
                $mPoints = self::calculateComponentIIIPoints($mLevel, $weight);

                $monthlyEvolution[] = [
                    'month_number' => $mNum,
                    'month_name' => $cfg['name'],
                    'label' => $cfg['label'],
                    'month_in_quarter' => count($monthlyEvolution) + 1,
                    'numerator' => $snap ? $snap->numerator : 0,
                    'denominator' => $snap ? $snap->denominator : 0,
                    'score_percent' => $mScore,
                    'performance_level' => $mLevel,
                    'component_iii_points' => $mPoints,
                ];

                $sumScores += $mScore;
                $countedMonths++;
            }

            // Média aritmética simples dos 4 meses conforme NT 08/2026: (M1 + M2 + M3 + M4) / 4
            $quarterAvgScore = $countedMonths > 0 ? round($sumScores / $countedMonths, 2) : ($activeSnap ? (float) $activeSnap->score_percent : 0.0);
            $quarterLevel = self::calculatePerformanceLevel($code, $quarterAvgScore);
            $quarterPoints = self::calculateComponentIIIPoints($quarterLevel, $weight);

            $detail['current']['score_percent'] = $quarterAvgScore;
            $detail['current']['performance_level'] = $quarterLevel;
            $detail['current']['component_iii_points'] = $quarterPoints;
            $detail['monthly_evolution'] = $monthlyEvolution;
            $detail['quarter_summary'] = [
                'average_score' => $quarterAvgScore,
                'performance_level' => $quarterLevel,
                'component_iii_points' => $quarterPoints,
                'weight' => $weight,
                'weighted_score' => $quarterPoints,
                'formula' => 'Média Aritmética: (Mês 1 + Mês 2 + Mês 3 + Mês 4) / 4',
                'balance_status' => match (true) {
                    $code === 'c1' && $quarterAvgScore > 70.0 => 'excess_programmatic',
                    $code === 'c1' && $quarterAvgScore < 30.0 => 'excess_spontaneous',
                    $quarterAvgScore > 75.0 => 'optimal',
                    $quarterAvgScore > 50.0 => 'good',
                    $quarterAvgScore > 25.0 => 'sufficient',
                    default => 'regular',
                },
            ];

            // Atalhos específicos para retrocompatibilidade
            $detail[$code.'_monthly_evolution'] = $monthlyEvolution;
            $detail[$code.'_quarter_summary'] = $detail['quarter_summary'];
            if ($code === 'c1') {
                $detail['c1_monthly_evolution'] = $monthlyEvolution;
                $detail['c1_quarter_summary'] = $detail['quarter_summary'];
            }

            // 2. Decorar as equipes com seus resultados mensais M1..M4 e pontos do Componente III
            $allTeamsMonthly = FamilyHealthMonthlySnapshot::query()
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->where('indicator_code', $code)
                ->whereNotNull('ine')
                ->get()
                ->groupBy('ine');

            $agendaAlerts = [];

            foreach ($teams as $team) {
                $tSnaps = $allTeamsMonthly->get($team->ine) ?? collect();
                $scoresByMonth = [];
                $detailsByMonth = [];
                $monthIdx = 1;
                foreach ($monthsConfig as $mNum => $cfg) {
                    $found = $tSnaps->firstWhere('month', $mNum);
                    $mScore = $found ? (float) $found->score_percent : 0.0;
                    $mLevel = $found ? $found->performance_level : self::calculatePerformanceLevel($code, $mScore);
                    $mNumAtend = $found ? (int) $found->numerator : 0;
                    $mDenAtend = $found ? (int) $found->denominator : 0;

                    $scoresByMonth[$monthIdx] = $mScore;
                    $detailsByMonth[$monthIdx] = [
                        'month_number' => $mNum,
                        'month_in_quarter' => $monthIdx,
                        'name' => $cfg['name'],
                        'label' => $cfg['label'],
                        'numerator' => $mNumAtend,
                        'denominator' => $mDenAtend,
                        'spontaneous' => max(0, $mDenAtend - $mNumAtend),
                        'score_percent' => $mScore,
                        'performance_level' => $mLevel,
                        'component_iii_points' => self::calculateComponentIIIPoints($mLevel, $weight),
                    ];
                    $monthIdx++;
                }

                $tAvg = count($scoresByMonth) > 0 ? round(array_sum($scoresByMonth) / count($scoresByMonth), 2) : (float) $team->score_percent;
                $tLevel = self::calculatePerformanceLevel($code, $tAvg);
                $tPoints = self::calculateComponentIIIPoints($tLevel, $weight);

                $team->monthly_scores = $scoresByMonth;
                $team->monthly_details = $detailsByMonth;
                $team->quarter_average = $tAvg;
                $team->quarter_level = $tLevel;
                $team->component_iii_points = $tPoints;

                if ($code === 'c1') {
                    if ($tAvg > 70.0) {
                        $team->agenda_status = 'excess_programmatic';
                        $agendaAlerts[] = [
                            'team_name' => $team->team_name,
                            'ine' => $team->ine,
                            'average' => $tAvg,
                            'status' => 'excess_programmatic',
                            'title' => 'Risco de Barreira à Demanda Espontânea (> 70%)',
                            'recommendation' => 'Agenda com excesso de vagas programadas. Recomenda-se reservar cotas diárias de acolhimento imediato e escuta qualificada sem agendamento prévio.',
                        ];
                    } elseif ($tAvg < 30.0) {
                        $team->agenda_status = 'excess_spontaneous';
                        $agendaAlerts[] = [
                            'team_name' => $team->team_name,
                            'ine' => $team->ine,
                            'average' => $tAvg,
                            'status' => 'excess_spontaneous',
                            'title' => 'Déficit de Acompanhamento Longitudinal (< 30%)',
                            'recommendation' => 'Predomínio excessivo de urgências/espontânea. Estruturar blocos de agenda para pré-natal, hipertensão, diabetes e puericultura.',
                        ];
                    } else {
                        $team->agenda_status = $tAvg > 50.0 ? 'optimal' : 'good';
                    }
                }
            }

            $detail['agenda_alerts'] = $agendaAlerts;
        }

        return $detail;
    }

    /**
     * Gera lista de busca ativa de cidadãos com pendências para atingir a meta no quadrimestre.
     *
     * @return array<int, array<string, mixed>>
     */
    private function generateActiveSearchSample(string $code, int $count, ?string $ine = null): array
    {
        $code = strtolower($code);
        $sampleNames = [
            'Maria Clara Silva dos Santos',
            'João Pedro de Oliveira Costa',
            'Ana Beatriz Albuquerque Souza',
            'Lucas Gabriel Mendes de Lima',
            'Francisca das Chagas Rodrigues',
            'Raimundo Nonato Barbosa Filho',
            'Juliana Ferreira do Nascimento',
            'Antônio Carlos de Morais Neto',
            'Camila Vitória Ribeiro Dias',
            'Sebastião Pereira Guimarães',
            'Larissa Eduarda Gomes Pinho',
            'Matheus Henrique Castro Vieira',
            'Tereza Cristina Lopes Barreto',
            'José Augusto Cavalcanti Melo',
            'Letícia Gabriela Moreira Torres',
        ];

        $pendingActionsByIndicator = [
            'c1' => 'Atendimento programático / consulta agendada pendente',
            'c2' => 'Puericultura: Peso/altura ou vacinação em atraso (Penta/VIP)',
            'c3' => 'Pré-natal: Exame do 3º trimestre (Sífilis/HIV) ou visita puerperal',
            'c4' => 'Diabetes: Hemoglobina Glicada anual ou Avaliação dos Pés pendente',
            'c5' => 'Hipertensão: Aferição de PA no semestre ou visita do ACS pendente',
            'c6' => 'Idoso: Vacina Influenza anual ou antropometria nos últimos 12 meses',
            'c7' => 'Mulher: Citopatológico (> 36 meses) ou mamografia de rastreamento pendente',
        ];

        $c2PendingActions = [
            'Prática A: 1ª consulta até o 30º dia de vida pendente (recém-nascido)',
            'Prática B: 9 consultas de puericultura incompletas (apenas 4 realizadas)',
            'Prática C: Antropometria pendente (registro simultâneo de peso e altura)',
            'Prática D: 2ª visita domiciliar de ACS até os 6 meses pendente',
            'Prática E: Vacina Pentavalente / VIP (3ª dose) em atraso',
            'Prática E: Vacina Tríplice Viral (SCR aos 12 meses) pendente',
            'Prática E: Vacina Pneumocócica 10-valente (2ª dose) pendente',
            'Prática B: Puericultura médica/enfermagem no 2º semestre atrasada',
        ];

        $items = [];
        $limit = min(max($count, 6), 15);

        for ($i = 0; $i < $limit; $i++) {
            $name = $sampleNames[$i % count($sampleNames)];
            $cnsPrefix = '7' . str_pad((string) (10000000000000 + ($i * 73921)), 14, '0', STR_PAD_LEFT);
            $cpf = sprintf('%03d.%03d.%03d-**', 120 + $i, 450 + $i, 780 + $i);
            $age = match ($code) {
                'c2' => sprintf('%d meses', max(1, min(23, 1 + ($i * 2)))),
                'c3' => sprintf('%d anos (Gestante)', 18 + ($i * 2)),
                'c4', 'c5' => sprintf('%d anos', 48 + ($i * 3)),
                'c6' => sprintf('%d anos', 62 + ($i * 3)),
                'c7' => sprintf('%d anos', 26 + ($i * 3)),
                default => sprintf('%d anos', 22 + ($i * 4)),
            };

            $pendingAction = $code === 'c2'
                ? $c2PendingActions[$i % count($c2PendingActions)]
                : ($pendingActionsByIndicator[$code] ?? 'Ação prioritária pendente');

            $items[] = [
                'name' => $name,
                'cns' => substr($cnsPrefix, 0, 7) . '****' . substr($cnsPrefix, -4),
                'cpf' => $cpf,
                'age' => $age,
                'ine' => $ine ?? sprintf('0001%04d', 201 + ($i % 4)),
                'team_name' => sprintf('eSF Unidade %02d', ($i % 4) + 1),
                'microarea' => sprintf('Microárea %02d', ($i % 6) + 1),
                'pending_action' => $pendingAction,
                'priority' => ($i % 3 === 0) ? 'alta' : 'media',
            ];
        }

        return $items;
    }

    /**
     * Garante a existência dos dados de baseline calculados para os indicadores C1 a C7.
     */
    public function ensureBaselineSnapshots(int $year, int $quarter): void
    {
        // Equipes padrão da Atenção Primária
        $teams = [
            ['ine' => '0001839210', 'name' => 'eSF 01 · Centro de Saúde Central', 'type' => '70'],
            ['ine' => '0001839229', 'name' => 'eSF 02 · Vila Esperança', 'type' => '70'],
            ['ine' => '0001839237', 'name' => 'eSF 03 · Bairro Novo Horizonte', 'type' => '70'],
            ['ine' => '0001839245', 'name' => 'eSF 04 · Jardim das Palmeiras', 'type' => '70'],
            ['ine' => '0001839253', 'name' => 'eAP 01 · Atenção Primária Noturna', 'type' => '76'],
        ];

        // 1. Garante que os snapshots mensais dos Indicadores C1 e C2 existam (Acompanhamento Mensal)
        $monthlyCountC1 = FamilyHealthMonthlySnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->where('indicator_code', 'c1')
            ->count();

        if ($monthlyCountC1 === 0) {
            $this->ensureBaselineMonthlyC1($year, $quarter, $teams);
        }

        $monthlyCountC2 = FamilyHealthMonthlySnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->where('indicator_code', 'c2')
            ->count();

        if ($monthlyCountC2 === 0) {
            $this->ensureBaselineMonthlyC2($year, $quarter, $teams);
        }

        // 2. Garante os snapshots quadrimestrais consolidados de C1 a C7
        $existingCount = FamilyHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->count();

        if ($existingCount > 0) {
            return;
        }

        $indicators = self::getIndicatorsMetadata();

        // Valores base de referência coerentes com as metas ministeriais
        $baselineData = [
            'c1' => ['num' => 1420, 'den' => 2450, 'pct' => 57.96, 'practices' => ['A' => 650, 'B' => 480, 'C' => 290]],
            'c2' => ['num' => 184, 'den' => 230, 'pct' => 80.00, 'practices' => ['A' => 85, 'B' => 78, 'C' => 82, 'D' => 75, 'E' => 80]],
            'c3' => ['num' => 95, 'den' => 120, 'pct' => 79.17, 'practices' => ['A' => 88, 'B' => 75, 'C' => 85, 'D' => 80, 'E' => 70, 'F' => 78, 'G' => 82, 'H' => 74, 'I' => 68, 'J' => 65, 'K' => 72]],
            'c4' => ['num' => 310, 'den' => 420, 'pct' => 73.81, 'practices' => ['A' => 80, 'B' => 78, 'C' => 72, 'D' => 68, 'E' => 75, 'F' => 65]],
            'c5' => ['num' => 740, 'den' => 950, 'pct' => 77.89, 'practices' => ['A' => 82, 'B' => 85, 'C' => 74, 'D' => 70]],
            'c6' => ['num' => 560, 'den' => 780, 'pct' => 71.79, 'practices' => ['A' => 76, 'B' => 72, 'C' => 68, 'D' => 71]],
            'c7' => ['num' => 620, 'den' => 840, 'pct' => 73.81, 'practices' => ['A' => 72, 'B' => 78, 'C' => 75, 'D' => 70]],
        ];

        foreach ($indicators as $slug => $meta) {
            $base = $baselineData[$slug];
            $level = self::calculatePerformanceLevel($slug, $base['pct']);

            // Snapshot Municipal Consolidado
            FamilyHealthIndicatorSnapshot::query()->create([
                'year' => $year,
                'quarter' => $quarter,
                'ine' => null,
                'team_name' => 'Consolidado Municipal',
                'team_type' => '70',
                'indicator_code' => $slug,
                'numerator' => $base['num'],
                'denominator' => $base['den'],
                'score_percent' => $base['pct'],
                'performance_level' => $level,
                'good_practices_breakdown' => $base['practices'],
                'active_search_count' => max(0, $base['den'] - $base['num']),
            ]);

            // Snapshots por Equipe
            $variance = [4.5, -3.2, 8.1, -6.4, 2.0];
            foreach ($teams as $idx => $team) {
                $teamVar = $variance[$idx % count($variance)];
                $teamPct = min(100.0, max(15.0, round($base['pct'] + $teamVar, 2)));
                $teamDen = (int) round($base['den'] / count($teams));
                $teamNum = (int) round(($teamPct / 100) * $teamDen);
                $teamLevel = self::calculatePerformanceLevel($slug, $teamPct);

                FamilyHealthIndicatorSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => $team['ine'],
                    'team_name' => $team['name'],
                    'team_type' => $team['type'],
                    'indicator_code' => $slug,
                    'numerator' => $teamNum,
                    'denominator' => $teamDen,
                    'score_percent' => $teamPct,
                    'performance_level' => $teamLevel,
                    'good_practices_breakdown' => $base['practices'],
                    'active_search_count' => max(0, $teamDen - $teamNum),
                ]);
            }
        }
    }

    /**
     * Gera snapshots mensais de baseline para o indicador C1 nos 4 meses do quadrimestre.
     *
     * @param list<array{ine: string, name: string, type: string}> $teams
     */
    public function ensureBaselineMonthlyC1(int $year, int $quarter, array $teams): void
    {
        $monthsConfig = self::getMonthsForQuarter($quarter);

        $monthBaseScores = [
            1 => 54.20,
            2 => 58.60,
            3 => 62.40,
            4 => 59.80,
        ];

        /** @var array<int, array{num: int, den: int, scores: list<float>}> $teamMonthlySum */
        $teamMonthlySum = [];

        foreach ($teams as $tIdx => $team) {
            $ine = $team['ine'];
            $teamVariance = [-3.5, 2.8, 5.2, -6.0, 1.4][$tIdx % 5];

            $mIndex = 1;
            foreach ($monthsConfig as $monthNum => $cfg) {
                $basePct = $monthBaseScores[$mIndex] + $teamVariance;
                $pct = min(92.0, max(18.0, round($basePct, 2)));
                $den = 280 + ($mIndex * 15) + ($tIdx * 20);
                $num = (int) round(($pct / 100) * $den);
                $level = self::calculatePerformanceLevel('c1', $pct);

                FamilyHealthMonthlySnapshot::query()->updateOrCreate(
                    [
                        'year' => $year,
                        'month' => $monthNum,
                        'ine' => $ine,
                        'indicator_code' => 'c1',
                    ],
                    [
                        'quarter' => $quarter,
                        'month_in_quarter' => $mIndex,
                        'team_name' => $team['name'],
                        'team_type' => $team['type'],
                        'numerator' => $num,
                        'denominator' => $den,
                        'score_percent' => $pct,
                        'performance_level' => $level,
                    ]
                );

                if (! isset($teamMonthlySum[$monthNum])) {
                    $teamMonthlySum[$monthNum] = ['num' => 0, 'den' => 0, 'scores' => []];
                }
                $teamMonthlySum[$monthNum]['num'] += $num;
                $teamMonthlySum[$monthNum]['den'] += $den;
                $teamMonthlySum[$monthNum]['scores'][] = $pct;

                $mIndex++;
            }
        }

        // Snapshots municipais consolidados para cada mês
        $mIndex = 1;
        foreach ($monthsConfig as $monthNum => $cfg) {
            $data = $teamMonthlySum[$monthNum];
            $mAvgScore = count($data['scores']) > 0 ? round(array_sum($data['scores']) / count($data['scores']), 2) : 0.0;
            $mLevel = self::calculatePerformanceLevel('c1', $mAvgScore);

            FamilyHealthMonthlySnapshot::query()->updateOrCreate(
                [
                    'year' => $year,
                    'month' => $monthNum,
                    'ine' => null,
                    'indicator_code' => 'c1',
                ],
                [
                    'quarter' => $quarter,
                    'month_in_quarter' => $mIndex,
                    'team_name' => 'Consolidado Municipal',
                    'team_type' => '70',
                    'numerator' => $data['num'],
                    'denominator' => $data['den'],
                    'score_percent' => $mAvgScore,
                    'performance_level' => $mLevel,
                ]
            );

            $mIndex++;
        }
    }

    /**
     * Gera snapshots mensais de baseline para o indicador C2 nos 4 meses do quadrimestre.
     *
     * @param list<array{ine: string, name: string, type: string}> $teams
     */
    public function ensureBaselineMonthlyC2(int $year, int $quarter, array $teams): void
    {
        $monthsConfig = self::getMonthsForQuarter($quarter);

        $monthBaseScores = [
            1 => 78.50,
            2 => 81.20,
            3 => 79.40,
            4 => 83.00,
        ];

        /** @var array<int, array{num: int, den: int, scores: list<float>}> $teamMonthlySum */
        $teamMonthlySum = [];

        foreach ($teams as $tIdx => $team) {
            $ine = $team['ine'];
            $teamVariance = [-4.0, 3.5, 6.0, -5.2, 2.0][$tIdx % 5];

            $mIndex = 1;
            foreach ($monthsConfig as $monthNum => $cfg) {
                $basePct = $monthBaseScores[$mIndex] + $teamVariance;
                $pct = min(98.0, max(20.0, round($basePct, 2)));
                $den = 20 + ($mIndex * 2) + ($tIdx * 3);
                $num = (int) round(($pct / 100) * ($den * 100)); // total de pontos das boas práticas
                $level = self::calculatePerformanceLevel('c2', $pct);

                FamilyHealthMonthlySnapshot::query()->updateOrCreate(
                    [
                        'year' => $year,
                        'month' => $monthNum,
                        'ine' => $ine,
                        'indicator_code' => 'c2',
                    ],
                    [
                        'quarter' => $quarter,
                        'month_in_quarter' => $mIndex,
                        'team_name' => $team['name'],
                        'team_type' => $team['type'],
                        'numerator' => $num,
                        'denominator' => $den,
                        'score_percent' => $pct,
                        'performance_level' => $level,
                    ]
                );

                if (! isset($teamMonthlySum[$monthNum])) {
                    $teamMonthlySum[$monthNum] = ['num' => 0, 'den' => 0, 'scores' => []];
                }
                $teamMonthlySum[$monthNum]['num'] += $num;
                $teamMonthlySum[$monthNum]['den'] += $den;
                $teamMonthlySum[$monthNum]['scores'][] = $pct;

                $mIndex++;
            }
        }

        // Snapshots municipais consolidados para cada mês
        $mIndex = 1;
        foreach ($monthsConfig as $monthNum => $cfg) {
            $data = $teamMonthlySum[$monthNum];
            $mAvgScore = count($data['scores']) > 0 ? round(array_sum($data['scores']) / count($data['scores']), 2) : 0.0;
            $mLevel = self::calculatePerformanceLevel('c2', $mAvgScore);

            FamilyHealthMonthlySnapshot::query()->updateOrCreate(
                [
                    'year' => $year,
                    'month' => $monthNum,
                    'ine' => null,
                    'indicator_code' => 'c2',
                ],
                [
                    'quarter' => $quarter,
                    'month_in_quarter' => $mIndex,
                    'team_name' => 'Consolidado Municipal',
                    'team_type' => '70',
                    'numerator' => $data['num'],
                    'denominator' => $data['den'],
                    'score_percent' => $mAvgScore,
                    'performance_level' => $mLevel,
                ]
            );

            $mIndex++;
        }
    }

    /**
     * Remove snapshots inválidos do indicador C1 (equipes que não são eSF ou eAP).
     */
    public static function purgeInvalidC1Snapshots(): int
    {
        return self::purgeInvalidTeamSnapshots('c1');
    }

    /**
     * Remove snapshots inválidos do indicador C2 (equipes que não são eSF ou eAP).
     */
    public static function purgeInvalidC2Snapshots(): int
    {
        return self::purgeInvalidTeamSnapshots('c2');
    }

    /**
     * Remove snapshots de equipes inválidas para um indicador específico (eSF Tipo 70 e eAP Tipo 76).
     */
    public static function purgeInvalidTeamSnapshots(string $code): int
    {
        $deleted = 0;

        $query = FamilyHealthIndicatorSnapshot::query()
            ->where('indicator_code', $code)
            ->whereNotNull('ine')
            ->where(function ($q) {
                $q->whereNotIn('team_type', ['70', '76'])
                  ->orWhere('team_name', 'like', 'ESB%')
                  ->orWhere('team_name', 'like', 'esb%')
                  ->orWhere('team_name', 'like', '%E-MULTI%')
                  ->orWhere('team_name', 'like', '%e-multi%')
                  ->orWhere('team_name', 'like', '%EQUIPE AMPLIADA%')
                  ->orWhere('team_name', 'like', '%equipe ampliada%')
                  ->orWhere('team_name', 'like', '%EMAD%')
                  ->orWhere('team_name', 'like', '%EMAP%')
                  ->orWhere('team_name', 'like', '%SEM EQUIPE%')
                  ->orWhere('team_name', 'like', '%sem equipe%')
                  ->orWhere('team_name', 'like', '%INE N%O ENCONTRADO%')
                  ->orWhere('ine', 'SEM_INE')
                  ->orWhere('ine', '0')
                  ->orWhereRaw('LENGTH(ine) != 10');
            });

        $deleted += $query->delete();

        $monthlyQuery = FamilyHealthMonthlySnapshot::query()
            ->where('indicator_code', $code)
            ->whereNotNull('ine')
            ->where(function ($q) {
                $q->where('ine', 'like', 'ESB%')
                  ->orWhere('ine', 'SEM_INE')
                  ->orWhere('ine', '0')
                  ->orWhereRaw('LENGTH(ine) != 10');
            });

        $deleted += $monthlyQuery->delete();

        return $deleted;
    }
}
