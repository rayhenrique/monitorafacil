<?php

use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\CvatTeamEvaluation;
use App\Models\FamilyHealthIndicatorSnapshot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        // 1. Consolidação de Cadastros (Q3/2026) - Dados Reais Oficiais
        // Total MICI: 47.480 atualizados + 19.923 desatualizados = 67.403 (70,44%)
        // Total MICDT: 18.363 atualizados + 7.369 desatualizados = 25.732 (71,36%)
        ConsolidationRegistration::query()->updateOrCreate(
            ['year' => 2026, 'quarter' => 3],
            [
                'mici_updated_count' => 47480,
                'mici_outdated_count' => 19923,
                'micdt_updated_count' => 18363,
                'micdt_outdated_count' => 7369,
            ]
        );

        // 2. Consolidação de Equipes Homologadas (Q3/2026) - 19 eSF, 8 eSB, 2 eMulti
        ConsolidationTeam::query()->updateOrCreate(
            ['year' => 2026, 'quarter' => 3, 'type' => 'esf'],
            ['total_active' => 19]
        );
        ConsolidationTeam::query()->updateOrCreate(
            ['year' => 2026, 'quarter' => 3, 'type' => 'esaude_bucal'],
            ['total_active' => 8]
        );
        ConsolidationTeam::query()->updateOrCreate(
            ['year' => 2026, 'quarter' => 3, 'type' => 'emulti'],
            ['total_active' => 2]
        );

        // 3. Avaliações de Desempenho CVAT das 19 Equipes de eSF (Q3/2026)
        // Distribuição Oficial: 7 ÓTIMO, 8 BOM, 3 SUFICIENTE, 1 REGULAR
        $teamsData = [
            // 7 ÓTIMO
            ['cnes' => '2719738', 'facility' => '01 CENTRO DE SAUDE MANUEL A DE SANTANA', 'ine' => '0000171107', 'team' => '01 06 CS MANUEL A DE SANTANA', 'reg_score' => 3.0, 'mon_score' => 7.0, 'final_score' => 10.0, 'classification' => 'ÓTIMO'],
            ['cnes' => '0111791', 'facility' => 'USF 19 SANDRA MARIA DA SILVA', 'ine' => '0001715364', 'team' => 'BENEDITO DE LIRA', 'reg_score' => 3.0, 'mon_score' => 7.0, 'final_score' => 10.0, 'classification' => 'ÓTIMO'],
            ['cnes' => '4649524', 'facility' => 'UBS 06 UNIVERSITARIO SERGIO CELESTINO DA PAIXAO JUNIOR', 'ine' => '0000171093', 'team' => 'ESF 006', 'reg_score' => 3.0, 'mon_score' => 7.0, 'final_score' => 10.0, 'classification' => 'ÓTIMO'],
            ['cnes' => '9307613', 'facility' => 'USF 03 MARIA LOPES DE LIMA MARIA CASSIMIRO', 'ine' => '0000171115', 'team' => 'ESF 03', 'reg_score' => 3.0, 'mon_score' => 7.0, 'final_score' => 10.0, 'classification' => 'ÓTIMO'],
            ['cnes' => '7705298', 'facility' => 'UNIDADE BASICA DE SAUDE 17', 'ine' => '0001573330', 'team' => 'ESF 17', 'reg_score' => 3.0, 'mon_score' => 7.0, 'final_score' => 10.0, 'classification' => 'ÓTIMO'],
            ['cnes' => '2008556', 'facility' => 'USF 16 JOAO LOURIVAL DE SOUZA', 'ine' => '0000171085', 'team' => 'PACS', 'reg_score' => 3.0, 'mon_score' => 6.56, 'final_score' => 9.56, 'classification' => 'ÓTIMO'],
            ['cnes' => '2722623', 'facility' => 'USF 05 SINEIDE FREIRE MONTEIRO', 'ine' => '0000171204', 'team' => 'USF 05 SINEIDE FREIRE MONTEIRO', 'reg_score' => 3.0, 'mon_score' => 7.0, 'final_score' => 10.0, 'classification' => 'ÓTIMO'],

            // 8 BOM
            ['cnes' => '2719886', 'facility' => '02 CENTRO DE SAUDE TEOTONIO VILELA', 'ine' => '0000171123', 'team' => '02 03 C S TEOTONIO VILELA', 'reg_score' => 2.63, 'mon_score' => 5.25, 'final_score' => 7.88, 'classification' => 'BOM'],
            ['cnes' => '2722585', 'facility' => 'USF 04 FRANCISCA ASSIS BORGES PEREIRA', 'ine' => '0000171166', 'team' => 'USF 04 FRANCISCA A BORGES PERE', 'reg_score' => 3.0, 'mon_score' => 5.25, 'final_score' => 8.25, 'classification' => 'BOM'],
            ['cnes' => '2722682', 'facility' => 'USF 08 GULANDIM', 'ine' => '0000171220', 'team' => 'USF 08 GULANDIM', 'reg_score' => 3.0, 'mon_score' => 5.25, 'final_score' => 8.25, 'classification' => 'BOM'],
            ['cnes' => '2722569', 'facility' => 'USF 09 AGUA DE MENINOS', 'ine' => '0000171131', 'team' => 'USF 09 AGUA DE MENINOS', 'reg_score' => 3.0, 'mon_score' => 5.25, 'final_score' => 8.25, 'classification' => 'BOM'],
            ['cnes' => '2722593', 'facility' => 'USF 10 IMBURI DO INACIO', 'ine' => '0000171174', 'team' => 'USF 10 IMBURI DO MATAO', 'reg_score' => 2.25, 'mon_score' => 5.25, 'final_score' => 7.5, 'classification' => 'BOM'],
            ['cnes' => '2722631', 'facility' => 'USF 11 TEN JOSE ALBINO', 'ine' => '0000171212', 'team' => 'USF 11 TEN JOSE ALBINO', 'reg_score' => 2.63, 'mon_score' => 5.25, 'final_score' => 7.88, 'classification' => 'BOM'],
            ['cnes' => '2722615', 'facility' => 'USF 12 MANOEL JACINTO G DA SILVA', 'ine' => '0000171190', 'team' => 'USF 12 MANOEL J G DA SILVA', 'reg_score' => 2.63, 'mon_score' => 5.25, 'final_score' => 7.88, 'classification' => 'BOM'],
            ['cnes' => '2722607', 'facility' => 'USF 13 JOSE BELARMINO SOARES', 'ine' => '0000171182', 'team' => 'USF 13 JOSE BELARMINO SOARES', 'reg_score' => 3.0, 'mon_score' => 5.25, 'final_score' => 8.25, 'classification' => 'BOM'],

            // 3 SUFICIENTE
            ['cnes' => '2722577', 'facility' => 'USF 07 JUMELICIA M CONCEICAO', 'ine' => '0000171158', 'team' => 'USF 07 JUMELICIA M CONCEICAO', 'reg_score' => 2.25, 'mon_score' => 3.5, 'final_score' => 5.75, 'classification' => 'SUFICIENTE'],
            ['cnes' => '4020596', 'facility' => 'USF 14 CELESTRINA MARIA DIAS', 'ine' => '0000171239', 'team' => 'USF 14 JOAO LOURIVAL DE SOU', 'reg_score' => 2.25, 'mon_score' => 3.5, 'final_score' => 5.75, 'classification' => 'SUFICIENTE'],
            ['cnes' => '6010989', 'facility' => 'PSF 15 NEUZA JOSEFA DO NASCIMENTO FIRMINO', 'ine' => '0000171255', 'team' => 'USF 15 NEUZA JOSEFA DO NASCIME', 'reg_score' => 2.25, 'mon_score' => 3.5, 'final_score' => 5.75, 'classification' => 'SUFICIENTE'],

            // 1 REGULAR
            ['cnes' => '7770499', 'facility' => 'UNIDADE BASICA DE SAUDE MATAO DO ROBERTO', 'ine' => '0001581554', 'team' => 'ESF MATAO DO ROBERTO', 'reg_score' => 0.75, 'mon_score' => 1.75, 'final_score' => 2.5, 'classification' => 'REGULAR'],
        ];

        foreach ($teamsData as $data) {
            CvatTeamEvaluation::query()->updateOrCreate(
                [
                    'year' => 2026,
                    'quarter' => 3,
                    'ine' => $data['ine'],
                ],
                [
                    'quarter_label' => 'Q3/26',
                    'cnes' => $data['cnes'],
                    'facility_name' => $data['facility'],
                    'team_type' => 'eSF',
                    'team_name' => $data['team'],
                    'parameter' => 2500,
                    'linked_registrations' => (int) round(($data['final_score'] / 10.0) * 2500),
                    'linked_ratio' => round(($data['final_score'] / 10.0) * 100, 2),
                    'registration_result' => round(($data['reg_score'] / 3.0) * 100, 2),
                    'registration_score' => $data['reg_score'],
                    'monitoring_result' => round(($data['mon_score'] / 7.0) * 100, 2),
                    'monitoring_score' => $data['mon_score'],
                    'final_score' => $data['final_score'],
                    'final_classification' => $data['classification'],
                ]
            );
        }

        // 4. Limpa snapshots fictícios de demonstração de C4..C7 se gerados anteriormente por baseline
        if (Schema::hasTable('family_health_indicator_snapshots')) {
            FamilyHealthIndicatorSnapshot::query()
                ->whereIn('indicator_code', ['c4', 'c5', 'c6', 'c7'])
                ->where('team_name', 'like', '%Centro de Saúde Central%')
                ->orWhere(function ($query): void {
                    $query->whereIn('indicator_code', ['c4', 'c5', 'c6', 'c7'])
                        ->where('team_name', 'Consolidado Municipal');
                })
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        CvatTeamEvaluation::query()->where('year', 2026)->where('quarter', 3)->delete();
    }
};
