<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relação Nominal · Vínculo e Acompanhamento</title>
    <style>
        @page {
            margin: 12mm 10mm 15mm 10mm;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8px;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #0d9488;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header .title {
            font-size: 13px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
        }
        .header .subtitle {
            font-size: 9px;
            color: #475569;
            margin-top: 2px;
        }
        .header .meta {
            text-align: right;
            font-size: 8px;
            color: #64748b;
        }
        .header .meta strong {
            color: #1e293b;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-bottom: 10px;
            padding: 6px 8px;
        }
        .summary-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f766e;
            margin-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 2px;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-grid td {
            padding: 2px 4px;
            font-size: 8px;
        }
        .summary-grid strong {
            font-size: 9px;
        }
        .table-citizens {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 7.5px;
        }
        .table-citizens th {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 3px;
            text-align: left;
            border-top: 1px solid #0d9488;
            border-bottom: 1px solid #0d9488;
            border-right: 1px solid #0d9488;
            font-size: 7px;
        }
        .table-citizens th:first-child {
            border-left: 1px solid #0d9488;
        }
        .table-citizens td {
            padding: 3.5px 3px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .table-citizens td:first-child {
            border-left: 1px solid #e2e8f0;
        }
        .table-citizens tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-green { background-color: #dcfce7; color: #15803d; }
        .badge-red { background-color: #fee2e2; color: #b91c1c; }
        .badge-blue { background-color: #e0f2fe; color: #0369a1; }
        .badge-amber { background-color: #fef3c7; color: #b45309; }
        .badge-slate { background-color: #f1f5f9; color: #475569; }
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            height: 8mm;
            border-top: 1px solid #cbd5e1;
            padding-top: 3px;
            font-size: 7px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }
        .footer-left { float: left; }
        .footer-right { float: right; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 65%;">
                    <div class="title">Monitora Fácil · Vínculo e Acompanhamento Territorial</div>
                    <div class="subtitle">
                        Relação Nominal de Cidadãos · NT nº 30/2025-CGESCO/DESCO/SAPS/MS ·
                        <strong>{{ trim($settings['municipio_nome'] ?? '') ?: 'Município' }}</strong>
                    </div>
                </td>
                <td class="meta" style="width: 35%;">
                    <div><strong>Competência:</strong> {{ $metrics->year ?? 2026 }}/M{{ str_pad((string)($metrics->month ?? 9), 2, '0', STR_PAD_LEFT) }}</div>
                    <div><strong>Equipe:</strong> {{ $teamName ?: 'Todas as Equipes (Consolidado Municipal)' }}</div>
                    <div><strong>Emissão:</strong> {{ now()->format('d/m/Y H:i') }} | <strong>Total:</strong> {{ number_format($totalCount, 0, ',', '.') }} registros</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Resumo dos Indicadores -->
    @if ($metrics)
        <div class="summary-box">
            <div class="summary-title">Resumo dos Indicadores · {{ $teamName ?: 'Consolidado Geral' }}</div>
            <table class="summary-grid">
                <tr>
                    <td><strong>Total MICI:</strong> {{ number_format($metrics->mici_total ?? 0, 0, ',', '.') }}</td>
                    <td><strong>MICI Atualizado:</strong> <span style="color: #15803d; font-weight: bold;">{{ number_format($metrics->mici_updated ?? 0, 0, ',', '.') }}</span></td>
                    <td><strong>Com MICDT:</strong> {{ number_format($metrics->mici_with_micdt_total ?? 0, 0, ',', '.') }}</td>
                    <td><strong>Ambos Atualizados:</strong> {{ number_format($metrics->mici_and_micdt_updated ?? 0, 0, ',', '.') }}</td>
                    <td><strong>Vinculados:</strong> <span style="color: #15803d; font-weight: bold;">{{ number_format($metrics->citizens_linked ?? 0, 0, ',', '.') }}</span></td>
                    <td><strong>Não Vinculados:</strong> <span style="color: #b91c1c; font-weight: bold;">{{ number_format($metrics->citizens_not_linked ?? 0, 0, ',', '.') }}</span></td>
                </tr>
                <tr>
                    <td><strong>Sem Critério:</strong> {{ number_format($metrics->no_criteria_total ?? 0, 0, ',', '.') }} (Acomp: {{ number_format($metrics->no_criteria_accompanied ?? 0, 0, ',', '.') }})</td>
                    <td><strong>Idoso / Criança:</strong> {{ number_format($metrics->elderly_or_child_total ?? 0, 0, ',', '.') }} (Acomp: {{ number_format($metrics->elderly_or_child_accompanied ?? 0, 0, ',', '.') }})</td>
                    <td><strong>BPC ou PBF:</strong> {{ number_format($metrics->bpc_or_pbf_total ?? 0, 0, ',', '.') }} (Acomp: {{ number_format($metrics->bpc_or_pbf_accompanied ?? 0, 0, ',', '.') }})</td>
                    <td colspan="3"><strong>Idoso/Criança + BPC/PBF:</strong> {{ number_format($metrics->elderly_child_and_benefit_total ?? 0, 0, ',', '.') }} (Acomp: {{ number_format($metrics->elderly_child_and_benefit_accompanied ?? 0, 0, ',', '.') }})</td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Tabela Nominal -->
    <table class="table-citizens">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">#</th>
                <th style="width: 90px;">CNS</th>
                <th style="width: 75px;">CPF</th>
                <th>Nome do Cidadão</th>
                <th style="width: 55px;" class="text-center">Nascimento</th>
                <th style="width: 25px;" class="text-center">Idade</th>
                <th style="width: 140px;">Equipe / Unidade</th>
                <th style="width: 30px;" class="text-center">MA</th>
                <th style="width: 50px;" class="text-center">MICI</th>
                <th style="width: 50px;" class="text-center">MICDT</th>
                <th style="width: 50px;" class="text-center">Vínculo</th>
                <th style="width: 60px;" class="text-center">Vulnerab.</th>
                <th style="width: 45px;" class="text-center">PBF/BPC</th>
                <th style="width: 55px;" class="text-center">Acompanhado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($citizens as $idx => $c)
                <tr>
                    <td class="text-center" style="color: #64748b;">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace;">{{ $c->cns ?: '---' }}</td>
                    <td style="font-family: monospace;">{{ $c->cpf ?: '---' }}</td>
                    <td><strong>{{ $c->name }}</strong></td>
                    <td class="text-center">{{ $c->birth_date ? \Carbon\Carbon::parse($c->birth_date)->format('d/m/Y') : '---' }}</td>
                    <td class="text-center">{{ $c->age ?? '---' }}</td>
                    <td>
                        <div><strong>{{ $c->team_name ?: 'EQUIPE NÃO INFORMADA' }}</strong></div>
                        <div style="font-size: 6.5px; color: #64748b;">INE: {{ $c->ine ?: '---' }} | CNES: {{ $c->cnes ?: '---' }}</div>
                    </td>
                    <td class="text-center" style="font-weight: bold;">{{ $c->microarea ?: '00' }}</td>
                    <td class="text-center">
                        @if ($c->mici_updated)
                            <span class="badge badge-green">Atual</span>
                        @else
                            <span class="badge badge-red">Desat</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if (! $c->has_micdt)
                            <span class="badge badge-slate">Sem</span>
                        @elseif ($c->micdt_updated)
                            <span class="badge badge-green">Atual</span>
                        @else
                            <span class="badge badge-red">Desat</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($c->is_linked)
                            <span class="badge badge-green">Sim</span>
                        @else
                            <span class="badge badge-red">Não</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($c->vulnerability_type === 'idoso')
                            <span class="badge badge-amber">Idoso</span>
                        @elseif ($c->vulnerability_type === 'crianca')
                            <span class="badge badge-blue">Criança</span>
                        @else
                            <span class="badge badge-slate">Sem crit</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($c->social_benefit === 'pbf')
                            <span class="badge badge-blue">PBF</span>
                        @elseif ($c->social_benefit === 'bpc')
                            <span class="badge badge-blue">BPC</span>
                        @elseif ($c->social_benefit === 'bpc_pbf')
                            <span class="badge badge-blue">BPC+PBF</span>
                        @else
                            <span style="color: #94a3b8;">---</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($c->is_accompanied)
                            <span class="badge badge-green">Sim</span>
                        @else
                            <span class="badge badge-red">Não</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="text-center" style="padding: 15px; color: #64748b;">
                        Nenhum cidadão encontrado para os filtros selecionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Rodapé Fixo -->
    <div class="footer">
        <div class="footer-left">
            Monitora Fácil · Sistema de Gestão e Monitoramento da Atenção Primária à Saúde | Relatório gerado em {{ now()->format('d/m/Y \à\s H:i:s') }}
        </div>
        <div class="footer-right">
            Total listado: {{ count($citizens) }} de {{ number_format($totalCount ?? count($citizens), 0, ',', '.') }} registros@if(isset($totalCount) && $totalCount > count($citizens)) (amostra de 200 no PDF · exporte em CSV para base completa)@endif
        </div>
    </div>
</body>
</html>
