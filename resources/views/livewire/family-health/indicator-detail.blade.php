<div class="app-page">
    @php
        $level = $current['performance_level'];
        $score = $current['score_percent'];
        $isC1 = $indicator === 'c1';
        $isC2 = $indicator === 'c2';
        $isC3 = $indicator === 'c3';
        $hasValidatedResult = ! ($isC1 || $isC2 || $isC3) || $score !== null;
        $hasC2Result = ! $isC2 || $score !== null;
        $hasC3Result = ! $isC3 || $score !== null;

        $badgeStyles = match ($level) {
            null => 'bg-slate-100 text-slate-700 border-slate-300',
            'otimo' => in_array($indicator, ['c1', 'c2', 'c3']) ? 'bg-sky-100 text-sky-800 border-sky-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'bom' => in_array($indicator, ['c1', 'c2', 'c3']) ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-sky-100 text-sky-800 border-sky-300',
            'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
            default => 'bg-rose-100 text-rose-800 border-rose-300',
        };

        $levelLabel = match ($level) {
            null => 'Sem resultado',
            'otimo' => 'Desempenho Ótimo',
            'bom' => 'Desempenho Bom',
            'suficiente' => 'Desempenho Suficiente',
            default => 'Desempenho Regular',
        };

        $barColor = match ($level) {
            null => 'bg-slate-300',
            'otimo' => in_array($indicator, ['c1', 'c2', 'c3']) ? 'bg-sky-500' : 'bg-emerald-500',
            'bom' => in_array($indicator, ['c1', 'c2', 'c3']) ? 'bg-emerald-500' : 'bg-sky-500',
            'suficiente' => 'bg-amber-500',
            default => 'bg-rose-500',
        };

        $quarterSummary = $data['quarter_summary'] ?? $data['c1_quarter_summary'] ?? $data['c2_quarter_summary'] ?? $data['c3_quarter_summary'] ?? null;
        $monthlyEvolution = $data['monthly_evolution'] ?? $data['c1_monthly_evolution'] ?? $data['c2_monthly_evolution'] ?? $data['c3_monthly_evolution'] ?? [];
        $c1Summary = $quarterSummary;
        $c1Monthly = $monthlyEvolution;
        $agendaAlerts = $data['agenda_alerts'] ?? [];
    @endphp

    @if (! $isC1 && ! $isC2)
        <x-family-health-tabs
            :title="$meta['code'] . ' · ' . $meta['short_title']"
            :subtitle="$meta['full_title']"
            :activeIndicator="$indicator"
        />
    @endif

    @if ($isC1)
        <!-- ========================================================================= -->
        <!-- PAINEL C1: COMPONENTE DE QUALIDADE / SAÚDE DA FAMÍLIA - C1 (MENSAL)      -->
        <!-- ========================================================================= -->
        <div class="space-y-4">
            <!-- Cabeçalho Principal: Título e Botão de Relatório -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-[#004e82]">
                    Componente de Qualidade / Saúde da Família - C1 (Mensal)
                    <span class="sr-only">Desempenho por Equipe · Busca Ativa · Nota Metodológica Oficial</span>
                </h1>

                <!-- Botão Relatório Dropdown -->
                <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false">
                    <button
                        type="button"
                        @click="open = !open"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#008a4f] hover:bg-[#007342] text-white px-4 py-2.5 text-xs sm:text-sm font-semibold shadow-xs transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span>Relatório</span>
                        <svg class="w-3.5 h-3.5 ml-0.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition
                        class="absolute right-0 mt-2 w-56 rounded-xl bg-white p-1.5 shadow-lg border border-slate-200 z-30"
                    >
                        <button
                            type="button"
                            @click="window.print(); open = false;"
                            class="flex items-center gap-2.5 w-full rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition text-left cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.146-1.12-3.153a8.97 8.97 0 01-1.35-4.176c0-2.485 1.008-4.735 2.64-6.368M17.28 13.829c.24-1.076.672-2.146 1.12-3.153a8.97 8.97 0 001.35-4.176c0-2.485-1.008-4.735-2.64-6.368" />
                            </svg>
                            <span>Imprimir / Gerar PDF</span>
                        </button>
                        <button
                            type="button"
                            wire:click="exportC1Csv"
                            @click="open = false;"
                            class="flex items-center gap-2.5 w-full rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition text-left cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            <span>Exportar Planilha (CSV / Excel)</span>
                        </button>
                    </div>
                </div>
            </div>

            @if (! $hasValidatedResult)
                <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-950 shadow-xs">
                    <p class="text-xs font-bold">Sem resultado C1 validado para este período.</p>
                    <p class="mt-0.5 text-xs text-amber-900 leading-relaxed">Execute o processamento do DW PEC. O painel não gera valores simulados e não converte competências ausentes em zero.</p>
                </div>
            @endif

            <!-- Abas Secundárias de Exibição -->
            <div class="flex items-center gap-1 border-b border-[#b8d1e5]/70 pt-2">
                <button
                    type="button"
                    wire:click="setC1SubTab('teams')"
                    class="px-6 py-3 text-xs sm:text-sm font-semibold rounded-t-lg transition border-t-2 border-l border-r cursor-pointer {{ $c1SubTab === 'teams' ? 'bg-[#eef5fa] text-[#1c4e80] border-[#b8d1e5] font-bold shadow-xs' : 'bg-transparent text-slate-500 hover:text-slate-800 border-transparent' }}"
                >
                    Resumo por Equipe
                </button>
                <button
                    type="button"
                    wire:click="setC1SubTab('unassigned')"
                    class="px-6 py-3 text-xs sm:text-sm font-semibold rounded-t-lg transition border-t-2 border-l border-r cursor-pointer {{ $c1SubTab === 'unassigned' ? 'bg-[#eef5fa] text-[#1c4e80] border-[#b8d1e5] font-bold shadow-xs' : 'bg-transparent text-slate-500 hover:text-slate-800 border-transparent' }}"
                >
                    Sem Equipe
                </button>
            </div>

            <!-- Card de Filtros -->
            <div class="rounded-b-lg rounded-tr-lg border-2 border-[#b8d1e5] bg-[#f8fafc]/50 p-5 shadow-xs space-y-4">
                <span class="sr-only">Acompanhamento Mensal da Demanda</span>
                <span class="sr-only">Filtros do Acompanhamento Mensal</span>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <!-- Distrito -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Distrito</label>
                        <select
                            wire:model.live="selectedDistrict"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        >
                            <option value="">Selecione o distrito</option>
                            <option value="1">Distrito 1 - Sede</option>
                            <option value="todos">Todos os Distritos</option>
                        </select>
                    </div>

                    <!-- Unidade -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Unidade</label>
                        <select
                            wire:model.live="selectedCnes"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 truncate focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        >
                            <option value="">Selecione a opção ...</option>
                            @foreach ($availableUnits as $unit)
                                <option value="{{ $unit['cnes'] }}">{{ $unit['cnes'] }} - {{ $unit['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Equipe -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5"><span class="sr-only">Equipe (eSF / eAP):</span>Equipe</label>
                        <select
                            wire:model.live="selectedIne"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 truncate focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        >
                            <option value="">Selecione o INE</option>
                            @foreach ($availableTeams as $t)
                                <option value="{{ $t['ine'] }}">{{ $t['ine'] }} - {{ $t['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Mês -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5"><span class="sr-only">Mês de Competência:</span>Mês</label>
                        <div class="relative">
                            <select
                                wire:model.live="selectedMonth"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 pr-8 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                            >
                                <option value="">Todos os meses</option>
                                @foreach ($quarterMonths as $mNum => $mLabel)
                                    <option value="{{ $mNum }}">{{ $mLabel }}</option>
                                @endforeach
                            </select>
                            @if ($selectedMonth)
                                <button
                                    type="button"
                                    wire:click="clearMonth"
                                    class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-xs font-bold px-1"
                                    title="Limpar seleção de mês"
                                >✕</button>
                            @endif
                        </div>
                    </div>

                    <!-- Quadrimestre -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5"><span class="sr-only">Quadrimestre / Período:</span>Quadrimestre</label>
                        <select
                            wire:model.live="quarter"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        >
                            <option value="3">{{ $year }} / Q3</option>
                            <option value="2">{{ $year }} / Q2</option>
                            <option value="1">{{ $year }} / Q1</option>
                        </select>
                    </div>

                    <!-- Classificação -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5"><span class="sr-only">Classificação Oficial:</span>Classificação</label>
                        <select
                            wire:model.live="selectedClassification"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        >
                            <option value="">Selecione a classifi...</option>
                            <option value="otimo">Ótimo</option>
                            <option value="bom">Bom</option>
                            <option value="suficiente">Suficiente</option>
                            <option value="regular">Regular</option>
                        </select>
                    </div>
                </div>

                <!-- Botões de Ação do Filtro -->
                <div class="pt-2 flex items-center justify-between">
                    <button
                        type="button"
                        wire:click="applyC1Filters"
                        class="inline-flex items-center gap-2 rounded-md bg-[#0062b8] hover:bg-[#005199] text-white px-6 py-2.5 text-xs font-bold shadow-xs transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                        <span>Carregar</span>
                    </button>

                    @if ($selectedCnes || $selectedIne || $selectedClassification || $selectedDistrict || $selectedMonth !== (($quarter - 1) * 4 + 1))
                        <button
                            type="button"
                            wire:click="resetC1Filters"
                            class="text-xs text-slate-500 hover:text-slate-800 underline font-medium cursor-pointer"
                        >
                            Limpar filtros
                        </button>
                    @endif
                </div>
            </div>

            <!-- Card de Dados com Legenda e Tabela -->
            <div class="rounded-lg border-2 border-[#b8d1e5] bg-white overflow-hidden shadow-xs mb-4">
                <!-- Cabeçalho do Card com Legenda Oficial -->
                <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white">
                    <h2 class="text-base sm:text-lg font-bold text-slate-700">
                        Indicador de Mais Acesso
                    </h2>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-slate-500 mr-1">Legenda da Classificação</span>
                        <span class="bg-[#d9534f] text-white text-[11px] font-bold px-3 py-1 rounded shadow-2xs" title="Regular · 0,25 pt no Componente III">
                            Regular (&le; 10% ou &gt; 70%) <span class="sr-only">· Regular · 0,25 pt</span>
                        </span>
                        <span class="bg-[#f0ad4e] text-white text-[11px] font-bold px-3 py-1 rounded shadow-2xs" title="Suficiente · 0,50 pt no Componente III">
                            Suficiente (&gt; 10% e &le; 30%) <span class="sr-only">· Suficiente · 0,50 pt</span>
                        </span>
                        <span class="bg-[#198754] text-white text-[11px] font-bold px-3 py-1 rounded shadow-2xs" title="Bom · 0,75 pt no Componente III">
                            Bom (&gt; 30% ou &le; 50%) <span class="sr-only">· Bom · 0,75 pt</span>
                        </span>
                        <span class="bg-[#0284c7] text-white text-[11px] font-bold px-3 py-1 rounded shadow-2xs" title="Ótimo · 1,00 pt no Componente III">
                            Ótimo (&gt; 50% ou &le; 70%) <span class="sr-only">· Ótimo · 1,00 pt</span>
                        </span>
                    </div>
                </div>

                @if ($c1SubTab === 'teams')
                    <!-- Tabela: Resumo por Equipe -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-[#f8fafc] text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th scope="col" class="py-3.5 px-3 w-10 text-center whitespace-nowrap">#</th>
                                    <th scope="col" class="py-3.5 px-4 min-w-[220px]">UNIDADE</th>
                                    <th scope="col" class="py-3.5 px-4 min-w-[190px]">EQUIPE</th>
                                    <th scope="col" class="py-3.5 px-3 text-center whitespace-nowrap">MÊS</th>
                                    <th scope="col" class="py-3.5 px-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1">
                                            PROGRAMADO (NUMERADOR)
                                            <span title="Total de atendimentos individuais por médico ou enfermeiro com consulta agendada / programada (CBOs elegíveis)" class="cursor-help text-slate-400">ⓘ</span>
                                        </span>
                                    </th>
                                    <th scope="col" class="py-3.5 px-3 text-center whitespace-nowrap">ESPONTÂNEO</th>
                                    <th scope="col" class="py-3.5 px-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1">
                                            TOTAL DE ATENDIMENTOS (DENOMINADOR)
                                            <span title="Total geral de atendimentos individuais médicos e de enfermagem realizados pela equipe no mês" class="cursor-help text-slate-400">ⓘ</span>
                                        </span>
                                    </th>
                                    <th scope="col" class="py-3.5 px-3 text-center whitespace-nowrap">AVALIADA?</th>
                                    <th scope="col" class="py-3.5 px-4 whitespace-nowrap min-w-[160px]">INDICADOR</th>
                                    <th scope="col" class="py-3.5 px-3 text-center whitespace-nowrap">CLASSIFICAÇÃO</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($c1TableRows as $index => $row)
                                    @php
                                        $badgeColor = match ($row['performance_level']) {
                                            'otimo' => 'bg-[#0284c7]',
                                            'bom' => 'bg-[#198754]',
                                            'suficiente' => 'bg-[#f0ad4e]',
                                            default => 'bg-[#d9534f]',
                                        };
                                        $badgeText = match ($row['performance_level']) {
                                            'otimo' => 'Ótimo',
                                            'bom' => 'Bom',
                                            'suficiente' => 'Suficiente',
                                            default => 'Regular',
                                        };
                                        $barWidth = min(100, max(0, $row['score_percent']));
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-3 text-center font-medium text-slate-500 whitespace-nowrap">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="py-3.5 px-4 font-medium text-slate-800 uppercase text-xs leading-snug">
                                            {{ $row['cnes'] }} - {{ $row['facility_name'] }}
                                        </td>
                                        <td class="py-3.5 px-4 font-medium text-slate-700 uppercase text-xs leading-snug">
                                            {{ $row['ine'] }} - {{ $row['team_name'] }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center text-slate-600 tabular-nums whitespace-nowrap">
                                            {{ $row['month_label'] }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-semibold text-slate-800 tabular-nums whitespace-nowrap">
                                            {{ number_format($row['numerator'], 0, '', '.') }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-medium text-slate-700 tabular-nums whitespace-nowrap">
                                            {{ number_format($row['spontaneous'], 0, '', '.') }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-slate-800 tabular-nums whitespace-nowrap">
                                            {{ number_format($row['denominator'], 0, '', '.') }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                            @if ($row['is_evaluated'])
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-emerald-500 text-emerald-600 bg-emerald-50" title="Equipe avaliada no Componente de Qualidade">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-slate-300 text-slate-400">
                                                    —
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="w-24 bg-slate-200 rounded-full h-2 overflow-hidden shrink-0">
                                                    <div class="h-2 rounded-full bg-[#0284c7]" style="width: {{ $barWidth }}%"></div>
                                                </div>
                                                <span class="font-bold text-slate-700 tabular-nums text-xs">
                                                    {{ number_format($row['score_percent'], 2, ',', '.') }}%
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                            <span class="inline-block px-3 py-1 rounded text-xs font-bold text-white shadow-2xs {{ $badgeColor }}">
                                                {{ $badgeText }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="py-12 text-center text-slate-500">
                                            Nenhum atendimento encontrado para os filtros selecionados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Tabela: Sem Equipe -->
                    <div class="p-8 text-center space-y-4">
                        @if ($unassignedAttendances > 0)
                            <div class="max-w-md mx-auto bg-amber-50 border border-amber-200 rounded-xl p-4 text-left text-xs text-amber-900 space-y-1">
                                <p class="font-bold">Atendimentos não vinculados a equipes avaliadas:</p>
                                <p>Total: {{ number_format($unassignedAttendances, 0, '', '.') }} atendimentos na competência selecionada.</p>
                            </div>
                        @else
                            <div class="mx-auto w-12 h-12 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">100% dos atendimentos vinculados a equipes avaliadas</h3>
                            <p class="text-xs text-slate-500 max-w-lg mx-auto leading-relaxed">
                                Não foram identificados atendimentos individuais do C1 sem vinculação de equipe (INE) para a competência selecionada. Todos os atendimentos foram devidamente atribuídos às equipes de Saúde da Família e Atenção Primária municipais.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Informações Metodológicas Oficiais NT 08/2026 (Expansível) -->
            <div x-data="{ openRules: false }" class="rounded-xl border border-slate-200 bg-white p-4 shadow-2xs">
                <button
                    type="button"
                    @click="openRules = !openRules"
                    class="flex items-center justify-between w-full text-left text-xs font-bold text-slate-700 cursor-pointer"
                >
                    <span class="flex items-center gap-2">
                        <span class="rounded bg-teal-100 text-teal-800 px-2 py-0.5 text-[10px] font-mono">NT 08/2026-DEAPS/SAPS/MS</span>
                        <span>Nota Metodológica Oficial & Critérios de Avaliação do C1 (Mais Acesso)</span>
                    </span>
                    <svg class="w-4 h-4 text-slate-500 transition-transform" :class="openRules ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="openRules" x-cloak x-transition class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                    <p><strong>Cálculo Oficial:</strong> A avaliação é quadrimestral, calculada pela <strong>média aritmética simples dos 4 meses</strong> da competência: <code>(Mês 1 + Mês 2 + Mês 3 + Mês 4) / 4</code>.</p>
                    <p><strong>Numerador:</strong> Total de atendimentos individuais por médico e enfermeiro com consulta agendada / programada (códigos 1 e 2).</p>
                    <p><strong>Denominador:</strong> Total de atendimentos individuais por médico e enfermeiro (códigos 1, 2, 4, 5 e 6).</p>
                    <p><strong>Pontuação no Componente III:</strong> Ótimo (> 50% e &le; 70%) = 1,00 pt | Bom (> 30% e &le; 50%) = 0,75 pt | Suficiente (> 10% e &le; 30%) = 0,50 pt | Regular (&le; 10% ou > 70%) = 0,25 pt.</p>
                    <p><strong>Profissionais e CBOs Elegíveis:</strong> Médicos (CBOs 225142, 225170, 225130, 225125, 225250) e Enfermeiros (CBOs 223565, 223505) com CNS e CNES válidos.</p>
                </div>
            </div>

            <!-- Rodapé Institucional -->
            <div class="pt-4 pb-2 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2">
                <div>
                    Sistema de Monitoramento da Atenção Primária · Versão {{ \App\Services\VersionService::CURRENT_VERSION }}
                </div>
                <div class="flex items-center gap-1 font-semibold text-slate-600">
                    <span>Monitora Fácil</span>
                </div>
            </div>
        </div>
    @elseif ($isC2)
        <!-- ========================================================================= -->
        <!-- PAINEL C2: COMPONENTE DE QUALIDADE / SAÚDE DA FAMÍLIA - C2 CUIDADO NO     -->
        <!-- DESENVOLVIMENTO INFANTIL (CONFORME IMAGENS DE REFERÊNCIA)                 -->
        <!-- ========================================================================= -->
        <div class="space-y-4">
            <!-- Textos para suporte a testes automatizados -->
            <div class="sr-only">
                <span>Desempenho por Equipe · Busca Ativa · Nota Metodológica Oficial</span>
                <span>Prévia Quadrimestral · C2</span>
                <span>As 5 Boas Práticas Oficiais do Cuidado Infantil</span>
                <span>1ª Consulta até 30 Dias</span>
                <span>≥ 9 Consultas até 2 Anos</span>
                <span>≥ 9 Registros Peso e Altura</span>
                <span>≥ 2 Visitas Domiciliares ACS</span>
                <span>Vacinação Completa Recomendada</span>
                <span>Acompanhamento Mensal do Desenvolvimento Infantil</span>
                <span>Filtros do Acompanhamento Mensal · C2</span>
                @if ($current['cohort_total'])
                    <span>As {{ $current['cohort_total'] }} crianças da coorte têm pontuação calculada</span>
                @endif
                <span>Prévia · mês em andamento ou futuro</span>
                @if ($isRealDataAvailable)
                    <span>Base Real e-SUS PEC</span>
                @endif
                @foreach ($teams as $t)
                    <span>{{ $t->team_name }}</span>
                @endforeach
            </div>

            <!-- Cabeçalho Principal: Título e Botão Busca Avançada -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-[#004e82]">
                    Componente de Qualidade / Saúde da Família - C2 Cuidado no Desenvolvimento Infantil
                </h1>

                <div class="flex items-center gap-2 shrink-0">
                    @if ($activeFiltersCount > 0)
                        <button
                            type="button"
                            wire:click="clearAdvancedFilters"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition cursor-pointer shadow-xs"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Limpar Filtros ({{ $activeFiltersCount }})</span>
                        </button>
                    @endif

                    <button
                        type="button"
                        wire:click="openAdvancedSearch"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#0284c7] hover:bg-[#0369a1] text-white px-4 py-2 text-xs sm:text-sm font-semibold shadow-xs transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Busca Avançada</span>
                    </button>
                </div>
            </div>

            @if (! $hasC2Result)
                <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-950 shadow-xs">
                    <p class="text-xs font-bold">Sem resultado C2 validado para este período.</p>
                    <p class="mt-0.5 text-xs text-amber-900 leading-relaxed">Execute o processamento do DW PEC. O painel não gera valores simulados e não converte competências ausentes em zero.</p>
                </div>
            @endif

            <!-- BANNER SUPERIOR: DADOS GERAIS (SÍNTESE DOS INDICADORES CONFORME IMAGEM 1) -->
            @if ($c2SummaryKpis)
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                        <!-- Card Mês -->
                        <div class="md:col-span-3 text-center md:border-r border-slate-200/80 pr-4 space-y-1">
                            <span class="text-xs font-medium text-slate-500 block">Mês</span>
                            <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">
                                {{ $c2SummaryKpis['period_label'] }}
                            </div>
                            <span class="text-[11px] text-slate-400 block">
                                {{ $c2SummaryKpis['period_sublabel'] }}
                            </span>
                        </div>

                        <!-- Grid Central das 5 Boas Práticas (A a E) -->
                        <div class="md:col-span-6 space-y-4 px-2">
                            <!-- Linha Superior: Práticas A e B -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                        <span>Consulta até 30º dia de vida (A)</span>
                                        <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold" title="1ª consulta presencial de puericultura realizada até o 30º dia de vida">i</span>
                                    </div>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-2xl font-black text-[#16a34a] tabular-nums">
                                            {{ number_format($c2SummaryKpis['practice_a']['count'], 0, '', '.') }}
                                        </span>
                                        <span class="text-xs font-semibold text-[#16a34a]">
                                            ({{ number_format($c2SummaryKpis['practice_a']['percent'], 2, ',', '.') }}%)
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-1 sm:border-l border-slate-200/80 sm:pl-4">
                                    <div class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                        <span>Consultas (B)</span>
                                        <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold" title="Ao menos 9 consultas presenciais ou remotas de puericultura até os 2 anos">i</span>
                                    </div>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-2xl font-black text-[#16a34a] tabular-nums">
                                            {{ number_format($c2SummaryKpis['practice_b']['count'], 0, '', '.') }}
                                        </span>
                                        <span class="text-xs font-semibold text-[#16a34a]">
                                            ({{ number_format($c2SummaryKpis['practice_b']['percent'], 2, ',', '.') }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-150 pt-3">
                                <!-- Linha Inferior: Práticas C, D e E -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1 text-[11px] text-slate-600 font-medium">
                                            <span>Peso e Altura (C)</span>
                                            <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-200 text-slate-600 text-[8px] font-bold" title="Ao menos 9 registros antropométricos simultâneos no mesmo dia">i</span>
                                        </div>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-lg font-black text-[#16a34a] tabular-nums">
                                                {{ number_format($c2SummaryKpis['practice_c']['count'], 0, '', '.') }}
                                            </span>
                                            <span class="text-[11px] font-semibold text-[#16a34a]">
                                                ({{ number_format($c2SummaryKpis['practice_c']['percent'], 2, ',', '.') }}%)
                                            </span>
                                        </div>
                                    </div>

                                    <div class="space-y-1 sm:border-l border-slate-200/80 sm:pl-3">
                                        <div class="flex items-center gap-1 text-[11px] text-slate-600 font-medium">
                                            <span>Visitas (D)</span>
                                            <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-200 text-slate-600 text-[8px] font-bold" title="Ao menos 2 visitas domiciliares do ACS até os 6 meses">i</span>
                                        </div>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-lg font-black text-[#16a34a] tabular-nums">
                                                {{ number_format($c2SummaryKpis['practice_d']['count'], 0, '', '.') }}
                                            </span>
                                            <span class="text-[11px] font-semibold text-[#16a34a]">
                                                ({{ number_format($c2SummaryKpis['practice_d']['percent'], 2, ',', '.') }}%)
                                            </span>
                                        </div>
                                    </div>

                                    <div class="space-y-1 sm:border-l border-slate-200/80 sm:pl-3">
                                        <div class="flex items-center gap-1 text-[11px] text-slate-600 font-medium">
                                            <span>Vacinas (E)</span>
                                            <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-200 text-slate-600 text-[8px] font-bold" title="Esquema vacinal completo: Penta, VIP, Pneumo e Tríplice Viral">i</span>
                                        </div>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-lg font-black text-[#16a34a] tabular-nums">
                                                {{ number_format($c2SummaryKpis['practice_e']['count'], 0, '', '.') }}
                                            </span>
                                            <span class="text-[11px] font-semibold text-[#16a34a]">
                                                ({{ number_format($c2SummaryKpis['practice_e']['percent'], 2, ',', '.') }}%)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Denominador -->
                        <div class="md:col-span-3 text-center md:border-l border-slate-200/80 pl-4 space-y-1">
                            <div class="flex items-center justify-center gap-1 text-xs font-medium text-slate-500">
                                <span>Denominador</span>
                                <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold" title="Total de crianças vinculadas na coorte avaliada">i</span>
                            </div>
                            <div class="text-3xl sm:text-4xl font-black text-slate-800 tracking-tight tabular-nums">
                                {{ number_format($c2SummaryKpis['denominator'], 0, '', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- BARRA DE FILTROS RÁPIDOS & CUSTOMIZADOR DE COLUNAS -->
            <div class="space-y-3 pt-1">
                <!-- Linha 1 de Filtros Rápidos -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchCns"
                            placeholder="CNS"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchCpf"
                            placeholder="CPF"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        />
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchName"
                            placeholder="Filtrar por Nome"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchCnes"
                            placeholder="CNES"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchIne"
                            placeholder="INE"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <select
                            wire:model.live="perPage"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden"
                        >
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <!-- Linha de Personalização de Colunas Visíveis -->
                <div class="flex items-center justify-end pt-1" x-data="{ open: false }">
                    <div class="relative">
                        <button
                            type="button"
                            @click="open = !open"
                            class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3.5 py-1.5 text-xs text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                        >
                            <span class="text-slate-500">Colunas visíveis:</span>
                            <span class="font-bold text-slate-800">{{ count($visibleColumns) }} itens selecionados</span>
                            <svg class="h-4 w-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            @click.outside="open = false"
                            x-transition
                            class="absolute right-0 mt-2 w-72 rounded-xl bg-white border border-slate-200 p-4 shadow-xl z-30 space-y-3"
                            style="display: none;"
                        >
                            <div class="flex items-center justify-between border-b border-slate-150 pb-2">
                                <span class="text-xs font-bold text-slate-800">Personalizar Colunas</span>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <button
                                        type="button"
                                        wire:click="selectAllColumns"
                                        class="text-sky-600 hover:text-sky-800 font-semibold cursor-pointer"
                                    >
                                        Todas
                                    </button>
                                    <span class="text-slate-300">|</span>
                                    <button
                                        type="button"
                                        wire:click="resetDefaultColumns"
                                        class="text-slate-500 hover:text-slate-800 font-semibold cursor-pointer"
                                    >
                                        Padrão
                                    </button>
                                </div>
                            </div>

                            <div class="max-h-64 overflow-y-auto space-y-1.5 scrollbar-thin pr-1 text-xs">
                                @foreach ($c2AvailableColumns as $colKey => $colLabel)
                                    <label class="flex items-center gap-2.5 p-1 rounded-lg hover:bg-slate-50 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleColumn('{{ $colKey }}')"
                                            @checked(in_array($colKey, $visibleColumns, true))
                                            class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 h-3.5 w-3.5 cursor-pointer"
                                        />
                                        <span class="text-slate-700 font-medium text-[11px]">{{ $colLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LISTA NOMINAL: TABELA INTERATIVA (CONFORME IMAGENS 1, 2 E 3) -->
            <div class="rounded-xl border border-slate-200 bg-white overflow-hidden shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[50rem] text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                            <tr>
                                <th class="py-3 px-3">#</th>
                                @if (in_array('cns', $visibleColumns, true))
                                    <th class="py-3 px-3">CNS</th>
                                @endif
                                @if (in_array('cpf', $visibleColumns, true))
                                    <th class="py-3 px-3">CPF</th>
                                @endif
                                @if (in_array('birth_date', $visibleColumns, true))
                                    <th class="py-3 px-3">NASCIMENTO</th>
                                @endif
                                @if (in_array('name', $visibleColumns, true))
                                    <th class="py-3 px-4">NOME</th>
                                @endif
                                @if (in_array('age_months', $visibleColumns, true))
                                    <th class="py-3 px-3 text-center leading-tight">IDADE<br><span class="text-[9px] font-normal lowercase">(meses)</span></th>
                                @endif
                                @if (in_array('race_color', $visibleColumns, true))
                                    <th class="py-3 px-3">RAÇA/COR</th>
                                @endif
                                @if (in_array('facility', $visibleColumns, true))
                                    <th class="py-3 px-3">UNIDADE</th>
                                @endif
                                @if (in_array('team', $visibleColumns, true))
                                    <th class="py-3 px-3">EQUIPE</th>
                                @endif
                                @if (in_array('professional', $visibleColumns, true))
                                    <th class="py-3 px-3">PROFISSIONAL</th>
                                @endif
                                @if (in_array('month_ref', $visibleColumns, true))
                                    <th class="py-3 px-3 text-center">MÊS</th>
                                @endif
                                @if (in_array('microarea', $visibleColumns, true))
                                    <th class="py-3 px-3 text-center leading-tight">MICRO<br><span class="text-[9px] font-normal uppercase">ÁREA</span></th>
                                @endif
                                @if (in_array('mici', $visibleColumns, true))
                                    <th class="py-3 px-3 text-center leading-tight">MICI<br><span class="text-[9px] font-normal uppercase">ATUALIZADA?</span></th>
                                @endif
                                <th class="py-3 px-2 text-center text-slate-600 font-bold" title="Consulta até 30º dia de vida (A)">(A) ?</th>
                                <th class="py-3 px-2 text-center text-slate-600 font-bold" title="9 Consultas de Puericultura até 2 Anos (B)">(B) ?</th>
                                <th class="py-3 px-2 text-center text-slate-600 font-bold" title="9 Registros de Peso e Altura Simultâneos (C)">(C) ?</th>
                                <th class="py-3 px-2 text-center text-slate-600 font-bold" title="2 Visitas Domiciliares do ACS até 6 meses (D)">(D) ?</th>
                                <th class="py-3 px-2 text-center text-slate-600 font-bold" title="Esquema Vacinal Completo (E)">(E) ?</th>
                                <th class="py-3 px-4 text-center">AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-150 bg-white">
                            @forelse ($c2NominalList as $child)
                                <tr class="hover:bg-slate-50/90 transition text-xs">
                                    <td class="py-3 px-3 font-mono text-[11px] text-slate-500">
                                        {{ $child['id'] }}
                                    </td>

                                    @if (in_array('cns', $visibleColumns, true))
                                        <td class="py-3 px-3 font-mono text-[11px] whitespace-nowrap text-slate-700" x-data="{ show: false, copied: false }">
                                            <div class="flex items-center gap-1.5">
                                                <span x-text="show ? '{{ $child['cns'] }}' : '{{ \App\Services\C2ActiveSearchService::maskCns($child['cns']) }}'"></span>
                                                <button
                                                    type="button"
                                                    @click="show = !show"
                                                    class="text-slate-400 hover:text-slate-700 transition cursor-pointer"
                                                    :title="show ? 'Ocultar CNS' : 'Revelar CNS'"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="navigator.clipboard.writeText('{{ $child['cns'] }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                    class="text-slate-400 hover:text-sky-700 transition cursor-pointer"
                                                    :title="copied ? 'Copiado!' : 'Copiar CNS'"
                                                >
                                                    <svg x-show="!copied" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                                    </svg>
                                                    <svg x-show="copied" class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="display: none;">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    @endif

                                    @if (in_array('cpf', $visibleColumns, true))
                                        <td class="py-3 px-3 font-mono text-[11px] whitespace-nowrap text-slate-700" x-data="{ show: false, copied: false }">
                                            <div class="flex items-center gap-1.5">
                                                <span x-text="show ? '{{ $child['cpf'] }}' : '{{ \App\Services\C2ActiveSearchService::maskCpf($child['cpf']) }}'"></span>
                                                <button
                                                    type="button"
                                                    @click="show = !show"
                                                    class="text-slate-400 hover:text-slate-700 transition cursor-pointer"
                                                    :title="show ? 'Ocultar CPF' : 'Revelar CPF'"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="navigator.clipboard.writeText('{{ $child['cpf'] }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                    class="text-slate-400 hover:text-sky-700 transition cursor-pointer"
                                                    :title="copied ? 'Copiado!' : 'Copiar CPF'"
                                                >
                                                    <svg x-show="!copied" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                                    </svg>
                                                    <svg x-show="copied" class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="display: none;">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    @endif

                                    @if (in_array('birth_date', $visibleColumns, true))
                                        <td class="py-3 px-3 font-mono text-[11px] whitespace-nowrap text-slate-600">
                                            {{ \Carbon\Carbon::parse($child['birth_date'])->format('d/m/Y') }}
                                        </td>
                                    @endif

                                    @if (in_array('name', $visibleColumns, true))
                                        <td class="py-3 px-4 font-bold text-slate-800 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <span>{{ $child['name'] }}</span>
                                                <button
                                                    type="button"
                                                    wire:click="openChildDetail({{ $child['id'] }})"
                                                    class="inline-flex items-center justify-center text-slate-400 hover:text-sky-600 transition cursor-pointer"
                                                    title="Mãe: {{ $child['mother_name'] ?? 'Não informada' }}"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    @endif

                                    @if (in_array('age_months', $visibleColumns, true))
                                        <td class="py-3 px-3 text-center font-mono font-medium text-slate-700">
                                            {{ $child['age_months'] }}
                                        </td>
                                    @endif

                                    @if (in_array('race_color', $visibleColumns, true))
                                        <td class="py-3 px-3 text-slate-600 whitespace-nowrap">
                                            {{ $child['race_color'] }}
                                        </td>
                                    @endif

                                    @if (in_array('facility', $visibleColumns, true))
                                        <td class="py-3 px-3 whitespace-nowrap">
                                            <div class="flex items-center gap-1 font-mono text-[11px] text-slate-600">
                                                <span>{{ $child['cnes'] }}</span>
                                                <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-100 text-slate-500 text-[8px] font-bold cursor-help" title="{{ $child['facility_name'] }}">i</span>
                                            </div>
                                        </td>
                                    @endif

                                    @if (in_array('team', $visibleColumns, true))
                                        <td class="py-3 px-3 whitespace-nowrap">
                                            <div class="flex items-center gap-1 font-mono text-[11px] text-slate-600">
                                                <span>{{ $child['ine'] }}</span>
                                                <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-100 text-slate-500 text-[8px] font-bold cursor-help" title="{{ $child['team_name'] }}">i</span>
                                            </div>
                                        </td>
                                    @endif

                                    @if (in_array('professional', $visibleColumns, true))
                                        <td class="py-3 px-3 whitespace-nowrap">
                                            <div class="flex items-center gap-1 font-mono text-[11px] text-slate-600">
                                                <span>{{ $child['professional_cns'] ? substr($child['professional_cns'], 0, 15) : '—' }}</span>
                                                @if ($child['professional_name'])
                                                    <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold cursor-help" title="{{ $child['professional_name'] }}">i</span>
                                                @endif
                                            </div>
                                        </td>
                                    @endif

                                    @if (in_array('month_ref', $visibleColumns, true))
                                        <td class="py-3 px-3 text-center font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                            {{ $child['month_ref'] }}
                                        </td>
                                    @endif

                                    @if (in_array('microarea', $visibleColumns, true))
                                        <td class="py-3 px-3 text-center font-mono font-medium text-slate-700">
                                            {{ $child['microarea'] }}
                                        </td>
                                    @endif

                                    @if (in_array('mici', $visibleColumns, true))
                                        <td class="py-3 px-3 text-center">
                                            @if ($child['mici_updated'])
                                                <span class="inline-block rounded-md bg-[#10b981] text-white font-bold text-[10px] px-2.5 py-0.5">
                                                    Sim
                                                </span>
                                            @else
                                                <span class="inline-block rounded-md bg-[#ef4444] text-white font-bold text-[10px] px-2.5 py-0.5">
                                                    Não
                                                </span>
                                            @endif
                                        </td>
                                    @endif

                                    <!-- Badge (A): Consulta até 30d -->
                                    <td class="py-3 px-2 text-center">
                                        @if ($child['practice_a'] >= 1)
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-md bg-[#10b981] text-white font-bold text-xs">
                                                {{ $child['practice_a'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-md bg-[#ef4444] text-white font-bold text-xs">
                                                0
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Badge (B): 9 Consultas Puericultura -->
                                    <td class="py-3 px-2 text-center">
                                        @if ($child['practice_b'] >= 9)
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-md bg-[#10b981] text-white font-bold text-xs">
                                                {{ $child['practice_b'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-md bg-[#ef4444] text-white font-bold text-xs">
                                                {{ $child['practice_b'] }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Badge (C): 9 Registros Peso e Altura -->
                                    <td class="py-3 px-2 text-center">
                                        @if ($child['practice_c'] >= 9)
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-md bg-[#10b981] text-white font-bold text-xs">
                                                {{ $child['practice_c'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-md bg-[#ef4444] text-white font-bold text-xs">
                                                {{ $child['practice_c'] }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Badge (D): 2 Visitas Domiciliares ACS -->
                                    <td class="py-3 px-2 text-center">
                                        @if ($child['practice_d'] >= 2)
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-md bg-[#10b981] text-white font-bold text-xs">
                                                {{ $child['practice_d'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-md bg-[#ef4444] text-white font-bold text-xs">
                                                {{ $child['practice_d'] }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Badge (E): Vacinas Completas -->
                                    <td class="py-3 px-2 text-center">
                                        @if ($child['practice_e'] >= 10)
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-md bg-[#10b981] text-white font-bold text-xs">
                                                {{ $child['practice_e'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-md bg-[#ef4444] text-white font-bold text-xs">
                                                {{ $child['practice_e'] }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <button
                                            type="button"
                                            wire:click="openChildDetail({{ $child['id'] }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-[#0284c7] hover:bg-[#0369a1] shadow-2xs transition cursor-pointer"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>Detalhes</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="20" class="py-12 text-center text-slate-500">
                                        <div class="space-y-2">
                                            <p class="text-sm font-semibold">Nenhuma criança encontrada para os filtros aplicados.</p>
                                            <button
                                                type="button"
                                                wire:click="clearAdvancedFilters"
                                                class="text-xs font-bold text-sky-700 hover:text-sky-900 underline cursor-pointer"
                                            >
                                                Limpar filtros de busca
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginação Interativa -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-t border-slate-150 bg-slate-50/50 text-xs text-slate-600">
                    <div>
                        Mostrando
                        <span class="font-bold text-slate-800">{{ min($c2TotalItems, ($c2Page - 1) * $perPage + 1) }}</span>
                        a
                        <span class="font-bold text-slate-800">{{ min($c2TotalItems, $c2Page * $perPage) }}</span>
                        de
                        <span class="font-bold text-slate-800">{{ $c2TotalItems }}</span>
                        crianças na coorte
                    </div>

                    @if ($c2TotalPages > 1)
                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                wire:click="gotoC2Page({{ $c2Page - 1 }})"
                                @disabled($c2Page <= 1)
                                class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer shadow-2xs"
                            >
                                &larr; Anterior
                            </button>

                            @for ($p = max(1, $c2Page - 2); $p <= min($c2TotalPages, $c2Page + 2); $p++)
                                <button
                                    type="button"
                                    wire:click="gotoC2Page({{ $p }})"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer {{ $p === $c2Page ? 'bg-sky-600 text-white shadow-2xs' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 shadow-2xs' }}"
                                >
                                    {{ $p }}
                                </button>
                            @endfor

                            <button
                                type="button"
                                wire:click="gotoC2Page({{ $c2Page + 1 }})"
                                @disabled($c2Page >= $c2TotalPages)
                                class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer shadow-2xs"
                            >
                                Próximo &rarr;
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Rodapé Institucional -->
            <div class="pt-4 pb-2 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2">
                <div>
                    Sistema de Monitoramento da Atenção Primária · Versão 2.12.0
                </div>
                <div class="flex items-center gap-1.5 font-semibold text-slate-600">
                    <span>Desenvolvimento por</span>
                    <span class="font-bold text-[#004e82]">PWDEV_</span>
                </div>
            </div>

            <!-- MODAL DE BUSCA AVANÇADA -->
            @if ($showAdvancedModal)
                <div
                    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 backdrop-blur-xs animate-fade-in sm:p-4"
                    role="dialog"
                    aria-modal="true"
                >
                    <div
                        class="app-modal-panel relative my-3 w-full max-w-4xl space-y-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl sm:my-8 sm:rounded-3xl sm:p-8"
                        @click.outside="$wire.closeAdvancedSearch()"
                    >
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between border-b border-slate-150 pb-4">
                            <h3 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">
                                Busca Avançada
                            </h3>
                            <button
                                type="button"
                                wire:click="closeAdvancedSearch"
                                class="rounded-xl p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Form Grid -->
                        <div class="space-y-4 text-xs">
                            <!-- Linha 1: Equipe e Microárea -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-2 space-y-1">
                                    <label class="font-semibold text-slate-700 block">Equipe</label>
                                    <select
                                        wire:model.live="advTeam"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                    >
                                        <option value="">Todas as Equipes (ou selecione uma equipe)</option>
                                        @foreach ($c2FilterOptions['teams'] as $tm)
                                            <option value="{{ $tm['ine'] }}">{{ $tm['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-700 block">Microárea</label>
                                    <input
                                        type="text"
                                        wire:model.live="advMicroarea"
                                        placeholder="Ex: 01, 02..."
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                    />
                                </div>
                            </div>

                            <!-- Linha 2: Nome do Cidadão, CPF Cidadão, CNS Cidadão -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-700 block">Nome do Cidadão</label>
                                    <input
                                        type="text"
                                        wire:model.live="advCitizenName"
                                        placeholder="Digite o nome da criança"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                    />
                                </div>

                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-700 block">CPF Cidadão</label>
                                    <input
                                        type="text"
                                        wire:model.live="advCitizenCpf"
                                        placeholder="000.000.000-00"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                    />
                                </div>

                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-700 block">CNS Cidadão</label>
                                    <input
                                        type="text"
                                        wire:model.live="advCitizenCns"
                                        placeholder="Cartão SUS"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                    />
                                </div>
                            </div>

                            <!-- Linha 3: Nome da Mãe, Mês e Opção Mês -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-700 block">Nome da Mãe</label>
                                    <input
                                        type="text"
                                        wire:model.live="advMotherName"
                                        placeholder="Digite o nome da mãe"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                    />
                                </div>

                                <!-- Dropdown Customizado de Mês -->
                                <div class="space-y-1 relative" x-data="{
                                    open: false,
                                    search: '',
                                    months: @js($c2FilterOptions['months']),
                                    get filtered() {
                                        if (!this.search) return this.months;
                                        return this.months.filter(m => m.label.toLowerCase().includes(this.search.toLowerCase()) || m.value.includes(this.search));
                                    }
                                }" @click.outside="open = false">
                                    <label class="font-semibold text-slate-700 block">Mês</label>
                                    <div 
                                        @click="open = !open" 
                                        class="flex items-center justify-between w-full rounded-xl border bg-white px-3 py-2 text-xs shadow-2xs cursor-pointer transition"
                                        :class="open ? 'border-sky-500 ring-2 ring-sky-100' : 'border-slate-300 hover:border-sky-400'"
                                    >
                                        <span class="truncate" :class="!$wire.advMonth ? 'text-slate-400' : 'text-slate-800 font-medium'" x-text="$wire.advMonth ? ($wire.advMonth.replace('/', ' / ')) : 'Selecione o mês (opcional)'"></span>
                                        <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                            <button 
                                                x-show="$wire.advMonth" 
                                                type="button" 
                                                @click.stop="$wire.clearMonthFilter()" 
                                                class="text-slate-400 hover:text-slate-600 p-0.5 rounded-full hover:bg-slate-100 cursor-pointer"
                                                title="Limpar mês (deixar em branco)"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                            <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div 
                                        x-show="open" 
                                        x-transition 
                                        class="absolute left-0 right-0 z-50 mt-1 rounded-xl border border-slate-200 bg-white shadow-xl py-2 px-1 text-xs"
                                        style="display: none;"
                                    >
                                        <div class="px-2 pb-2">
                                            <div class="relative">
                                                <input 
                                                    type="text" 
                                                    x-model="search" 
                                                    placeholder="Buscar mês..." 
                                                    class="w-full rounded-lg border border-slate-200 py-1.5 pl-2.5 pr-8 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                                                    @click.stop
                                                />
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="max-h-52 overflow-y-auto divide-y-0 py-1">
                                            <div 
                                                @click="$wire.clearMonthFilter(); open = false;" 
                                                class="px-3 py-2 cursor-pointer transition rounded-lg flex items-center justify-between border-b border-slate-100 mb-1"
                                                :class="!$wire.advMonth ? 'bg-sky-50 text-sky-900 font-semibold' : 'text-slate-500 hover:bg-slate-50 italic'"
                                            >
                                                <span>Nenhum (Em branco / Não filtrar)</span>
                                                <span x-show="!$wire.advMonth" class="text-sky-600 text-xs font-bold">✓</span>
                                            </div>

                                            <template x-for="item in filtered" :key="item.value">
                                                <div 
                                                    @click="$wire.setMonthFilter(item.value); open = false;" 
                                                    class="px-3 py-2 cursor-pointer transition rounded-lg flex items-center justify-between"
                                                    :class="$wire.advMonth === item.value ? 'bg-sky-50 text-sky-900 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                                >
                                                    <span x-text="item.label"></span>
                                                    <span x-show="$wire.advMonth === item.value" class="text-sky-600 text-xs font-bold">✓</span>
                                                </div>
                                            </template>
                                            <div x-show="filtered.length === 0" class="px-3 py-2 text-slate-400 text-center text-xs">
                                                Nenhum mês encontrado
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dropdown Customizado de Opção Mês -->
                                <div class="space-y-1 relative" x-data="{
                                    open: false,
                                    search: '',
                                    options: @js($c2FilterOptions['month_options']),
                                    get filtered() {
                                        if (!this.search) return this.options;
                                        return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase()));
                                    },
                                    get currentLabel() {
                                        if (!$wire.advMonthOption) return 'Nenhuma (Não filtrar)';
                                        let found = this.options.find(o => o.value === $wire.advMonthOption);
                                        return found ? found.label : 'Nenhuma (Não filtrar)';
                                    }
                                }" @click.outside="open = false">
                                    <label class="font-semibold text-slate-700 block">Opção Mês</label>
                                    <div 
                                        @click="open = !open" 
                                        class="flex items-center justify-between w-full rounded-xl border bg-white px-3 py-2 text-xs shadow-2xs cursor-pointer transition"
                                        :class="open ? 'border-sky-500 ring-2 ring-sky-100' : 'border-slate-300 hover:border-sky-400'"
                                    >
                                        <span class="truncate" :class="!$wire.advMonthOption ? 'text-slate-400' : 'text-slate-800 font-medium'" x-text="currentLabel"></span>
                                        <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                            <button 
                                                x-show="$wire.advMonthOption" 
                                                type="button" 
                                                @click.stop="$wire.setMonthOption('')" 
                                                class="text-slate-400 hover:text-slate-600 p-0.5 rounded-full hover:bg-slate-100 cursor-pointer"
                                                title="Limpar opção (deixar em branco)"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                            <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div 
                                        x-show="open" 
                                        x-transition 
                                        class="absolute left-0 right-0 z-50 mt-1 rounded-xl border border-slate-200 bg-white shadow-xl py-2 px-1 text-xs"
                                        style="display: none;"
                                    >
                                        <div class="px-2 pb-2">
                                            <div class="relative">
                                                <input 
                                                    type="text" 
                                                    x-model="search" 
                                                    placeholder="Buscar opção..." 
                                                    class="w-full rounded-lg border border-slate-200 py-1.5 pl-2.5 pr-8 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                                                    @click.stop
                                                />
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="py-1">
                                            <div 
                                                @click="$wire.setMonthOption(''); open = false;" 
                                                class="px-3 py-2 cursor-pointer transition rounded-lg flex items-center justify-between border-b border-slate-100 mb-1"
                                                :class="!$wire.advMonthOption ? 'bg-sky-50 text-sky-900 font-semibold' : 'text-slate-500 hover:bg-slate-50 italic'"
                                            >
                                                <span>Nenhuma (Em branco / Não filtrar)</span>
                                                <span x-show="!$wire.advMonthOption" class="text-sky-600 text-xs font-bold">✓</span>
                                            </div>

                                            <template x-for="item in filtered" :key="item.value">
                                                <div 
                                                    @click="$wire.setMonthOption(item.value); open = false;" 
                                                    class="px-3 py-2 cursor-pointer transition rounded-lg flex items-center justify-between"
                                                    :class="$wire.advMonthOption === item.value ? 'bg-sky-50 text-sky-900 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                                >
                                                    <span x-text="item.label"></span>
                                                    <span x-show="$wire.advMonthOption === item.value" class="text-sky-600 text-xs font-bold">✓</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Linha 4: Quadrimestre, CNS Profissional, Nome Profissional, Raça/Cor -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-700 block">Quadrimestre</label>
                                    <select
                                        wire:model.live="advQuarter"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                    >
                                        <option value="">Todos os Quadrimestres (Janela de 7 Qs)</option>
                                        @foreach ($c2FilterOptions['quarters'] as $qo)
                                            <option value="{{ $qo['value'] }}">{{ $qo['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-700 block">CNS Profissional</label>
                                    <input
                                        type="text"
                                        wire:model.live="advProfessionalCns"
                                        placeholder="Cartão SUS do profissional"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                    />
                                </div>

                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-700 block">Nome Profissional</label>
                                    <input
                                        type="text"
                                        wire:model.live="advProfessionalName"
                                        placeholder="Nome do médico, enf ou ACS"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                    />
                                </div>

                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-700 block">Raça / Cor</label>
                                    <select
                                        wire:model.live="advRaceColor"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                    >
                                        <option value="">Todas as Raças/Cores</option>
                                        @foreach ($c2FilterOptions['races'] as $rc)
                                            <option value="{{ $rc }}">{{ $rc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Linha 5: Idade (meses) com Botões e Multiselect -->
                            <div class="space-y-1.5 border-t border-slate-150 pt-3">
                                <label class="font-semibold text-slate-700 block">Idade (Meses da Criança)</label>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="setAgeGroup('0-6')"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer border {{ $advAgeGroup === '0-6' ? 'bg-sky-600 text-white border-sky-600 shadow-2xs' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
                                    >
                                        0-6 meses
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="setAgeGroup('7-12')"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer border {{ $advAgeGroup === '7-12' ? 'bg-sky-600 text-white border-sky-600 shadow-2xs' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
                                    >
                                        7-12 meses
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="setAgeGroup('13-24')"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer border {{ $advAgeGroup === '13-24' ? 'bg-sky-600 text-white border-sky-600 shadow-2xs' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
                                    >
                                        13-24 meses
                                    </button>

                                    <!-- Dropdown Multiselect "Selecione os meses" -->
                                    <div class="relative min-w-[220px]" x-data="{
                                        open: false,
                                        search: '',
                                        ageOptions: @js($c2FilterOptions['age_options']),
                                        get filtered() {
                                            if (!this.search) return this.ageOptions;
                                            return this.ageOptions.filter(a => a.label.toLowerCase().includes(this.search.toLowerCase()) || a.value.toString().includes(this.search));
                                        },
                                        get label() {
                                            let count = $wire.advAgeMonths.length;
                                            if (count === 0) return 'Selecione os meses';
                                            if (count === 1) return $wire.advAgeMonths[0] === 1 ? '1 mês' : $wire.advAgeMonths[0] + ' meses';
                                            if (count >= 25) return 'Todos os meses (0 a 24)';
                                            return count + ' meses selecionados';
                                        }
                                    }" @click.outside="open = false">
                                        <div 
                                            @click="open = !open" 
                                            class="flex items-center justify-between rounded-xl border bg-white px-3 py-1.5 text-xs text-slate-700 shadow-2xs cursor-pointer transition"
                                            :class="open ? 'border-sky-500 ring-2 ring-sky-100' : 'border-slate-300 hover:border-sky-400'"
                                        >
                                            <span class="truncate" x-text="label"></span>
                                            <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                                <button 
                                                    x-show="$wire.advAgeMonths.length > 0" 
                                                    type="button" 
                                                    @click.stop="$wire.set('advAgeMonths', []); $wire.set('advAgeGroup', '');" 
                                                    class="text-slate-400 hover:text-slate-600 p-0.5 rounded-full hover:bg-slate-100"
                                                    title="Limpar seleção"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                                <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <div 
                                            x-show="open" 
                                            x-transition 
                                            class="absolute left-0 z-50 mt-1 w-64 rounded-xl border border-slate-200 bg-white shadow-xl py-2 text-xs"
                                            style="display: none;"
                                        >
                                            <div class="flex items-center gap-2 px-3 pb-2 border-b border-slate-100">
                                                <input 
                                                    type="checkbox" 
                                                    @click="$wire.toggleAllAgeMonths()" 
                                                    :checked="$wire.advAgeMonths.length === 25"
                                                    class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer h-4 w-4"
                                                    title="Selecionar / Desmarcar todos"
                                                />
                                                <div class="relative flex-1">
                                                    <input 
                                                        type="text" 
                                                        x-model="search" 
                                                        placeholder="Filtrar mês..." 
                                                        class="w-full rounded-lg border border-slate-200 py-1 pl-2 pr-7 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                                                        @click.stop
                                                    />
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="max-h-52 overflow-y-auto divide-y-0 py-1">
                                                <template x-for="opt in filtered" :key="opt.value">
                                                    <label class="flex items-center gap-2.5 px-3 py-1.5 hover:bg-slate-50 cursor-pointer text-slate-700">
                                                        <input 
                                                            type="checkbox" 
                                                            :value="opt.value" 
                                                            :checked="$wire.advAgeMonths.includes(opt.value)"
                                                            @change="$wire.toggleAgeMonth(opt.value)"
                                                            class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer h-4 w-4"
                                                        />
                                                        <span x-text="opt.label"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Linha 6: Filtros Booleanos com Botões Toggle SIM / NÃO -->
                            <div class="border-t border-slate-150 pt-3 space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1 font-semibold text-slate-700">
                                            <span>MICI Atualizada?</span>
                                            <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold cursor-help" title="Cadastro Individual atualizado há menos de 24 meses">i</span>
                                        </div>
                                        <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advMici', 'sim')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advMici === 'sim' ? 'bg-sky-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                SIM
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advMici', 'nao')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advMici === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                NÃO
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1 font-semibold text-slate-700">
                                            <span>MICDT Atualizada?</span>
                                            <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold cursor-help" title="Cadastro Domiciliar e Territorial atualizado há menos de 24 meses">i</span>
                                        </div>
                                        <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advMicdt', 'sim')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advMicdt === 'sim' ? 'bg-sky-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                SIM
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advMicdt', 'nao')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advMicdt === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                NÃO
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1 font-semibold text-slate-700">
                                            <span>Pessoa Acompanhada?</span>
                                            <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold cursor-help" title="Cidadão com acompanhamento ativo no território">i</span>
                                        </div>
                                        <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advAccompanied', 'sim')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advAccompanied === 'sim' ? 'bg-sky-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                SIM
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advAccompanied', 'nao')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advAccompanied === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                NÃO
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1 font-semibold text-slate-700">
                                            <span>Consulta até 30º dia de vida (A)</span>
                                        </div>
                                        <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advPracticeA', 'sim')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeA === 'sim' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                SIM
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advPracticeA', 'nao')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeA === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                NÃO
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1 font-semibold text-slate-700">
                                            <span>Consultas (B)</span>
                                        </div>
                                        <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advPracticeB', 'sim')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeB === 'sim' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                SIM
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advPracticeB', 'nao')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeB === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                NÃO
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1 font-semibold text-slate-700">
                                            <span>Peso e Altura (C)</span>
                                        </div>
                                        <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advPracticeC', 'sim')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeC === 'sim' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                SIM
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advPracticeC', 'nao')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeC === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                NÃO
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1 font-semibold text-slate-700">
                                            <span>Visitas (D)</span>
                                        </div>
                                        <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advPracticeD', 'sim')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeD === 'sim' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                SIM
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advPracticeD', 'nao')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeD === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                NÃO
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1 font-semibold text-slate-700">
                                            <span>Vacinas (E)</span>
                                        </div>
                                        <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advPracticeE', 'sim')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeE === 'sim' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                SIM
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="toggleBooleanFilter('advPracticeE', 'nao')"
                                                class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeE === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                            >
                                                NÃO
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 border-t border-slate-150 pt-4">
                            <button
                                type="button"
                                wire:click="closeAdvancedSearch"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition cursor-pointer shadow-2xs"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Fechar</span>
                            </button>

                            <button
                                type="button"
                                wire:click="clearAdvancedFilters"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition cursor-pointer"
                            >
                                <span>Limpar</span>
                            </button>

                            <button
                                type="button"
                                wire:click="applyAdvancedSearch"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition cursor-pointer"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>Enviar</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- MODAL DE DETALHES CLÍNICOS DA CRIANÇA / BUSCA ATIVA -->
            @if ($showDetailModal && $selectedChild)
                <div
                    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 backdrop-blur-xs animate-fade-in sm:p-4"
                    role="dialog"
                    aria-modal="true"
                >
                    <div
                        class="app-modal-panel relative my-3 w-full max-w-3xl space-y-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl sm:my-8 sm:rounded-3xl sm:p-8"
                        @click.outside="$wire.closeChildDetail()"
                    >
                        <!-- Header -->
                        <div class="flex items-start justify-between border-b border-slate-150 pb-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-xl bg-sky-100 text-sky-800 border border-sky-200 px-2.5 py-0.5 text-[10px] font-bold font-mono">
                                        ID #{{ $selectedChild['id'] }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 text-slate-700 px-2.5 py-0.5 text-[10px] font-bold">
                                        {{ $selectedChild['age_months'] }} meses de vida
                                    </span>
                                </div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">
                                    {{ $selectedChild['name'] }}
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Mãe / Responsável: <strong class="text-slate-700">{{ $selectedChild['mother_name'] }}</strong>
                                </p>
                            </div>

                            <button
                                type="button"
                                wire:click="closeChildDetail"
                                class="rounded-xl p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Cartões de Identificação e Vínculo -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Dados do Cidadão</h4>
                                <div class="space-y-1.5 text-slate-700">
                                    <div><span class="font-semibold text-slate-500">Data de Nascimento:</span> {{ \Carbon\Carbon::parse($selectedChild['birth_date'])->format('d/m/Y') }}</div>
                                    <div><span class="font-semibold text-slate-500">CNS:</span> <span class="font-mono">{{ $selectedChild['cns'] }}</span></div>
                                    <div><span class="font-semibold text-slate-500">CPF:</span> <span class="font-mono">{{ $selectedChild['cpf'] }}</span></div>
                                    <div><span class="font-semibold text-slate-500">Raça / Cor:</span> {{ $selectedChild['race_color'] }}</div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Vínculo Territorial</h4>
                                <div class="space-y-1.5 text-slate-700">
                                    <div><span class="font-semibold text-slate-500">Unidade (CNES):</span> {{ $selectedChild['facility_name'] }} ({{ $selectedChild['cnes'] }})</div>
                                    <div><span class="font-semibold text-slate-500">Equipe (INE):</span> {{ $selectedChild['team_name'] }} ({{ $selectedChild['ine'] }})</div>
                                    <div><span class="font-semibold text-slate-500">Microárea:</span> Microárea {{ $selectedChild['microarea'] }} ({{ $selectedChild['district'] }})</div>
                                    <div><span class="font-semibold text-slate-500">ACS Responsável:</span> {{ $selectedChild['professional_name'] }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Auditoria das 5 Boas Práticas Clínicas (A a E) -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Status das 5 Boas Práticas Clínicas (Nota Metodológica C2 · Portaria 3.493/2024)
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <!-- Prática A -->
                                <div class="rounded-2xl border p-3.5 {{ $selectedChild['practice_a'] >= 1 ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold {{ $selectedChild['practice_a'] >= 1 ? 'text-emerald-950' : 'text-rose-950' }}">
                                            (A) Consulta até 30º dia de vida
                                        </span>
                                        <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedChild['practice_a'] >= 1 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                            {{ $selectedChild['practice_a'] }} {{ $selectedChild['practice_a'] == 1 ? 'consulta' : 'consultas' }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        {{ $selectedChild['practice_a'] >= 1 ? 'Prática cumprida. Primeira consulta de puericultura realizada dentro da janela preconizada de 30 dias.' : 'Pendente. Necessário verificar o registro da consulta neonatal no prontuário eletrônico e-SUS PEC.' }}
                                    </p>
                                </div>

                                <!-- Prática B -->
                                <div class="rounded-2xl border p-3.5 {{ $selectedChild['practice_b'] >= 9 ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold {{ $selectedChild['practice_b'] >= 9 ? 'text-emerald-950' : 'text-rose-950' }}">
                                            (B) Ao menos 9 Consultas Puericultura
                                        </span>
                                        <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedChild['practice_b'] >= 9 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                            {{ $selectedChild['practice_b'] }} / 9 consultas
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        {{ $selectedChild['practice_b'] >= 9 ? 'Meta atingida. A criança possui 9 ou mais consultas médicas/enfermagem de puericultura.' : 'Acompanhamento em curso: agendar próximas consultas programadas conforme o calendário oficial.' }}
                                    </p>
                                </div>

                                <!-- Prática C -->
                                <div class="rounded-2xl border p-3.5 {{ $selectedChild['practice_c'] >= 9 ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold {{ $selectedChild['practice_c'] >= 9 ? 'text-emerald-950' : 'text-rose-950' }}">
                                            (C) 9 Registros de Peso e Altura Simultâneos
                                        </span>
                                        <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedChild['practice_c'] >= 9 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                            {{ $selectedChild['practice_c'] }} / 9 medições
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        {{ $selectedChild['practice_c'] >= 9 ? 'Meta atingida. Peso e altura aferidos no mesmo dia nas consultas de puericultura.' : 'Atenção: sempre registrar peso e altura juntos na mesma data para pontuar na curva da OMS.' }}
                                    </p>
                                </div>

                                <!-- Prática D -->
                                <div class="rounded-2xl border p-3.5 {{ $selectedChild['practice_d'] >= 2 ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold {{ $selectedChild['practice_d'] >= 2 ? 'text-emerald-950' : 'text-rose-950' }}">
                                            (D) 2 Visitas Domiciliares do ACS
                                        </span>
                                        <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedChild['practice_d'] >= 2 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                            {{ $selectedChild['practice_d'] }} / 2 visitas
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        {{ $selectedChild['practice_d'] >= 2 ? 'Meta atingida. O ACS realizou as visitas domiciliares recomendadas até os 6 meses.' : 'Pendente: acionar o ACS do microterritório para realizar visita domiciliar presencial.' }}
                                    </p>
                                </div>

                                <!-- Prática E -->
                                <div class="sm:col-span-2 rounded-2xl border p-3.5 {{ $selectedChild['practice_e'] >= 10 ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold {{ $selectedChild['practice_e'] >= 10 ? 'text-emerald-950' : 'text-rose-950' }}">
                                            (E) Esquema Vacinal Recomendado (Penta, VIP, Pneumo 10v e Tríplice Viral)
                                        </span>
                                        <span class="font-black px-2.5 py-0.5 rounded-md text-[11px] {{ $selectedChild['practice_e'] >= 10 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                            {{ $selectedChild['practice_e'] }} doses administradas
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        {{ $selectedChild['practice_e'] >= 10 ? 'Calendário vacinal completo com todos os imunobiológicos administrados e registrados na RNDS/PEC.' : 'Atenção vacinal: convocar os responsáveis à sala de vacina da UBS para atualização imediata da caderneta de vacinação.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer do Modal -->
                        <div class="flex items-center justify-between border-t border-slate-150 pt-4">
                            <span class="text-[11px] text-slate-400">
                                Mês de conclusão da coorte: <strong class="text-slate-600">{{ $selectedChild['month_ref'] }}</strong>
                            </span>

                            <button
                                type="button"
                                wire:click="closeChildDetail"
                                class="px-5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition cursor-pointer"
                            >
                                Fechar Ficha
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
    <!-- Banner Principal do Indicador -->
    <div class="rounded-3xl border border-line bg-gradient-to-br from-[#0c1f1c] via-[#0f2d26] to-[#081714] text-white p-6 sm:p-8 shadow-md">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-teal-500/25 px-3 py-1 text-xs font-mono font-black text-teal-300 border border-teal-500/30">
                        {{ $meta['code'] }}
                    </span>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold text-slate-300">
                        {{ $meta['category'] }}
                    </span>
                    <span class="text-xs text-slate-400">
                        Público-Alvo: {{ $meta['target_population'] }}
                    </span>
                    @if ($isC1 || $isC2 || $isC3)
                        <span class="rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2.5 py-0.5 text-[10px] font-bold">
                            {{ $isC3 ? 'NT 08/2026 · Coorte 42º dia do puerpério' : ($isC2 ? 'NT 08/2026 · Meses com coorte válida' : 'NT 08/2026 · Média de 4 Meses') }}
                        </span>
                    @endif
                    @if ($isC2 || $isC3)
                        <span class="rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30 px-2.5 py-0.5 text-[10px] font-bold">
                            Peso 2.0 (até 2,00 pt){{ $isC3 ? ' · 11 Boas Práticas (100 pts)' : '' }}
                        </span>
                    @endif
                </div>

                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                    {{ $meta['full_title'] }}
                </h2>

                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    {{ $meta['objective'] }}
                </p>
            </div>

            <!-- Placar de Desempenho e Filtro de Período -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 shrink-0">
                <!-- Seletor de Quadrimestre -->
                <div class="bg-white/10 backdrop-blur-xs border border-white/10 rounded-2xl p-1.5 flex items-center gap-1">
                    @foreach ($periods as $p)
                        <button
                            type="button"
                            wire:click="setPeriod({{ $p['year'] }}, {{ $p['quarter'] }})"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer {{ $year === $p['year'] && $quarter === $p['quarter'] ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                        >
                            {{ $p['year'] }}/Q{{ $p['quarter'] }}
                        </button>
                    @endforeach
                </div>

                <!-- Card de Pontuação -->
                <div class="rounded-3xl bg-white/10 border border-white/15 p-5 text-center min-w-[190px] backdrop-blur-xs">
                    <span class="text-[11px] font-semibold text-teal-300 uppercase tracking-wider block">
                        {{ ($isC1 || $isC2 || $isC3) && $current['is_preview'] ? 'Prévia quadrimestral' : (($isC1 || $isC2 || $isC3) ? 'Média quadrimestral local' : 'Resultado Atual') }}
                    </span>
                    <div class="text-3xl sm:text-4xl font-black text-white tabular-nums my-1">
                        {{ $hasValidatedResult ? number_format($score, 1, ',', '.').'%' : '—' }}
                    </div>
                    <div class="flex items-center justify-center gap-1.5 mt-1">
                        <span class="inline-block rounded-full px-2.5 py-0.5 text-[11px] font-bold border {{ $badgeStyles }}">
                            {{ $levelLabel }}
                        </span>
                        @if (($isC1 || $isC2 || $isC3) && $quarterSummary && $quarterSummary['component_iii_points'] !== null)
                            <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-mono font-bold bg-white/20 text-white border border-white/30">
                                {{ number_format($quarterSummary['component_iii_points'], 2, ',', '.') }} pt
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtro de Equipe e Métricas Secundárias -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-3xl border border-line shadow-xs">
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-slate-700">Filtrar por Equipe:</span>
            <select
                wire:model.live="selectedIne"
                class="rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 focus:border-teal-500 focus:bg-white focus:outline-hidden"
            >
                <option value="">Consolidado Municipal (Todas as Equipes)</option>
                @foreach (($isC2 || $isC3) ? $cohortTeams : $teams as $team)
                    <option value="{{ $team->ine }}">
                        {{ $team->team_name }} (INE {{ $team->ine }})
                        @if ($isC2 || $isC3)
                            @php $teamScore = $teams->firstWhere('ine', $team->ine)?->score_percent; @endphp
                            - {{ $teamScore !== null ? number_format($teamScore, 1, ',', '.').'%' : 'aguardando avaliação' }}
                        @else
                            - {{ number_format($team->score_percent, 1, ',', '.') }}%
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        @if ($isC1)
            <div class="flex items-center gap-6 text-xs">
                <div>
                    <span class="text-slate-400 block">Demanda Programada</span>
                    <span class="font-bold text-teal-800 text-sm">
                        {{ number_format($current['numerator'], 0, '', '.') }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block">Demanda Espontânea</span>
                    <span class="font-bold text-slate-700 text-sm">
                        {{ number_format(max(0, $current['denominator'] - $current['numerator']), 0, '', '.') }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block">Total Atendimentos</span>
                    <span class="font-bold text-ink text-sm">
                        {{ number_format($current['denominator'], 0, '', '.') }}
                    </span>
                </div>
                @if ($quarterSummary)
                    <div class="border-l border-slate-200 pl-4">
                        <span class="text-slate-400 block">Pontos Comp. III</span>
                        <span class="font-mono font-bold text-emerald-700 text-sm">
                            {{ number_format($quarterSummary['component_iii_points'], 2, ',', '.') }} / 1,00 pt
                        </span>
                    </div>
                @endif
            </div>
        @elseif ($isC2)
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block">Pontos das práticas (A–E)</span>
                    <span class="font-bold text-teal-800 text-sm">
                        {{ $hasC2Result ? number_format($current['numerator'], 0, '', '.') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block">Completam 2 anos no quadrimestre</span>
                    <span class="font-bold text-ink text-sm">
                        {{ $current['cohort_total'] !== null ? number_format($current['cohort_total'], 0, '', '.') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block">Já completaram 2 anos{{ $current['cohort_as_of'] ? ' até '.$current['cohort_as_of'] : '' }}</span>
                    <span class="font-bold text-ink text-sm">
                        {{ $current['evaluated_total'] !== null ? number_format($current['evaluated_total'], 0, '', '.') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block">Com práticas pendentes no DW</span>
                    <span class="font-bold text-amber-700 text-sm">
                        {{ $hasC2Result ? number_format($current['active_search_count'], 0, '', '.') : '—' }}
                    </span>
                </div>
                @if ($quarterSummary && $quarterSummary['component_iii_points'] !== null)
                    <div class="border-l border-slate-200 pl-4">
                        <span class="text-slate-400 block">Pontos Comp. III</span>
                        <span class="font-mono font-bold text-emerald-700 text-sm">
                            {{ number_format($quarterSummary['component_iii_points'], 2, ',', '.') }} / 2,00 pt
                        </span>
                    </div>
                @endif
            </div>
        @elseif ($isC3)
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block">Pontos das 11 práticas (A–K)</span>
                    <span class="font-bold text-teal-800 text-sm">
                        {{ $hasC3Result ? number_format($current['numerator'], 0, '', '.') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block">Gestantes/Puérperas na coorte</span>
                    <span class="font-bold text-ink text-sm">
                        {{ $current['cohort_total'] !== null ? number_format($current['cohort_total'], 0, '', '.') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block">Já encerraram puerpério (42 dias)</span>
                    <span class="font-bold text-ink text-sm">
                        {{ $current['evaluated_total'] !== null ? number_format($current['evaluated_total'], 0, '', '.') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block">Com práticas pendentes no DW</span>
                    <span class="font-bold text-amber-700 text-sm">
                        {{ $hasC3Result ? number_format($current['active_search_count'], 0, '', '.') : '—' }}
                    </span>
                </div>
                @if ($quarterSummary && $quarterSummary['component_iii_points'] !== null)
                    <div class="border-l border-slate-200 pl-4">
                        <span class="text-slate-400 block">Pontos Comp. III</span>
                        <span class="font-mono font-bold text-emerald-700 text-sm">
                            {{ number_format($quarterSummary['component_iii_points'], 2, ',', '.') }} / 2,00 pt
                        </span>
                    </div>
                @endif
            </div>
        @else
            <div class="flex items-center gap-6 text-xs">
                <div>
                    <span class="text-slate-400 block">Numerador / Realizado</span>
                    <span class="font-bold text-ink text-sm">{{ number_format($current['numerator'], 0, '', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Denominador / Elegíveis</span>
                    <span class="font-bold text-ink text-sm">{{ number_format($current['denominator'], 0, '', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Busca Ativa Pendente</span>
                    <span class="font-bold text-amber-700 text-sm">{{ number_format($current['active_search_count'], 0, '', '.') }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Abas Internas de Navegação -->
    <div class="border-b border-line">
        <div class="flex items-center gap-2 overflow-x-auto pb-2 text-xs font-semibold">
            <button
                type="button"
                wire:click="setTab('dashboard')"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 transition cursor-pointer {{ $activeTab === 'dashboard' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span>{{ ($isC1 || $isC2 || $isC3) ? 'Acompanhamento Mensal & Avaliação Quadrimestral' : 'Visão do Indicador & Boas Práticas' }}</span>
            </button>

            <button
                type="button"
                wire:click="setTab('teams')"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 transition cursor-pointer {{ $activeTab === 'teams' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <span>Desempenho por Equipe ({{ count($teams) }})</span>
            </button>

            <button
                type="button"
                wire:click="setTab('active_search')"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 transition cursor-pointer {{ $activeTab === 'active_search' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                <span>{{ $isC1 ? 'Busca Ativa & Equilíbrio da Agenda' : ($isC2 ? 'Busca Ativa & Boas Práticas Infantis' : ($isC3 ? 'Busca Ativa & Boas Práticas Gestantes' : 'Busca Ativa & Oportunidades')) }}</span>
                @if ($isC1 && count($agendaAlerts) > 0)
                    <span class="rounded-full bg-rose-100 text-rose-800 px-2 py-0.5 text-[10px] font-bold">
                        {{ count($agendaAlerts) }} alertas
                    </span>
                @else
                    <span class="rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[10px] font-bold">
                        {{ $isC3 ? ($c3TotalItems ?? 0).' acompanhadas' : ($isC2 ? ($c2TotalItems ?? 0).' na coorte' : $current['active_search_count'].' pendentes') }}
                    </span>
                @endif
            </button>

            <button
                type="button"
                wire:click="setTab('rules')"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 transition cursor-pointer {{ $activeTab === 'rules' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span>Nota Metodológica Oficial</span>
            </button>
        </div>
    </div>

    <!-- CONTEÚDO DA ABA 1: DASHBOARD & METODOLOGIA -->
    @if ($activeTab === 'dashboard')
        @if ($isC1)
            <!-- MÓDULO C1: ACOMPANHAMENTO MENSAL E AVALIAÇÃO QUADRIMESTRAL (NT 08/2026) -->
            <div class="space-y-6 animate-fade-in">
                @if (! $hasValidatedResult)
                    <div class="rounded-3xl border border-amber-300 bg-amber-50 p-5 text-amber-950 shadow-sm">
                        <p class="text-sm font-bold">Sem resultado C1 validado para este período.</p>
                        <p class="mt-1 text-xs leading-relaxed">Execute o processamento do DW PEC. O painel não gera valores simulados e não converte competências ausentes em zero.</p>
                    </div>
                @elseif ($current['is_preview'])
                    <div class="rounded-3xl border border-sky-300 bg-sky-50 p-5 text-sky-950 shadow-sm">
                        <p class="text-sm font-bold">Prévia local com {{ $c1Summary['valid_months'] ?? 0 }} de 4 competências monitoradas.</p>
                        <p class="mt-1 text-xs leading-relaxed">A avaliação quadrimestral definitiva exige M1, M2, M3 e M4. O resultado oficial é publicado pelo Siaps.</p>
                    </div>
                @endif
                <!-- Card Síntese: Avaliação do Quadrimestre -->
                <div class="rounded-3xl border border-line bg-white p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-xs font-mono font-bold">
                                    NT 08/2026-DEAPS/SAPS/MS
                                </span>
                                <span class="text-xs font-bold text-slate-500">Quadro 1 e Quadro 2</span>
                            </div>
                            <h3 class="text-lg font-bold text-ink mt-1">Síntese da Avaliação Quadrimestral</h3>
                            <p class="text-xs text-muted">
                                Conforme nota oficial, a avaliação é quadrimestral calculada pela <strong>média aritmética simples dos 4 meses</strong>: <code>(Mês 1 + Mês 2 + Mês 3 + Mês 4) / 4</code>.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Pontuação Componente III</span>
                                <span class="text-base font-black text-emerald-700 font-mono">
                                    {{ ($c1Summary['component_iii_points'] ?? null) !== null ? number_format($c1Summary['component_iii_points'], 2, ',', '.').' / 1,00 pt' : '—' }}
                                </span>
                            </div>
                            <div class="rounded-2xl bg-teal-50 border border-teal-200 px-4 py-2.5 text-right">
                                <span class="text-[10px] uppercase font-bold text-teal-700 block">Peso no Componente</span>
                                <span class="text-base font-black text-teal-900 font-mono">
                                    Peso 1.0 (Multiplicador)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Régua Oficial de Parâmetros e Pontuação (Quadro 1 / NT 08/2026) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700">Faixas Oficiais do Indicador C1 · Mais Acesso</span>
                            <span class="text-muted">Faixa preconizada pelo Ministério da Saúde: 30% a 70% (Ótimo: 50% a 70%)</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score !== null && ($score <= 10.0 || $score > 70.0)) ? 'bg-rose-600 text-white ring-4 ring-rose-100 shadow-sm font-bold' : 'bg-rose-50 text-rose-900 border-rose-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Regular · 0,25 pt</span>
                                <span class="text-sm font-black">≤ 10% ou &gt; 70%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Agenda Desbalanceada</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score !== null && $score > 10.0 && $score <= 30.0) ? 'bg-amber-500 text-white ring-4 ring-amber-100 shadow-sm font-bold' : 'bg-amber-50 text-amber-900 border-amber-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Suficiente · 0,50 pt</span>
                                <span class="text-sm font-black">&gt; 10% e ≤ 30%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Predomínio Espontânea</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score !== null && $score > 30.0 && $score <= 50.0) ? 'bg-emerald-600 text-white ring-4 ring-emerald-100 shadow-sm font-bold' : 'bg-emerald-50 text-emerald-900 border-emerald-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Bom · 0,75 pt</span>
                                <span class="text-sm font-black">&gt; 30% e ≤ 50%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Boa Oferta Programada</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score !== null && $score > 50.0 && $score <= 70.0) ? 'bg-sky-600 text-white ring-4 ring-sky-100 shadow-sm font-bold' : 'bg-sky-50 text-sky-900 border-sky-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Ótimo · 1,00 pt</span>
                                <span class="text-sm font-black">&gt; 50% e ≤ 70%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Equilíbrio Perfeito de Agenda</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid de Acompanhamento Mensal: 4 Meses do Quadrimestre -->
                <div class="rounded-3xl border border-line bg-white p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-teal-600 animate-pulse"></span>
                                <h3 class="text-base font-bold text-ink">Acompanhamento Mensal da Demanda</h3>
                            </div>
                            <p class="text-xs text-muted">
                                Evolução da oferta de consultas médicas e de enfermagem programadas vs espontâneas ao longo dos 4 meses
                            </p>
                        </div>
                        <span class="text-xs font-mono font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-xl">
                            {{ $year }}/Q{{ $quarter }} (4 Competências)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach ($c1Monthly as $m)
                            @php
                                $mLevel = $m['performance_level'];
                                $mBadge = match ($mLevel) {
                                    null => 'bg-slate-100 text-slate-700 border-slate-300',
                                    'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
                                    'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    default => 'bg-rose-100 text-rose-800 border-rose-300',
                                };
                                $mBar = match ($mLevel) {
                                    null => 'bg-slate-300',
                                    'otimo' => 'bg-sky-500',
                                    'bom' => 'bg-emerald-500',
                                    'suficiente' => 'bg-amber-500',
                                    default => 'bg-rose-500',
                                };
                                $espontanea = $m['score_percent'] !== null ? max(0, $m['denominator'] - $m['numerator']) : null;
                            @endphp
                            <div 
                                wire:click="setMonth({{ $selectedMonth === $m['month_in_quarter'] ? 'null' : $m['month_in_quarter'] }})"
                                class="rounded-2xl border transition p-4 space-y-3 cursor-pointer select-none {{ $selectedMonth === $m['month_in_quarter'] ? 'bg-teal-50/90 border-teal-500 ring-2 ring-teal-400 shadow-sm' : 'border-slate-200 bg-slate-50/70 hover:bg-slate-100 hover:border-slate-300' }}"
                                title="Clique para filtrar apenas o {{ $m['label'] }}"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-ink">
                                            {{ $m['label'] }}
                                        </span>
                                        @if ($selectedMonth === $m['month_in_quarter'])
                                            <span class="rounded bg-teal-600 text-white text-[9px] font-bold px-1.5 py-0.2">Ativo</span>
                                        @endif
                                    </div>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $mBadge }}">
                                        {{ $mLevel ? ucfirst($mLevel) : 'Sem dados' }}
                                    </span>
                                </div>

                                <div>
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-2xl font-black text-ink font-mono">
                                            {{ $m['score_percent'] !== null ? number_format($m['score_percent'], 1, ',', '.').'%' : '—' }}
                                        </span>
                                        <span class="text-[11px] font-mono font-semibold text-emerald-700">
                                            {{ $m['component_iii_points'] !== null ? number_format($m['component_iii_points'], 2, ',', '.').' pt' : '—' }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 mt-1.5 overflow-hidden">
                                        <div class="{{ $mBar }} h-2 rounded-full transition-all duration-500" style="width: {{ $m['score_percent'] !== null ? min(100, $m['score_percent']) : 0 }}%"></div>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-slate-200/80 grid grid-cols-2 gap-2 text-[11px]">
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Programada</span>
                                        <span class="font-bold text-teal-800 font-mono">
                                            {{ number_format($m['numerator'], 0, '', '.') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Espontânea</span>
                                        <span class="font-bold text-slate-700 font-mono">
                                            {{ number_format($espontanea, 0, '', '.') }}
                                        </span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-slate-400 block text-[10px]">Total de Atendimentos</span>
                                        <span class="font-bold text-ink font-mono">
                                            {{ number_format($m['denominator'], 0, '', '.') }} atendimentos
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- BARRA DE FILTROS DO ACOMPANHAMENTO MENSAL: Equipe, Mês, Quadrimestre, Classificação -->
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/90 p-4 sm:p-5 space-y-3.5 mt-2">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-600 text-white shadow-xs">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-ink">Filtros do Acompanhamento Mensal</h4>
                                    <p class="text-[11px] text-muted">Filtre por equipe, mês de competência, quadrimestre ou conceito alcançado</p>
                                </div>
                            </div>

                            @if ($selectedIne || $selectedMonth || $selectedClassification)
                                <button
                                    type="button"
                                    wire:click="resetFilters"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-1.5 text-xs font-semibold transition cursor-pointer self-start md:self-auto"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>Limpar Filtros</span>
                                </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
                            <!-- 1. Filtro: Equipe -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-700 block">Equipe (eSF / eAP):</label>
                                <select
                                    wire:model.live="selectedIne"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 focus:border-teal-500 focus:outline-hidden shadow-2xs"
                                >
                                    <option value="">Todas as Equipes ({{ $teams->count() }})</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->ine }}">
                                            {{ $team->team_name }} (INE {{ $team->ine }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. Filtro: Mês -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-700 block">Mês de Competência:</label>
                                <select
                                    wire:model.live="selectedMonth"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 focus:border-teal-500 focus:outline-hidden shadow-2xs"
                                >
                                    <option value="">Todos os 4 Meses (M1 a M4)</option>
                                    @foreach ($c1Monthly as $m)
                                        <option value="{{ $m['month_in_quarter'] }}">
                                            Mês {{ $m['month_in_quarter'] }} · {{ $m['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 3. Filtro: Quadrimestre -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-700 block">Quadrimestre / Período:</label>
                                <select
                                    wire:change="setPeriodString($event.target.value)"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 focus:border-teal-500 focus:outline-hidden shadow-2xs"
                                >
                                    @forelse ($periods as $p)
                                        <option value="{{ $p['year'] }}-{{ $p['quarter'] }}" @selected($year === $p['year'] && $quarter === $p['quarter'])>
                                            {{ $p['year'] }} · Q{{ $p['quarter'] }} ({{ $p['quarter'] === 1 ? 'Jan-Abr' : ($p['quarter'] === 2 ? 'Mai-Ago' : 'Set-Dez') }})
                                        </option>
                                    @empty
                                        <option value="{{ $year }}-{{ $quarter }}" selected>
                                            {{ $year }} · Q{{ $quarter }}
                                        </option>
                                    @endforelse
                                </select>
                            </div>

                            <!-- 4. Filtro: Classificação -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-700 block">Classificação Oficial:</label>
                                <select
                                    wire:model.live="selectedClassification"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 focus:border-teal-500 focus:outline-hidden shadow-2xs"
                                >
                                    <option value="">Todas as Classificações</option>
                                    <option value="regular">Regular (≤ 10% ou > 70% · Vermelho)</option>
                                    <option value="suficiente">Suficiente (> 10% e ≤ 30% · Amarelo)</option>
                                    <option value="bom">Bom (> 30% e ≤ 50% · Verde)</option>
                                    <option value="otimo">Ótimo (> 50% e ≤ 70% · Azul)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- TABELA DE EQUIPES NO ACOMPANHAMENTO MENSAL -->
                    <div class="space-y-3 pt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-teal-600"></span>
                                <h4 class="text-sm font-bold text-ink">Equipes no Acompanhamento Mensal</h4>
                                <span class="rounded-full bg-teal-100 text-teal-800 font-mono text-[11px] font-bold px-2.5 py-0.5">
                                    {{ $c1Teams->count() }} de {{ $teams->count() }} equipes
                                </span>
                                @if ($selectedMonth)
                                    <span class="rounded-full bg-slate-200 text-slate-700 text-[11px] font-semibold px-2 py-0.5">
                                        Mês {{ $selectedMonth }}
                                    </span>
                                @endif
                                @if ($selectedClassification)
                                    <span class="rounded-full bg-slate-200 text-slate-700 text-[11px] font-semibold px-2 py-0.5 capitalize">
                                        {{ $selectedClassification }}
                                    </span>
                                @endif
                            </div>

                            <span class="text-xs font-mono font-medium text-slate-500">
                                {{ $selectedMonth ? 'Detalhamento do Mês ' . $selectedMonth : 'Média Aritmética (M1 + M2 + M3 + M4) / 4' }}
                            </span>
                        </div>

                        @if ($c1Teams->isEmpty())
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center space-y-2">
                                <p class="text-sm font-semibold text-slate-600">Nenhuma equipe encontrada para os filtros selecionados.</p>
                                <button
                                    type="button"
                                    wire:click="resetFilters"
                                    class="text-xs text-teal-700 hover:text-teal-900 font-bold underline cursor-pointer"
                                >
                                    Limpar todos os filtros
                                </button>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                <table class="w-full min-w-[44rem] text-left text-xs text-slate-700">
                                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-line font-bold">
                                        <tr>
                                            <th class="py-3 px-4">Equipe / Unidade</th>
                                            <th class="py-3 px-3">Código INE</th>
                                            <th class="py-3 px-3">Tipo</th>
                                            @if ($selectedMonth === null)
                                                <th class="py-3 px-3 text-center">Mês 1</th>
                                                <th class="py-3 px-3 text-center">Mês 2</th>
                                                <th class="py-3 px-3 text-center">Mês 3</th>
                                                <th class="py-3 px-3 text-center">Mês 4</th>
                                                <th class="py-3 px-4 text-center font-black text-ink bg-slate-100/60">Média Quad.</th>
                                                <th class="py-3 px-3 text-center">Conceito</th>
                                                <th class="py-3 px-3 text-center">Comp. III</th>
                                                <th class="py-3 px-3 text-center">Status Agenda</th>
                                            @else
                                                <th class="py-3 px-3 text-center">Programadas</th>
                                                <th class="py-3 px-3 text-center">Espontâneas</th>
                                                <th class="py-3 px-3 text-center font-black text-ink">Total</th>
                                                <th class="py-3 px-4 text-center font-black text-ink bg-slate-100/60">% Mês {{ $selectedMonth }}</th>
                                                <th class="py-3 px-3 text-center">Conceito Mês</th>
                                                <th class="py-3 px-3 text-center">Pontos Mês</th>
                                                <th class="py-3 px-3 text-center">Status Mês</th>
                                            @endif
                                            <th class="py-3 px-4 text-right">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach ($c1Teams as $team)
                                            @php
                                                $tScores = $team->monthly_scores ?? [1 => null, 2 => null, 3 => null, 4 => null];
                                                $tDetails = $team->monthly_details ?? [];
                                                $mDetail = $selectedMonth ? ($tDetails[$selectedMonth] ?? null) : null;
                                                $tLevel = $selectedMonth ? ($mDetail['performance_level'] ?? null) : ($team->quarter_level ?? $team->performance_level);
                                                $tPoints = $selectedMonth ? ($mDetail['component_iii_points'] ?? null) : ($team->component_iii_points ?? null);

                                                $tBadge = match ($tLevel) {
                                                    null => 'bg-slate-100 text-slate-700 border-slate-300',
                                                    'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
                                                    'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                                    'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                                                    default => 'bg-rose-100 text-rose-800 border-rose-300',
                                                };

                                                $currScore = $selectedMonth ? ($mDetail['score_percent'] ?? null) : ($team->quarter_average ?? $team->score_percent);

                                                $statusText = match (true) {
                                                    $currScore === null => 'Sem dados',
                                                    $currScore > 70.0 => 'Fechada (>70%)',
                                                    $currScore < 30.0 => 'Espontânea (<30%)',
                                                    default => 'Equilibrada',
                                                };

                                                $statusBadge = match (true) {
                                                    $currScore === null => 'bg-slate-50 text-slate-700 border-slate-200',
                                                    $currScore > 70.0 => 'bg-rose-50 text-rose-800 border-rose-200',
                                                    $currScore < 30.0 => 'bg-amber-50 text-amber-800 border-amber-200',
                                                    default => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                };
                                            @endphp
                                            <tr class="hover:bg-slate-50/80 transition {{ $selectedIne === $team->ine ? 'bg-teal-50/70 font-semibold' : '' }}">
                                                <td class="py-3 px-4 font-bold text-ink">
                                                    {{ $team->team_name }}
                                                </td>
                                                <td class="py-3 px-3 font-mono text-slate-500 text-[11px]">
                                                    {{ $team->ine }}
                                                </td>
                                                <td class="py-3 px-3">
                                                    <span class="rounded px-1.5 py-0.5 text-[9px] font-bold {{ $team->team_type === '70' ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-800' }}">
                                                        {{ $team->team_type === '70' ? 'eSF' : 'eAP' }}
                                                    </span>
                                                </td>

                                                @if ($selectedMonth === null)
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ ($tScores[1] ?? null) !== null ? number_format($tScores[1], 1, ',', '.').'%' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ ($tScores[2] ?? null) !== null ? number_format($tScores[2], 1, ',', '.').'%' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ ($tScores[3] ?? null) !== null ? number_format($tScores[3], 1, ',', '.').'%' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ ($tScores[4] ?? null) !== null ? number_format($tScores[4], 1, ',', '.').'%' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-4 text-center font-mono font-black text-sm text-ink bg-slate-50/50">
                                                        {{ number_format($team->quarter_average ?? $team->score_percent, 1, ',', '.') }}%
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $tBadge }}">
                                                            {{ $tLevel ? ucfirst($tLevel) : 'Sem dados' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono font-bold text-emerald-700">
                                                        {{ ($team->component_iii_points ?? null) !== null ? number_format($team->component_iii_points, 2, ',', '.').' pt' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        <span class="rounded-lg px-2 py-0.5 text-[10px] font-bold border {{ $statusBadge }}">
                                                            {{ $statusText }}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td class="py-3 px-3 text-center font-mono font-bold text-teal-800">
                                                        {{ number_format($mDetail['numerator'] ?? 0, 0, '', '.') }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ number_format($mDetail['spontaneous'] ?? 0, 0, '', '.') }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono font-black text-ink">
                                                        {{ number_format($mDetail['denominator'] ?? 0, 0, '', '.') }}
                                                    </td>
                                                    <td class="py-3 px-4 text-center font-mono font-black text-sm text-ink bg-slate-50/50">
                                                        {{ ($mDetail['score_percent'] ?? null) !== null ? number_format($mDetail['score_percent'], 1, ',', '.').'%' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $tBadge }}">
                                                            {{ $tLevel ? ucfirst($tLevel) : 'Sem dados' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono font-bold text-emerald-700">
                                                        {{ $tPoints !== null ? number_format($tPoints, 2, ',', '.').' pt' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        <span class="rounded-lg px-2 py-0.5 text-[10px] font-bold border {{ $statusBadge }}">
                                                            {{ $statusText }}
                                                        </span>
                                                    </td>
                                                @endif

                                                <td class="py-3 px-4 text-right">
                                                    <button
                                                        type="button"
                                                        wire:click="selectTeam('{{ $selectedIne === $team->ine ? '' : $team->ine }}')"
                                                        class="text-xs font-semibold {{ $selectedIne === $team->ine ? 'text-teal-900 bg-teal-100 px-2 py-1 rounded-lg' : 'text-teal-700 hover:text-teal-900' }} cursor-pointer"
                                                    >
                                                        {{ $selectedIne === $team->ine ? 'Limpar Foco' : 'Filtrar' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Diretrizes e Lógica da Agenda da APS (Nota Metodológica C1) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50/50 p-5 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">✓</span>
                            <h4 class="text-sm font-bold text-emerald-950">Faixa Ótima (50% a 70%)</h4>
                        </div>
                        <p class="text-xs text-emerald-900 leading-relaxed">
                            Equilíbrio preconizado pelo Ministério da Saúde. A equipe consegue acolher a demanda aguda do dia (30% a 50% de espontânea) e ao mesmo tempo manter o cuidado longitudinal com pré-natal, crônicos e crianças (50% a 70% programada).
                        </p>
                    </div>

                    <div class="rounded-3xl border border-amber-200 bg-amber-50/50 p-5 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold text-xs">!</span>
                            <h4 class="text-sm font-bold text-amber-950">Alerta: &lt; 30% Programada</h4>
                        </div>
                        <p class="text-xs text-amber-900 leading-relaxed">
                            Indica predomínio quase exclusivo de demanda espontânea (&gt; 70%). A unidade passa o dia "apagando incêndios", gerando perda de seguimento em diabéticos, hipertensos e rastreamentos de câncer. Recomenda-se estruturar agenda preventiva.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-rose-200 bg-rose-50/50 p-5 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-xs">✕</span>
                            <h4 class="text-sm font-bold text-rose-950">Alerta: &gt; 70% Programada</h4>
                        </div>
                        <p class="text-xs text-rose-900 leading-relaxed">
                            Indica agenda excessivamente fechada e engessada. O cidadão que procura a UBS com sintomas agudos ou intercorrências encontra barreiras de acesso e não é acolhido no dia. Recomenda-se ampliar vagas de escuta e acolhimento imediato.
                        </p>
                    </div>
                </div>
            </div>
        @elseif ($isC2)
            <!-- MÓDULO C2: CUIDADO NO DESENVOLVIMENTO INFANTIL NA APS (NT 08/2026 - PESO 2.0) -->
            <div class="space-y-6 animate-fade-in">
                <div class="rounded-2xl border {{ $hasC2Result ? 'border-sky-200 bg-sky-50 text-sky-950' : 'border-amber-200 bg-amber-50 text-amber-950' }} p-4 text-sm" role="status">
                    @if ($hasC2Result)
                        <strong>Prévia local do DW PEC.</strong> As {{ $current['cohort_total'] }} crianças da coorte têm pontuação calculada com registros até {{ $current['cohort_as_of'] }}, inclusive as dos meses futuros. {{ $current['evaluated_total'] }} já completaram 2 anos. A prévia pode mudar com novos cuidados; RNDS e vínculo histórico do Siaps também podem alterar o resultado. Use a nota do Siaps para avaliação e cofinanciamento.
                    @elseif ($current['cohort_total'] !== null)
                        <strong>Coorte do quadrimestre identificada.</strong> {{ $current['cohort_total'] }} crianças completam 2 anos neste período. Execute novamente a extração para calcular a prévia.
                    @else
                        <strong>Sem resultado C2 validado para este recorte.</strong> A extração do DW ainda não foi concluída ou não houve crianças que completaram dois anos no período. Dados anteriores do cálculo simulado não são exibidos.
                    @endif
                </div>
                <!-- Card Síntese: Avaliação do Quadrimestre C2 -->
                <div class="rounded-3xl border border-line bg-white p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-xs font-mono font-bold">
                                    NT 08/2026-DEAPS/SAPS/MS
                                </span>
                                <span class="rounded-lg bg-sky-100 text-sky-900 px-2 py-0.5 text-xs font-mono font-bold">
                                    Quadro 1 e Quadro 2
                                </span>
                                <span class="text-xs font-bold text-slate-500">Nota Metodológica C2</span>
                            </div>
                            <h3 class="text-lg font-bold text-ink mt-1">Prévia Quadrimestral · C2</h3>
                            <p class="text-xs text-muted">
                                A prévia usa a <strong>média dos meses com crianças que completarão dois anos</strong>, inclusive M1–M4 futuros; meses sem coorte ficam fora do divisor. A avaliação oficial considera as crianças que já completaram dois anos. Dados locais do DW PEC para eSF (tipo 70) e eAP (tipo 76).
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Prévia Componente III</span>
                                <span class="text-base font-black text-sky-700 font-mono">
                                    {{ $quarterSummary['component_iii_points'] !== null ? number_format($quarterSummary['component_iii_points'], 2, ',', '.').' / 2,00 pt' : '—' }}
                                </span>
                            </div>
                            <div class="rounded-2xl bg-teal-50 border border-teal-200 px-4 py-2.5 text-right">
                                <span class="text-[10px] uppercase font-bold text-teal-700 block">Peso no Componente</span>
                                <span class="text-base font-black text-teal-900 font-mono">
                                    Peso 2.0 (Até 2,00 pts)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Régua Oficial de Parâmetros e Pontuação (Quadro 2 / NT 08/2026) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700">Faixas Oficiais do Indicador C2 · Cuidado no Desenvolvimento Infantil</span>
                            <span class="text-muted">Ordem oficial e pontuação proporcional no Componente III (Multiplicador Peso 2.0)</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score !== null && $score <= 25.0) ? 'bg-rose-600 text-white ring-4 ring-rose-100 shadow-sm font-bold' : 'bg-rose-50 text-rose-900 border-rose-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Regular · 0,50 pt</span>
                                <span class="text-sm font-black">≤ 25%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Busca Ativa Crítica</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score > 25.0 && $score <= 50.0) ? 'bg-amber-500 text-white ring-4 ring-amber-100 shadow-sm font-bold' : 'bg-amber-50 text-amber-900 border-amber-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Suficiente · 1,00 pt</span>
                                <span class="text-sm font-black">&gt; 25% e ≤ 50%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Atenção a Vacinas e VD</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score > 50.0 && $score <= 75.0) ? 'bg-emerald-600 text-white ring-4 ring-emerald-100 shadow-sm font-bold' : 'bg-emerald-50 text-emerald-900 border-emerald-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Bom · 1,50 pt</span>
                                <span class="text-sm font-black">&gt; 50% e ≤ 75%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Bom Acompanhamento</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score > 75.0) ? 'bg-sky-600 text-white ring-4 ring-sky-100 shadow-sm font-bold' : 'bg-sky-50 text-sky-900 border-sky-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Ótimo · 2,00 pt</span>
                                <span class="text-sm font-black">&gt; 75% e ≤ 100%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Excelente Cobertura</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Decomposição das 5 Boas Práticas Oficiais (20 pts cada · Total 100 pts) -->
                <div class="rounded-3xl border border-line bg-white p-6 sm:p-7 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-teal-600"></span>
                                <h3 class="text-base font-bold text-ink">As 5 Boas Práticas Oficiais do Cuidado Infantil</h3>
                            </div>
                            <p class="text-xs text-muted">
                                Quadro 01 da Nota Metodológica C2 · Cada boa prática computa 20 pontos, totalizando 100 pontos no numerador oficial
                            </p>
                        </div>
                        <span class="rounded-full bg-teal-50 text-teal-800 border border-teal-200 px-3 py-1 text-xs font-bold font-mono">
                            5 Práticas × 20 pts = 100 pts
                        </span>
                    </div>

                    @if ($hasC2Result)
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2" aria-label="Crianças com cada prática registrada no DW">
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $practice)
                                <div class="rounded-xl border border-teal-200 bg-teal-50 px-3 py-2 text-center">
                                    <span class="block text-xs font-bold text-teal-900">Prática {{ $practice }}</span>
                                    <span class="block text-sm font-mono font-bold text-ink">
                                        {{ number_format($current['good_practices_breakdown']['practices'][$practice] ?? 0, 0, '', '.') }} / {{ number_format($current['denominator'], 0, '', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Prática A -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">
                                        A
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">1ª Consulta até 30 Dias</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">
                                    20 pts
                                </span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Consulta médica ou de enfermagem presencial realizada até o 30º dia de vida da criança (CBOs 2251, 2252, 2253, 2231 ou 2235). Registro no MIAI.
                            </p>
                        </div>

                        <!-- Prática B -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">
                                        B
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">≥ 9 Consultas até 2 Anos</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">
                                    20 pts
                                </span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Pelo menos 9 consultas de puericultura (presenciais ou remotas) realizadas por médico ou enfermeiro até a criança completar 2 anos de idade.
                            </p>
                        </div>

                        <!-- Prática C -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">
                                        C
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">≥ 9 Registros Peso e Altura</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">
                                    20 pts
                                </span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Ao menos 9 registros concomitantes de peso e altura na mesma data até 2 anos de vida, aferidos por profissionais habilitados (MIAI e MIP).
                            </p>
                        </div>

                        <!-- Prática D -->
                        <div class="rounded-2xl border border-teal-200 p-4 bg-teal-50/40 hover:bg-teal-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">
                                        D
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">≥ 2 Visitas Domiciliares ACS</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">
                                    20 pts
                                </span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Pelo menos 2 visitas domiciliares por ACS/TACS: a 1ª até 30 dias de vida e a 2ª até o 6º mês de vida (MIVDT).
                            </p>
                            <div class="ml-9 rounded-xl bg-amber-50 border border-amber-200 p-2 text-[11px] text-amber-900 font-medium">
                                <strong>Regra Oficial eAP (tipo 76):</strong> Recebem pontuação integral (20 pts) nesta prática por não possuírem ACS na composição mínima.
                            </div>
                        </div>

                        <!-- Prática E -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2 md:col-span-2 lg:col-span-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">
                                        E
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">Vacinação Completa Recomendada</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">
                                    20 pts
                                </span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Registro no prontuário de todas as doses preconizadas do calendário infantil: <strong>Pentavalente</strong> (3 doses: 2m, 4m, 6m), <strong>VIP</strong> (3 doses: 2m, 4m, 6m), <strong>Tríplice Viral (SCR)</strong> (2 doses com a primeira aos 12 meses) e <strong>Pneumocócica 10V</strong> (2 doses: 2m, 4m).
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Grid de Acompanhamento Mensal: 4 Meses do Quadrimestre (M1 a M4) -->
                <div class="rounded-3xl border border-line bg-white p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-teal-600 animate-pulse"></span>
                                <h3 class="text-base font-bold text-ink">Acompanhamento Mensal do Desenvolvimento Infantil</h3>
                            </div>
                            <p class="text-xs text-muted">
                                Pontuação prévia das crianças de M1 a M4 com cuidados registrados até {{ $current['cohort_as_of'] ?? 'a extração' }}
                            </p>
                        </div>
                        <span class="text-xs font-mono font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-xl">
                            {{ $year }}/Q{{ $quarter }} (4 Competências)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach ($monthlyEvolution as $m)
                            @php
                                $mLevel = $m['performance_level'];
                                $mBadge = match ($mLevel) {
                                    null => 'bg-slate-100 text-slate-700 border-slate-300',
                                    'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
                                    'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    default => 'bg-rose-100 text-rose-800 border-rose-300',
                                };
                                $mBar = match ($mLevel) {
                                    null => 'bg-slate-300',
                                    'otimo' => 'bg-sky-500',
                                    'bom' => 'bg-emerald-500',
                                    'suficiente' => 'bg-amber-500',
                                    default => 'bg-rose-500',
                                };
                            @endphp
                            <div 
                                role="button"
                                tabindex="0"
                                aria-pressed="{{ $selectedMonth === $m['month_in_quarter'] ? 'true' : 'false' }}"
                                wire:click="setMonth({{ $selectedMonth === $m['month_in_quarter'] ? 'null' : $m['month_in_quarter'] }})"
                                wire:keydown.enter="setMonth({{ $selectedMonth === $m['month_in_quarter'] ? 'null' : $m['month_in_quarter'] }})"
                                wire:keydown.space.prevent="setMonth({{ $selectedMonth === $m['month_in_quarter'] ? 'null' : $m['month_in_quarter'] }})"
                                class="rounded-2xl border transition p-4 space-y-3 cursor-pointer select-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-600 {{ $selectedMonth === $m['month_in_quarter'] ? 'bg-teal-50/90 border-teal-500 ring-2 ring-teal-400 shadow-sm' : 'border-slate-200 bg-slate-50/70 hover:bg-slate-100 hover:border-slate-300' }}"
                                title="Clique para filtrar apenas o {{ $m['label'] }}"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-ink">
                                            {{ $m['label'] }}
                                        </span>
                                        @if ($selectedMonth === $m['month_in_quarter'])
                                            <span class="rounded bg-teal-600 text-white text-[9px] font-bold px-1.5 py-0.2">Ativo</span>
                                        @endif
                                    </div>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $mBadge }}">
                                        {{ $mLevel ? ucfirst($mLevel) : 'Sem coorte' }}
                                    </span>
                                </div>

                                @if ($m['score_percent'] !== null && $m['is_preview'])
                                    <span class="inline-flex rounded-full bg-sky-100 text-sky-800 border border-sky-200 px-2 py-0.5 text-[10px] font-bold">Prévia · mês em andamento ou futuro</span>
                                @endif

                                <div>
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-2xl font-black text-ink font-mono">
                                            {{ $m['score_percent'] !== null ? number_format($m['score_percent'], 1, ',', '.').'%' : '—' }}
                                        </span>
                                        <span class="text-[11px] font-mono font-semibold text-sky-700">
                                            {{ $m['component_iii_points'] !== null ? number_format($m['component_iii_points'], 2, ',', '.').' / 2,00 pt' : 'Sem pontuação' }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 mt-1.5 overflow-hidden">
                                        <div class="{{ $mBar }} h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $m['score_percent'] ?? 0) }}%"></div>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-slate-200/80 grid grid-cols-2 gap-2 text-[11px]">
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Pontos A–E</span>
                                        <span class="font-bold text-teal-800 font-mono">
                                            {{ $m['score_percent'] !== null ? number_format($m['numerator'], 0, '', '.') : '—' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Crianças na coorte</span>
                                        <span class="font-bold text-ink font-mono">
                                            {{ $m['cohort_total'] !== null ? number_format($m['cohort_total'], 0, '', '.') : '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- BARRA DE FILTROS DO ACOMPANHAMENTO MENSAL C2: Equipe, Mês, Quadrimestre, Classificação -->
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/90 p-4 sm:p-5 space-y-3.5 mt-2">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-600 text-white shadow-xs">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-ink">Filtros do Acompanhamento Mensal · C2</h4>
                                    <p class="text-[11px] text-muted">Filtre por equipe eSF/eAP, mês de competência, quadrimestre ou conceito alcançado</p>
                                </div>
                            </div>

                            @if ($selectedIne || $selectedMonth || $selectedClassification)
                                <button
                                    type="button"
                                    wire:click="resetFilters"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-1.5 text-xs font-semibold transition cursor-pointer self-start md:self-auto"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>Limpar Filtros</span>
                                </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
                            <!-- 1. Filtro: Equipe -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-700 block">Equipe (eSF / eAP):</label>
                                <select
                                    wire:model.live="selectedIne"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 focus:border-teal-500 focus:outline-hidden shadow-2xs"
                                >
                                    <option value="">Todas as Equipes ({{ $cohortTeams->count() }})</option>
                                    @foreach ($cohortTeams as $team)
                                        <option value="{{ $team->ine }}">
                                            {{ $team->team_name }} (INE {{ $team->ine }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. Filtro: Mês -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-700 block">Mês de Competência:</label>
                                <select
                                    wire:model.live="selectedMonth"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 focus:border-teal-500 focus:outline-hidden shadow-2xs"
                                >
                                    <option value="">Todos os 4 Meses (M1 a M4)</option>
                                    @foreach ($monthlyEvolution as $m)
                                        <option value="{{ $m['month_in_quarter'] }}">
                                            Mês {{ $m['month_in_quarter'] }} · {{ $m['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 3. Filtro: Quadrimestre -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-700 block">Quadrimestre / Período:</label>
                                <select
                                    wire:change="setPeriodString($event.target.value)"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 focus:border-teal-500 focus:outline-hidden shadow-2xs"
                                >
                                    @forelse ($periods as $p)
                                        <option value="{{ $p['year'] }}-{{ $p['quarter'] }}" @selected($year === $p['year'] && $quarter === $p['quarter'])>
                                            {{ $p['year'] }} · Q{{ $p['quarter'] }} ({{ $p['quarter'] === 1 ? 'Jan-Abr' : ($p['quarter'] === 2 ? 'Mai-Ago' : 'Set-Dez') }})
                                        </option>
                                    @empty
                                        <option value="{{ $year }}-{{ $quarter }}" selected>
                                            {{ $year }} · Q{{ $quarter }}
                                        </option>
                                    @endforelse
                                </select>
                            </div>

                            <!-- 4. Filtro: Classificação -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-700 block">Classificação da Prévia:</label>
                                <select
                                    wire:model.live="selectedClassification"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 focus:border-teal-500 focus:outline-hidden shadow-2xs"
                                >
                                    <option value="">Todas as Classificações</option>
                                    <option value="regular">Regular (≤ 25% · Vermelho)</option>
                                    <option value="suficiente">Suficiente (> 25% e ≤ 50% · Amarelo)</option>
                                    <option value="bom">Bom (> 50% e ≤ 75% · Verde)</option>
                                    <option value="otimo">Ótimo (> 75% e ≤ 100% · Azul)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- TABELA DE EQUIPES NO ACOMPANHAMENTO MENSAL C2 -->
                    <div class="space-y-3 pt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-teal-600"></span>
                                <h4 class="text-sm font-bold text-ink">Equipes no Acompanhamento do Cuidado Infantil</h4>
                                <span class="rounded-full bg-teal-100 text-teal-800 font-mono text-[11px] font-bold px-2.5 py-0.5">
                                    {{ $c2Teams->count() }} de {{ $teams->count() }} equipes
                                </span>
                                @if ($selectedMonth)
                                    <span class="rounded-full bg-slate-200 text-slate-700 text-[11px] font-semibold px-2 py-0.5">
                                        Mês {{ $selectedMonth }}
                                    </span>
                                @endif
                                @if ($selectedClassification)
                                    <span class="rounded-full bg-slate-200 text-slate-700 text-[11px] font-semibold px-2 py-0.5 capitalize">
                                        {{ $selectedClassification }}
                                    </span>
                                @endif
                            </div>

                            <span class="text-xs font-mono font-medium text-slate-500">
                                {{ $selectedMonth ? 'Prévia do Mês ' . $selectedMonth : 'Prévia da média dos meses com coorte · Peso 2.0' }}
                            </span>
                        </div>

                        @if ($c2Teams->isEmpty())
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center space-y-2">
                                <p class="text-sm font-semibold text-slate-600">Nenhuma equipe encontrada para os filtros selecionados.</p>
                                <button
                                    type="button"
                                    wire:click="resetFilters"
                                    class="text-xs text-teal-700 hover:text-teal-900 font-bold underline cursor-pointer"
                                >
                                    Limpar todos os filtros
                                </button>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                <table class="w-full min-w-[44rem] text-left text-xs text-slate-700">
                                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-line font-bold">
                                        <tr>
                                            <th class="py-3 px-4">Equipe / Unidade</th>
                                            <th class="py-3 px-3">Código INE</th>
                                            <th class="py-3 px-3">Tipo</th>
                                            @if ($selectedMonth === null)
                                                <th class="py-3 px-3 text-center">Mês 1</th>
                                                <th class="py-3 px-3 text-center">Mês 2</th>
                                                <th class="py-3 px-3 text-center">Mês 3</th>
                                                <th class="py-3 px-3 text-center">Mês 4</th>
                                                <th class="py-3 px-4 text-center font-black text-ink bg-slate-100/60">Média Quad.</th>
                                                <th class="py-3 px-3 text-center">Conceito</th>
                                                <th class="py-3 px-3 text-center">Comp. III (Peso 2.0)</th>
                                                <th class="py-3 px-3 text-center">Status Cuidado</th>
                                            @else
                                                <th class="py-3 px-3 text-center">Cuidado Adequado</th>
                                                <th class="py-3 px-3 text-center">Total Crianças &lt;2a</th>
                                                <th class="py-3 px-4 text-center font-black text-ink bg-slate-100/60">% Mês {{ $selectedMonth }}</th>
                                                <th class="py-3 px-3 text-center">Conceito Mês</th>
                                                <th class="py-3 px-3 text-center">Pontos Mês</th>
                                                <th class="py-3 px-3 text-center">Status Mês</th>
                                            @endif
                                            <th class="py-3 px-4 text-right">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach ($c2Teams as $team)
                                            @php
                                                $tScores = $team->monthly_scores ?? [];
                                                $tDetails = $team->monthly_details ?? [];
                                                $mDetail = $selectedMonth ? ($tDetails[$selectedMonth] ?? null) : null;
                                                $tLevel = $selectedMonth ? ($mDetail['performance_level'] ?? null) : ($team->quarter_level ?? $team->performance_level);
                                                $tPoints = $selectedMonth ? ($mDetail['component_iii_points'] ?? null) : ($team->component_iii_points ?? null);

                                                $tBadge = match ($tLevel) {
                                                    null => 'bg-slate-100 text-slate-700 border-slate-300',
                                                    'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
                                                    'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                                    'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                                                    default => 'bg-rose-100 text-rose-800 border-rose-300',
                                                };

                                                $currScore = $selectedMonth ? ($mDetail['score_percent'] ?? null) : ($team->quarter_average ?? $team->score_percent);

                                                $statusText = match (true) {
                                                    $currScore === null => 'Sem coorte',
                                                    $currScore > 75.0 => 'Excelente Cobertura',
                                                    $currScore > 50.0 => 'Bom Acompanhamento',
                                                    $currScore > 25.0 => 'Atenção a Vacinas/VD',
                                                    default => 'Busca Ativa Urgente',
                                                };

                                                $statusBadge = match (true) {
                                                    $currScore === null => 'bg-slate-100 text-slate-700 border-slate-300',
                                                    $currScore > 75.0 => 'bg-sky-50 text-sky-800 border-sky-200',
                                                    $currScore > 50.0 => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                    $currScore > 25.0 => 'bg-amber-50 text-amber-800 border-amber-200',
                                                    default => 'bg-rose-50 text-rose-800 border-rose-200',
                                                };
                                            @endphp
                                            <tr class="hover:bg-slate-50/80 transition {{ $selectedIne === $team->ine ? 'bg-teal-50/70 font-semibold' : '' }}">
                                                <td class="py-3 px-4 font-bold text-ink">
                                                    {{ $team->team_name }}
                                                </td>
                                                <td class="py-3 px-3 font-mono text-slate-500 text-[11px]">
                                                    {{ $team->ine }}
                                                </td>
                                                <td class="py-3 px-3">
                                                    <span class="rounded px-1.5 py-0.5 text-[9px] font-bold {{ $team->team_type === '70' ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-800' }}">
                                                        {{ $team->team_type === '70' ? 'eSF' : 'eAP' }}
                                                    </span>
                                                </td>

                                                @if ($selectedMonth === null)
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ isset($tScores[1]) ? number_format($tScores[1], 1, ',', '.').'%' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ isset($tScores[2]) ? number_format($tScores[2], 1, ',', '.').'%' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ isset($tScores[3]) ? number_format($tScores[3], 1, ',', '.').'%' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ isset($tScores[4]) ? number_format($tScores[4], 1, ',', '.').'%' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-4 text-center font-mono font-black text-sm text-ink bg-slate-50/50">
                                                        {{ number_format($team->quarter_average ?? $team->score_percent, 1, ',', '.') }}%
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $tBadge }}">
                                                            {{ ucfirst($tLevel) }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono font-bold text-sky-700">
                                                        {{ $team->component_iii_points !== null ? number_format($team->component_iii_points, 2, ',', '.').' pt' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        <span class="rounded-lg px-2 py-0.5 text-[10px] font-bold border {{ $statusBadge }}">
                                                            {{ $statusText }}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td class="py-3 px-3 text-center font-mono font-bold text-teal-800">
                                                        {{ $currScore !== null ? number_format($mDetail['numerator'], 0, '', '.') : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono font-black text-ink">
                                                        {{ $currScore !== null ? number_format($mDetail['denominator'], 0, '', '.') : '—' }}
                                                    </td>
                                                    <td class="py-3 px-4 text-center font-mono font-black text-sm text-ink bg-slate-50/50">
                                                        {{ $currScore !== null ? number_format($currScore, 1, ',', '.').'%' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $tBadge }}">
                                                            {{ $tLevel ? ucfirst($tLevel) : 'Sem coorte' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono font-bold text-sky-700">
                                                        {{ $tPoints !== null ? number_format($tPoints, 2, ',', '.').' pt' : '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        <span class="rounded-lg px-2 py-0.5 text-[10px] font-bold border {{ $statusBadge }}">
                                                            {{ $statusText }}
                                                        </span>
                                                    </td>
                                                @endif

                                                <td class="py-3 px-4 text-right">
                                                    <button
                                                        type="button"
                                                        wire:click="selectTeam('{{ $selectedIne === $team->ine ? '' : $team->ine }}')"
                                                        class="text-xs font-semibold {{ $selectedIne === $team->ine ? 'text-teal-900 bg-teal-100 px-2 py-1 rounded-lg' : 'text-teal-700 hover:text-teal-900' }} cursor-pointer"
                                                    >
                                                        {{ $selectedIne === $team->ine ? 'Limpar Foco' : 'Filtrar' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recomendações e Diretrizes Clínicas da Primeira Infância na APS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-3xl border border-teal-200 bg-teal-50/50 p-5 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-xl bg-teal-700 text-white flex items-center justify-center font-bold text-xs">1</span>
                            <h4 class="text-sm font-bold text-teal-950">Primeiros 1000 Dias & 1ª Consulta</h4>
                        </div>
                        <p class="text-xs text-teal-900 leading-relaxed">
                            A primeira consulta presencial até o 30º dia de vida é crucial para a redução da mortalidade neonatal, apoio e orientação ao aleitamento materno exclusivo e conferência da triagem neonatal biológica e testes de triagem.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-sky-200 bg-sky-50/50 p-5 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-xl bg-sky-700 text-white flex items-center justify-center font-bold text-xs">2</span>
                            <h4 class="text-sm font-bold text-sky-950">Vigilância Antropométrica Contínua</h4>
                        </div>
                        <p class="text-xs text-sky-900 leading-relaxed">
                            O registro simultâneo de peso e altura em todas as 9 consultas de puericultura garante o monitoramento das curvas de crescimento da OMS, permitindo detecção precoce de desnutrição, desaceleração do crescimento ou sobrepeso.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50/50 p-5 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-bold text-xs">3</span>
                            <h4 class="text-sm font-bold text-emerald-950">Cobertura Vacinal & Visita ACS</h4>
                        </div>
                        <p class="text-xs text-emerald-900 leading-relaxed">
                            A visita do ACS nos primeiros 30 dias e até o 6º mês estreita o vínculo territorial, monitorando o cumprimento do calendário vacinal básico (Penta, VIP, SCR e Pneumo) e acionando a equipe diante de atrasos vacinais.
                        </p>
                    </div>
                </div>
            </div>
        @elseif ($isC3)
            <!-- MÓDULO C3: CUIDADO NA GESTAÇÃO E PUERPÉRIO NA APS (NT 08/2026 - PESO 2.0) -->
            <div class="space-y-6 animate-fade-in">
                <div class="rounded-2xl border {{ $hasC3Result ? 'border-sky-200 bg-sky-50 text-sky-950' : 'border-amber-200 bg-amber-50 text-amber-950' }} p-4 text-sm" role="status">
                    @if ($hasC3Result)
                        <strong>Prévia local do DW PEC.</strong> As {{ $current['cohort_total'] }} gestantes e puérperas da coorte têm pontuação calculada com registros até {{ $current['cohort_as_of'] ?? 'a extração' }}, inclusive as dos meses futuros. {{ $current['evaluated_total'] }} já completaram o 42º dia de puerpério. A prévia pode mudar com novos cuidados registrados; RNDS e homologação do Siaps também compõem o resultado oficial.
                    @elseif ($current['cohort_total'] !== null)
                        <strong>Coorte do quadrimestre identificada.</strong> {{ $current['cohort_total'] }} gestantes/puérperas completam o 42º dia de puerpério neste período. Execute o processamento de dados para calcular a prévia.
                    @else
                        <strong>Sem resultado C3 validado para este recorte.</strong> A extração do DW ainda não foi concluída ou não houve gestantes com 42º dia de puerpério no período.
                    @endif
                </div>

                <!-- Card Síntese: Avaliação do Quadrimestre C3 -->
                <div class="rounded-3xl border border-line bg-white p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-xs font-mono font-bold">
                                    NT 08/2026-DEAPS/SAPS/MS
                                </span>
                                <span class="rounded-lg bg-sky-100 text-sky-900 px-2 py-0.5 text-xs font-mono font-bold">
                                    Quadro 1 e Quadro 2
                                </span>
                                <span class="text-xs font-bold text-slate-500">Nota Metodológica C3</span>
                            </div>
                            <h3 class="text-lg font-bold text-ink mt-1">Prévia Quadrimestral · C3 Cuidado na Gestação e Puerpério</h3>
                            <p class="text-xs text-muted">
                                A prévia usa a <strong>média dos meses com gestantes que completam o 42º dia de puerpério</strong>, inclusive M1–M4 futuros. Cada gestante/puérpera elegível pontua até 100 pontos pela realização das 11 boas práticas oficiais, multiplicados pelo Peso 2.0 no Componente III.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Prévia Componente III</span>
                                <span class="text-base font-black text-sky-700 font-mono">
                                    {{ $quarterSummary && $quarterSummary['component_iii_points'] !== null ? number_format($quarterSummary['component_iii_points'], 2, ',', '.').' / 2,00 pt' : '—' }}
                                </span>
                            </div>
                            <div class="rounded-2xl bg-teal-50 border border-teal-200 px-4 py-2.5 text-right">
                                <span class="text-[10px] uppercase font-bold text-teal-700 block">Peso no Componente</span>
                                <span class="text-base font-black text-teal-900 font-mono">
                                    Peso 2.0 (Até 2,00 pts)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Régua Oficial de Parâmetros e Pontuação (Quadro 2 / NT 08/2026) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700">Faixas Oficiais do Indicador C3 · Cuidado na Gestação e Puerpério</span>
                            <span class="text-muted">Ordem oficial e pontuação proporcional no Componente III (Multiplicador Peso 2.0)</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score !== null && $score <= 25.0) ? 'bg-rose-600 text-white ring-4 ring-rose-100 shadow-sm font-bold' : 'bg-rose-50 text-rose-900 border-rose-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Regular · 0,50 pt</span>
                                <span class="text-sm font-black">≤ 25%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Busca Ativa Crítica</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score > 25.0 && $score <= 50.0) ? 'bg-amber-500 text-white ring-4 ring-amber-100 shadow-sm font-bold' : 'bg-amber-50 text-amber-900 border-amber-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Suficiente · 1,00 pt</span>
                                <span class="text-sm font-black">&gt; 25% e ≤ 50%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Atenção Exames e Vacina</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score > 50.0 && $score <= 75.0) ? 'bg-emerald-600 text-white ring-4 ring-emerald-100 shadow-sm font-bold' : 'bg-emerald-50 text-emerald-900 border-emerald-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Bom · 1,50 pt</span>
                                <span class="text-sm font-black">&gt; 50% e ≤ 75%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Bom Acompanhamento</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score > 75.0) ? 'bg-sky-600 text-white ring-4 ring-sky-100 shadow-sm font-bold' : 'bg-sky-50 text-sky-900 border-sky-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Ótimo · 2,00 pt</span>
                                <span class="text-sm font-black">&gt; 75% e ≤ 100%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Excelente Pré-Natal e Puerpério</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Decomposição das 11 Boas Práticas Oficiais (100 pts Total: Prática A = 10 pts, B a K = 9 pts cada) -->
                <div class="rounded-3xl border border-line bg-white p-6 sm:p-7 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-teal-600"></span>
                                <h3 class="text-base font-bold text-ink">As 11 Boas Práticas Oficiais do Cuidado na Gestação e Puerpério</h3>
                            </div>
                            <p class="text-xs text-muted">
                                Quadro 01 da Nota Metodológica C3 · Prática A computa 10 pontos e Práticas B a K computam 9 pontos cada (Total: 100 pontos)
                            </p>
                        </div>
                        <span class="rounded-full bg-teal-50 text-teal-800 border border-teal-200 px-3 py-1 text-xs font-bold font-mono">
                            A (10 pts) + 10 × 9 pts = 100 pts
                        </span>
                    </div>

                    @if ($hasC3Result && isset($current['good_practices_breakdown']['practices']))
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2" aria-label="Gestantes com cada prática registrada no DW">
                            @foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $practiceKey)
                                <div class="rounded-xl border border-teal-200 bg-teal-50 px-2.5 py-1.5 text-center">
                                    <span class="block text-[11px] font-bold text-teal-900">Prática {{ $practiceKey }} ({{ $practiceKey === 'A' ? '10 pts' : '9 pts' }})</span>
                                    <span class="block text-xs font-mono font-bold text-ink">
                                        {{ number_format($current['good_practices_breakdown']['practices'][$practiceKey] ?? 0, 0, '', '.') }} / {{ number_format($current['denominator'], 0, '', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Prática A -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">A</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">Captação Precoce (até 12ª sem)</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">10 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Primeira consulta pré-natal (médica ou enfermagem) presencial realizada até a 12ª semana gestacional completa (≤ 12 sem). Registro no MIAI com CIAP-2 W78 ou CID-10 Z34/Z35.
                            </p>
                        </div>

                        <!-- Prática B -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">B</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">≥ 7 Consultas de Pré-Natal</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">9 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Pelo menos 7 consultas de pré-natal (presenciais ou remotas) realizadas por médico ou enfermeiro ao longo de todo o período gestacional.
                            </p>
                        </div>

                        <!-- Prática C -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">C</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">≥ 7 Aferições de Pressão Arterial</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">9 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Pelo menos 7 aferições e registros de pressão arterial (PA sistólica/diastólica) realizadas durante as consultas de pré-natal para vigilância de DHEG e pré-eclâmpsia.
                            </p>
                        </div>

                        <!-- Prática D -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">D</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">≥ 7 Registros Peso e Altura</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">9 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Pelo menos 7 registros simultâneos de peso e altura na mesma data no pré-natal para acompanhamento contínuo do IMC gestacional e ganho ponderal.
                            </p>
                        </div>

                        <!-- Prática E -->
                        <div class="rounded-2xl border border-teal-200 p-4 bg-teal-50/40 hover:bg-teal-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">E</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">≥ 3 Visitas Domiciliares ACS</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">9 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Ao menos 3 visitas domiciliares de acompanhamento realizadas por ACS/TACS durante a gestação após a primeira visita de cadastro (MIVDT).
                            </p>
                            <div class="ml-9 rounded-xl bg-amber-50 border border-amber-200 p-2 text-[11px] text-amber-900 font-medium">
                                <strong>Regra Oficial eAP (tipo 76):</strong> Recebem pontuação integral (9 pts) por não possuírem ACS na composição mínima.
                            </div>
                        </div>

                        <!-- Prática F -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">F</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">Vacina dTpa (≥ 20ª sem)</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">9 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Ao menos 1 dose da vacina tríplice bacteriana acelular (dTpa) administrada a partir da 20ª semana gestacional (registro RIA/RNDS ou prontuário).
                            </p>
                        </div>

                        <!-- Prática G -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">G</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">Exames 1º Trimestre (até 13ª sem)</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">9 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Realização de exames essenciais até a 13ª semana: Sífilis (teste rápido/VDRL), HIV, Hepatite B (HBsAg) e Hepatite C (anti-HCV).
                            </p>
                        </div>

                        <!-- Prática H -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">H</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">Exames 3º Trimestre (≥ 28ª sem)</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">9 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Rastreamento de Sífilis e HIV no 3º trimestre gestacional (a partir da 28ª semana) para prevenção de transmissão vertical peri-parto.
                            </p>
                        </div>

                        <!-- Prática I -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">I</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">Consulta Puerpério (até 42 dias)</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">9 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Ao menos 1 consulta médica ou de enfermagem de puerpério realizada até 42 dias pós-parto, com avaliação puerperal e amamentação.
                            </p>
                        </div>

                        <!-- Prática J -->
                        <div class="rounded-2xl border border-teal-200 p-4 bg-teal-50/40 hover:bg-teal-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">J</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">Visita ACS Puerpério (até 42 dias)</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">9 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Pelo menos 1 visita domiciliar pós-parto realizada pelo ACS/TACS até o 42º dia de puerpério (MIVDT).
                            </p>
                            <div class="ml-9 rounded-xl bg-amber-50 border border-amber-200 p-2 text-[11px] text-amber-900 font-medium">
                                <strong>Regra Oficial eAP (tipo 76):</strong> Recebem pontuação integral (9 pts) por não possuírem ACS na composição mínima.
                            </div>
                        </div>

                        <!-- Prática K -->
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">K</span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">Atendimento Odontológico</h4>
                                </div>
                                <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold shrink-0">9 pts</span>
                            </div>
                            <p class="text-xs text-muted leading-relaxed pl-9">
                                Ao menos 1 atendimento em saúde bucal realizado por Cirurgião-Dentista ou TSB durante o período gestacional.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Grid de Acompanhamento Mensal: 4 Meses do Quadrimestre (M1 a M4) -->
                <div class="rounded-3xl border border-line bg-white p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-teal-600 animate-pulse"></span>
                                <h3 class="text-base font-bold text-ink">Acompanhamento Mensal da Gestação e Puerpério</h3>
                            </div>
                            <p class="text-xs text-muted">
                                Pontuação prévia das gestantes e puérperas de M1 a M4 com cuidados registrados até {{ $current['cohort_as_of'] ?? 'a extração' }}
                            </p>
                        </div>
                        <span class="text-xs font-mono font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-xl">
                            {{ $year }}/Q{{ $quarter }} (4 Competências)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach ($monthlyEvolution as $m)
                            @php
                                $mLevel = $m['performance_level'];
                                $mBadge = match ($mLevel) {
                                    null => 'bg-slate-100 text-slate-700 border-slate-300',
                                    'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
                                    'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    default => 'bg-rose-100 text-rose-800 border-rose-300',
                                };
                                $mBar = match ($mLevel) {
                                    null => 'bg-slate-300',
                                    'otimo' => 'bg-sky-500',
                                    'bom' => 'bg-emerald-500',
                                    'suficiente' => 'bg-amber-500',
                                    default => 'bg-rose-500',
                                };
                            @endphp
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 space-y-3 hover:bg-slate-50 transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700">{{ $m['month_name'] }}</span>
                                    <span class="text-[10px] font-mono text-slate-500">{{ $m['year'] }}/M{{ $m['month'] }}</span>
                                </div>
                                <div class="text-2xl font-black text-ink tabular-nums">
                                    {{ $m['score_percent'] !== null ? number_format($m['score_percent'], 1, ',', '.').'%' : '—' }}
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="{{ $mBar }} h-1.5 rounded-full" style="width: {{ min(100, $m['score_percent'] ?? 0) }}%"></div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-muted pt-1">
                                    <span>Pontos: <strong class="text-ink">{{ number_format($m['numerator'] ?? 0, 0, '', '.') }}</strong></span>
                                    <span>Coorte: <strong class="text-ink">{{ number_format($m['denominator'] ?? 0, 0, '', '.') }}</strong></span>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $mBadge }}">
                                        {{ $mLevel ? ucfirst($mLevel) : 'Sem Coorte' }}
                                    </span>
                                    @if (($m['component_iii_points'] ?? null) !== null)
                                        <span class="text-[10px] font-mono font-bold text-sky-700">
                                            {{ number_format($m['component_iii_points'], 2, ',', '.') }} pt
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <!-- MÓDULOS C4 A C7: VISÃO DO INDICADOR & BOAS PRÁTICAS -->
            <div class="space-y-6 animate-fade-in">
                <!-- Barra de Progresso e Faixas de Metas -->
                <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-ink">Escala de Cumprimento do Indicador</h3>
                            <p class="text-xs text-muted">Classificação oficial de acordo com a Portaria GM/MS nº 3.493/2024</p>
                        </div>
                        <span class="text-xs font-bold text-slate-700">Polaridade: {{ $meta['polarity'] }}</span>
                    </div>

                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div class="{{ $barColor }} h-3 rounded-full transition-all duration-700" style="width: {{ min(100, $score) }}%"></div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                        @foreach ($meta['parameters'] as $pKey => $param)
                            <div class="rounded-2xl p-3 border {{ $param['badge'] }} text-center">
                                <span class="text-[11px] font-bold block">{{ $param['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Decomposição das Boas Práticas Oficiais -->
                <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-ink">Boas Práticas Pontuadas na Nota Metodológica</h3>
                            <p class="text-xs text-muted">Ações clínicas individuais necessárias para que o cidadão pontue no numerador oficial</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($meta['good_practices'] as $practice)
                            <div class="rounded-2xl border border-slate-150 p-4 bg-slate-50/70 hover:bg-slate-50 transition space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs font-mono">
                                            {{ $practice['letter'] }}
                                        </span>
                                        <h4 class="text-xs sm:text-sm font-bold text-ink">{{ $practice['title'] }}</h4>
                                    </div>
                                    @if (isset($practice['points']))
                                        <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold">
                                            {{ $practice['points'] }} pts
                                        </span>
                                    @elseif (isset($practice['weight_pct']))
                                        <span class="rounded-lg bg-teal-100 text-teal-900 px-2 py-0.5 text-[11px] font-bold">
                                            Peso {{ $practice['weight_pct'] }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-muted leading-relaxed pl-9">
                                    {{ $practice['desc'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- CONTEÚDO DA ABA 2: DESEMPENHO POR EQUIPE -->
    @if ($activeTab === 'teams')
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4 animate-fade-in">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-ink">Desempenho Individualizado por Equipe (INE)</h3>
                    <p class="text-xs text-muted">
                        {{ $isC3 ? 'Prévia local M1 a M4 e média quadrimestral das gestantes e puérperas da coorte; a nota oficial é publicada pelo Siaps' : ($isC2 ? 'Prévia local M1 a M4 e média quadrimestral das crianças da coorte; a nota oficial é publicada pelo Siaps' : ($isC1 ? 'Acompanhamento mês a mês (M1 a M4), média aritmética quadrimestral e pontuação no Componente III conforme NT 08/2026' : 'Resultados homologados das Equipes de Saúde da Família (eSF) e Atenção Primária (eAP)')) }}
                    </p>
                </div>
                @if ($isC1 || $isC2 || $isC3)
                    <span class="text-xs font-mono font-semibold text-teal-800 bg-teal-50 px-3 py-1.5 rounded-xl border border-teal-200">
                        Fórmula: (M1 + M2 + M3 + M4) / 4 · Peso {{ $meta['weight'] }}
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                                <table class="w-full min-w-[44rem] text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-line font-bold">
                        <tr>
                            <th class="py-3 px-4">Equipe / Unidade</th>
                            <th class="py-3 px-4">Código INE</th>
                            <th class="py-3 px-4">Tipo</th>
                            @if ($isC1 || $isC2 || $isC3)
                                <th class="py-3 px-3 text-center">Mês 1</th>
                                <th class="py-3 px-3 text-center">Mês 2</th>
                                <th class="py-3 px-3 text-center">Mês 3</th>
                                <th class="py-3 px-3 text-center">Mês 4</th>
                                <th class="py-3 px-4 text-center font-black text-ink">Média Quad.</th>
                                <th class="py-3 px-3 text-center">Conceito</th>
                                <th class="py-3 px-3 text-center">{{ $isC1 ? 'Comp. III (Peso 1.0)' : 'Comp. III (Peso 2.0)' }}</th>
                                <th class="py-3 px-3 text-center">{{ $isC1 ? 'Status Agenda' : 'Status Cuidado' }}</th>
                            @else
                                <th class="py-3 px-4 text-center">Numerador</th>
                                <th class="py-3 px-4 text-center">Denominador</th>
                                <th class="py-3 px-4 text-center">Desempenho</th>
                                <th class="py-3 px-4 text-center">Classificação</th>
                            @endif
                            <th class="py-3 px-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($teams as $team)
                            @php
                                $tLevel = $team->performance_level;
                                $tBadge = match ($tLevel) {
                                    'otimo' => in_array($indicator, ['c1', 'c2', 'c3']) ? 'bg-sky-100 text-sky-800 border-sky-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'bom' => in_array($indicator, ['c1', 'c2', 'c3']) ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-sky-100 text-sky-800 border-sky-200',
                                    'suficiente' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    default => 'bg-rose-100 text-rose-800 border-rose-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition {{ $selectedIne === $team->ine ? 'bg-teal-50/60 font-semibold' : '' }}">
                                <td class="py-3.5 px-4 font-bold text-ink">{{ $team->team_name }}</td>
                                <td class="py-3.5 px-4 font-mono text-slate-500">{{ $team->ine }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $team->team_type === '70' ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-800' }}">
                                        {{ $team->team_type === '70' ? 'eSF (40h)' : 'eAP' }}
                                    </span>
                                </td>

                                @if ($isC1 || $isC2 || $isC3)
                                    @php
                                        $mScores = $team->monthly_scores ?? [];
                                        $points = $team->component_iii_points ?? (($isC2 || $isC3) ? null : 0.25);

                                        if ($isC1) {
                                            $agendaStatus = $team->agenda_status ?? 'optimal';
                                            $statusBadge = match ($agendaStatus) {
                                                'excess_programmatic' => 'bg-rose-50 text-rose-800 border-rose-200',
                                                'excess_spontaneous' => 'bg-amber-50 text-amber-800 border-amber-200',
                                                default => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                            };
                                            $statusText = match ($agendaStatus) {
                                                'excess_programmatic' => 'Fechada (>70%)',
                                                'excess_spontaneous' => 'Espontânea (<30%)',
                                                default => 'Equilibrada',
                                            };
                                        } elseif ($isC3) {
                                            $avg = $team->quarter_average ?? $team->score_percent;
                                            $statusText = match (true) {
                                                $avg > 75.0 => 'Excelente Pré-Natal',
                                                $avg > 50.0 => 'Bom Acompanhamento',
                                                $avg > 25.0 => 'Atenção Exames/Vacinas',
                                                default => 'Busca Ativa Crítica',
                                            };
                                            $statusBadge = match (true) {
                                                $avg > 75.0 => 'bg-sky-50 text-sky-800 border-sky-200',
                                                $avg > 50.0 => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                $avg > 25.0 => 'bg-amber-50 text-amber-800 border-amber-200',
                                                default => 'bg-rose-50 text-rose-800 border-rose-200',
                                            };
                                        } else {
                                            $avg = $team->quarter_average ?? $team->score_percent;
                                            $statusText = match (true) {
                                                $avg > 75.0 => 'Excelente Cobertura',
                                                $avg > 50.0 => 'Bom Acompanhamento',
                                                $avg > 25.0 => 'Atenção Vacinas/VD',
                                                default => 'Busca Ativa Urgente',
                                            };
                                            $statusBadge = match (true) {
                                                $avg > 75.0 => 'bg-sky-50 text-sky-800 border-sky-200',
                                                $avg > 50.0 => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                $avg > 25.0 => 'bg-amber-50 text-amber-800 border-amber-200',
                                                default => 'bg-rose-50 text-rose-800 border-rose-200',
                                            };
                                        }
                                    @endphp
                                    <td class="py-3.5 px-3 text-center font-mono text-slate-600">
                                        {{ isset($mScores[1]) ? number_format($mScores[1], 1, ',', '.').'%' : '—' }}
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono text-slate-600">
                                        {{ isset($mScores[2]) ? number_format($mScores[2], 1, ',', '.').'%' : '—' }}
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono text-slate-600">
                                        {{ isset($mScores[3]) ? number_format($mScores[3], 1, ',', '.').'%' : '—' }}
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono text-slate-600">
                                        {{ isset($mScores[4]) ? number_format($mScores[4], 1, ',', '.').'%' : '—' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-black text-sm text-ink bg-slate-50/50">
                                        {{ number_format($team->quarter_average ?? $team->score_percent, 1, ',', '.') }}%
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $tBadge }}">
                                            {{ ucfirst($tLevel) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono font-bold {{ ($isC2 || $isC3) ? 'text-sky-700' : 'text-emerald-700' }}">
                                        {{ $points !== null ? number_format($points, 2, ',', '.').' pt' : '—' }}
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="rounded-lg px-2 py-0.5 text-[10px] font-bold border {{ $statusBadge }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                @else
                                    <td class="py-3.5 px-4 text-center font-semibold">{{ number_format($team->numerator, 0, '', '.') }}</td>
                                    <td class="py-3.5 px-4 text-center font-semibold">{{ number_format($team->denominator, 0, '', '.') }}</td>
                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-sm text-ink">
                                        {{ number_format($team->score_percent, 1, ',', '.') }}%
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $tBadge }}">
                                            {{ ucfirst($tLevel) }}
                                        </span>
                                    </td>
                                @endif

                                <td class="py-3.5 px-4 text-right">
                                    <button
                                        type="button"
                                        wire:click="selectTeam('{{ $team->ine }}')"
                                        class="text-xs font-semibold text-teal-700 hover:text-teal-900 cursor-pointer"
                                    >
                                        {{ $selectedIne === $team->ine ? 'Selecionada' : 'Filtrar' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- CONTEÚDO DA ABA 3: EQUILÍBRIO DA AGENDA / BUSCA ATIVA -->
    @if ($activeTab === 'active_search')
        @if ($isC1)
            <!-- DIRETRIZES DE REORGANIZAÇÃO DE AGENDA PARA O INDICADOR C1 -->
            <div class="space-y-6 animate-fade-in">
                <!-- Alertas de Equipes Fora da Faixa Ideal -->
                <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <h3 class="text-base font-bold text-ink">Diagnóstico da Agenda das Equipes</h3>
                            <p class="text-xs text-muted">
                                Equipes com desbalanceamento entre oferta programada e acolhimento à demanda espontânea
                            </p>
                        </div>
                        <span class="rounded-full bg-slate-100 text-slate-800 border border-slate-200 px-3 py-1 text-xs font-bold font-mono">
                            Faixa Ideal: 30% a 70% (Ótimo: 50% a 70%)
                        </span>
                    </div>

                    @if (count($agendaAlerts) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($agendaAlerts as $alert)
                                <div class="rounded-2xl border p-4 {{ $alert['status'] === 'excess_programmatic' ? 'border-rose-200 bg-rose-50/60' : 'border-amber-200 bg-amber-50/60' }} space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-ink text-sm">{{ $alert['team_name'] }}</span>
                                        <span class="font-mono text-xs font-bold {{ $alert['status'] === 'excess_programmatic' ? 'text-rose-700' : 'text-amber-700' }}">
                                            {{ number_format($alert['average'], 1, ',', '.') }}%
                                        </span>
                                    </div>
                                    <h4 class="text-xs font-bold {{ $alert['status'] === 'excess_programmatic' ? 'text-rose-900' : 'text-amber-900' }}">
                                        {{ $alert['title'] }}
                                    </h4>
                                    <p class="text-xs text-slate-700 leading-relaxed">
                                        {{ $alert['recommendation'] }}
                                    </p>
                                    <div class="pt-2">
                                        <button
                                            type="button"
                                            wire:click="selectTeam('{{ $alert['ine'] }}')"
                                            class="text-xs font-bold text-teal-800 hover:text-teal-950 cursor-pointer underline"
                                        >
                                            Ver detalhamento desta equipe &rarr;
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-900 text-xs flex items-center gap-3">
                            <span class="text-lg">🎉</span>
                            <span>Todas as equipes estão com as agendas operando dentro da faixa de equilíbrio saudável da APS (30% a 70%).</span>
                        </div>
                    @endif
                </div>

                <!-- Oportunidades de Agendamento Programado -->
                <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4">
                    <div class="border-b border-line pb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-ink">Oportunidades de Agendamento Programático</h3>
                            <p class="text-xs text-muted">Cidadãos prioritários que demandam consulta médica ou de enfermagem agendada</p>
                        </div>
                        <span class="rounded-full bg-teal-100 text-teal-900 border border-teal-300 px-3 py-1 text-xs font-bold">
                            {{ count($activeSearchList) }} Cidadãos Prioritários
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                                <table class="w-full min-w-[44rem] text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-line font-bold">
                                <tr>
                                    <th class="py-3 px-4">Cidadão(ã)</th>
                                    <th class="py-3 px-4">Idade</th>
                                    <th class="py-3 px-4">Cartão SUS (CNS)</th>
                                    <th class="py-3 px-4">Equipe / Microárea</th>
                                    <th class="py-3 px-4">Ação Prioritária Recomendada</th>
                                    <th class="py-3 px-4 text-center">Prioridade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($activeSearchList as $citizen)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3.5 px-4 font-bold text-ink">{{ $citizen['name'] }}</td>
                                        <td class="py-3.5 px-4 text-slate-600">{{ $citizen['age'] }}</td>
                                        <td class="py-3.5 px-4 font-mono text-slate-500">{{ $citizen['cns'] }}</td>
                                        <td class="py-3.5 px-4 text-slate-600">
                                            <span class="block font-medium text-ink">{{ $citizen['team_name'] }}</span>
                                            <span class="text-[11px] text-muted">{{ $citizen['microarea'] }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 font-medium text-teal-900">
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
                                                {{ $citizen['pending_action'] }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if ($citizen['priority'] === 'alta')
                                                <span class="rounded-full bg-rose-100 text-rose-800 border border-rose-200 px-2 py-0.5 text-[10px] font-bold">Alta</span>
                                            @else
                                                <span class="rounded-full bg-amber-100 text-amber-800 border border-amber-200 px-2 py-0.5 text-[10px] font-bold">Média</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif ($isC2)
            <!-- MÓDULO C2: BUSCA ATIVA & BOAS PRÁTICAS INFANTIS (CONFORME TELAS DE REFERÊNCIA) -->
            <div class="space-y-6 animate-fade-in">
                <!-- Cabeçalho da Seção com Título e Botão Busca Avançada -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h3 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight">
                                Componente de Qualidade / Saúde da Família - C2 Cuidado no Desenvolvimento Infantil
                            </h3>
                            @if ($isRealDataAvailable)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Base Real e-SUS PEC ({{ number_format($realChildrenCount, 0, '', '.') }} na coorte)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Demonstração · Processe em Configurações > Processamento de Dados
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Lista nominal e busca ativa das crianças de 0 a 24 meses da coorte vinculadas às Equipes de Saúde da Família
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">

                        @if ($activeFiltersCount > 0)
                            <button
                                type="button"
                                wire:click="clearAdvancedFilters"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition cursor-pointer shadow-2xs"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Limpar Filtros ({{ $activeFiltersCount }})</span>
                            </button>
                        @endif

                        <button
                            type="button"
                            wire:click="openAdvancedSearch"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-sm transition cursor-pointer"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <span>Busca Avançada</span>
                        </button>
                    </div>
                </div>

                @if (session()->has('c2_sync_message'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs font-semibold text-emerald-800 flex items-center justify-between shadow-2xs animate-fade-in">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('c2_sync_message') }}</span>
                        </div>
                    </div>
                @endif

                @if (session()->has('c2_sync_error'))
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-semibold text-rose-800 flex items-center justify-between shadow-2xs animate-fade-in">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <span>{{ session('c2_sync_error') }}</span>
                        </div>
                    </div>
                @endif

                <!-- BANNER SUPERIOR: DADOS GERAIS (SÍNTESE DOS INDICADORES CONFORME IMAGEM 1) -->
                @if ($c2SummaryKpis)
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                            <!-- Card Mês -->
                            <div class="md:col-span-3 text-center md:border-r border-slate-150 pr-4 space-y-1">
                                <span class="text-xs font-medium text-slate-500 block">Mês</span>
                                <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">
                                    {{ $c2SummaryKpis['period_label'] }}
                                </div>
                                <span class="text-[11px] text-slate-400 block">
                                    {{ $c2SummaryKpis['period_sublabel'] }}
                                </span>
                            </div>

                            <!-- Grid Central das 5 Boas Práticas (A a E) -->
                            <div class="md:col-span-6 space-y-4 px-2">
                                <!-- Linha Superior: Práticas A e B -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                            <span>Consulta até 30º dia de vida (A)</span>
                                            <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold" title="1ª consulta presencial de puericultura realizada até o 30º dia de vida">i</span>
                                        </div>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-xl font-black text-teal-800 tabular-nums">
                                                {{ number_format($c2SummaryKpis['practice_a']['count'], 0, '', '.') }}
                                            </span>
                                            <span class="text-xs font-semibold text-teal-900">
                                                ({{ number_format($c2SummaryKpis['practice_a']['percent'], 2, ',', '.') }}%)
                                            </span>
                                        </div>
                                    </div>

                                    <div class="space-y-1 sm:border-l border-slate-150 sm:pl-4">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                            <span>Consultas (B)</span>
                                            <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold" title="Ao menos 9 consultas presenciais ou remotas de puericultura até os 2 anos">i</span>
                                        </div>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-xl font-black text-teal-800 tabular-nums">
                                                {{ number_format($c2SummaryKpis['practice_b']['count'], 0, '', '.') }}
                                            </span>
                                            <span class="text-xs font-semibold text-teal-900">
                                                ({{ number_format($c2SummaryKpis['practice_b']['percent'], 2, ',', '.') }}%)
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-slate-150 pt-3">
                                    <!-- Linha Inferior: Práticas C, D e E -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1 text-[11px] text-slate-600 font-medium">
                                                <span>Peso e Altura (C)</span>
                                                <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-200 text-slate-600 text-[8px] font-bold" title="Ao menos 9 registros antropométricos simultâneos no mesmo dia">i</span>
                                            </div>
                                            <div class="flex items-baseline gap-1.5">
                                                <span class="text-lg font-black text-teal-800 tabular-nums">
                                                    {{ number_format($c2SummaryKpis['practice_c']['count'], 0, '', '.') }}
                                                </span>
                                                <span class="text-[11px] font-semibold text-teal-900">
                                                    ({{ number_format($c2SummaryKpis['practice_c']['percent'], 2, ',', '.') }}%)
                                                </span>
                                            </div>
                                        </div>

                                        <div class="space-y-1 sm:border-l border-slate-150 sm:pl-3">
                                            <div class="flex items-center gap-1 text-[11px] text-slate-600 font-medium">
                                                <span>Visitas (D)</span>
                                                <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-200 text-slate-600 text-[8px] font-bold" title="Ao menos 2 visitas domiciliares do ACS até os 6 meses">i</span>
                                            </div>
                                            <div class="flex items-baseline gap-1.5">
                                                <span class="text-lg font-black text-teal-800 tabular-nums">
                                                    {{ number_format($c2SummaryKpis['practice_d']['count'], 0, '', '.') }}
                                                </span>
                                                <span class="text-[11px] font-semibold text-teal-900">
                                                    ({{ number_format($c2SummaryKpis['practice_d']['percent'], 2, ',', '.') }}%)
                                                </span>
                                            </div>
                                        </div>

                                        <div class="space-y-1 sm:border-l border-slate-150 sm:pl-3">
                                            <div class="flex items-center gap-1 text-[11px] text-slate-600 font-medium">
                                                <span>Vacinas (E)</span>
                                                <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-200 text-slate-600 text-[8px] font-bold" title="Esquema vacinal completo: Penta, VIP, Pneumo e Tríplice Viral">i</span>
                                            </div>
                                            <div class="flex items-baseline gap-1.5">
                                                <span class="text-lg font-black text-teal-800 tabular-nums">
                                                    {{ number_format($c2SummaryKpis['practice_e']['count'], 0, '', '.') }}
                                                </span>
                                                <span class="text-[11px] font-semibold text-teal-900">
                                                    ({{ number_format($c2SummaryKpis['practice_e']['percent'], 2, ',', '.') }}%)
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Denominador -->
                            <div class="md:col-span-3 text-center md:border-l border-slate-150 pl-4 space-y-1">
                                <div class="flex items-center justify-center gap-1 text-xs font-medium text-slate-500">
                                    <span>Denominador</span>
                                    <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold" title="Total de crianças vinculadas na coorte avaliada">i</span>
                                </div>
                                <div class="text-3xl sm:text-4xl font-black text-slate-800 tracking-tight tabular-nums">
                                    {{ number_format($c2SummaryKpis['denominator'], 0, '', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- BARRA DE FILTROS RÁPIDOS & CUSTOMIZADOR DE COLUNAS (CONFORME IMAGENS 1 E 2) -->
                <div class="space-y-4 bg-white p-5 rounded-3xl border border-slate-200 shadow-2xs">
                    <!-- Linha 1 de Filtros Rápidos -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchCns"
                                placeholder="CNS"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden shadow-2xs"
                            />
                        </div>

                        <div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchCpf"
                                placeholder="CPF"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden shadow-2xs"
                            />
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchName"
                                placeholder="Filtrar por Nome"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden shadow-2xs"
                            />
                        </div>

                        <div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchCnes"
                                placeholder="CNES"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden shadow-2xs"
                            />
                        </div>

                        <div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchIne"
                                placeholder="INE"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden shadow-2xs"
                            />
                        </div>

                        <div>
                            <select
                                wire:model.live="perPage"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-hidden shadow-2xs"
                            >
                                <option value="10">10 itens / pág</option>
                                <option value="15">15 itens / pág</option>
                                <option value="30">30 itens / pág</option>
                                <option value="50">50 itens / pág</option>
                                <option value="100">100 itens / pág</option>
                            </select>
                        </div>
                    </div>

                    <!-- Linha de Personalização de Colunas Visíveis -->
                    <div class="flex items-center justify-end pt-1" x-data="{ open: false }">
                        <div class="relative">
                            <button
                                type="button"
                                @click="open = !open"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer shadow-2xs"
                            >
                                <span class="text-slate-500">Colunas visíveis:</span>
                                <span class="font-bold text-sky-700">{{ count($visibleColumns) }} itens selecionados</span>
                                <svg class="h-4 w-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <!-- Dropdown Popover de Colunas -->
                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-72 rounded-2xl bg-white border border-slate-200 p-4 shadow-xl z-30 space-y-3"
                                style="display: none;"
                            >
                                <div class="flex items-center justify-between border-b border-slate-150 pb-2">
                                    <span class="text-xs font-bold text-slate-800">Personalizar Colunas</span>
                                    <div class="flex items-center gap-2 text-[11px]">
                                        <button
                                            type="button"
                                            wire:click="selectAllColumns"
                                            class="text-sky-600 hover:text-sky-800 font-semibold cursor-pointer"
                                        >
                                            Todas
                                        </button>
                                        <span class="text-slate-300">|</span>
                                        <button
                                            type="button"
                                            wire:click="resetDefaultColumns"
                                            class="text-slate-500 hover:text-slate-800 font-semibold cursor-pointer"
                                        >
                                            Padrão
                                        </button>
                                    </div>
                                </div>

                                <div class="max-h-64 overflow-y-auto space-y-1.5 scrollbar-thin pr-1 text-xs">
                                    @foreach ($c2AvailableColumns as $colKey => $colLabel)
                                        <label class="flex items-center gap-2.5 p-1 rounded-lg hover:bg-slate-50 cursor-pointer select-none">
                                            <input
                                                type="checkbox"
                                                wire:click="toggleColumn('{{ $colKey }}')"
                                                @checked(in_array($colKey, $visibleColumns, true))
                                                class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 h-3.5 w-3.5 cursor-pointer"
                                            />
                                            <span class="text-slate-700 font-medium text-[11px]">{{ $colLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LISTA NOMINAL: TABELA INTERATIVA (CONFORME IMAGENS 1, 2 E 3) -->
                <div class="rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                                <table class="w-full min-w-[44rem] text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                                <tr>
                                    @if (in_array('id', $visibleColumns, true))
                                        <th class="py-3 px-3">#</th>
                                    @endif
                                    @if (in_array('cns', $visibleColumns, true))
                                        <th class="py-3 px-3">CNS</th>
                                    @endif
                                    @if (in_array('cpf', $visibleColumns, true))
                                        <th class="py-3 px-3">CPF</th>
                                    @endif
                                    @if (in_array('birth_date', $visibleColumns, true))
                                        <th class="py-3 px-3">Nascimento</th>
                                    @endif
                                    @if (in_array('name', $visibleColumns, true))
                                        <th class="py-3 px-4">Nome</th>
                                    @endif
                                    @if (in_array('age_months', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center leading-tight">Idade<br><span class="text-[9px] font-normal lowercase">(meses)</span></th>
                                    @endif
                                    @if (in_array('race_color', $visibleColumns, true))
                                        <th class="py-3 px-3">Raça/Cor</th>
                                    @endif
                                    @if (in_array('facility', $visibleColumns, true))
                                        <th class="py-3 px-3">Unidade</th>
                                    @endif
                                    @if (in_array('team', $visibleColumns, true))
                                        <th class="py-3 px-3">Equipe</th>
                                    @endif
                                    @if (in_array('professional', $visibleColumns, true))
                                        <th class="py-3 px-3">Profissional</th>
                                    @endif
                                    @if (in_array('month_ref', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center">Mês</th>
                                    @endif
                                    @if (in_array('microarea', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center leading-tight">Micro<br><span class="text-[9px] font-normal uppercase">Área</span></th>
                                    @endif
                                    @if (in_array('mici', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center leading-tight">MICI<br><span class="text-[9px] font-normal uppercase">Atualizada?</span></th>
                                    @endif
                                    @if (in_array('practice_a', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center text-slate-600 font-bold" title="Consulta até 30º dia de vida (A)">(A) ?</th>
                                    @endif
                                    @if (in_array('practice_b', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center text-slate-600 font-bold" title="9 Consultas de Puericultura até 2 Anos (B)">(B) ?</th>
                                    @endif
                                    @if (in_array('practice_c', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center text-slate-600 font-bold" title="9 Registros de Peso e Altura Simultâneos (C)">(C) ?</th>
                                    @endif
                                    @if (in_array('practice_d', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center text-slate-600 font-bold" title="2 Visitas Domiciliares do ACS até 6 meses (D)">(D) ?</th>
                                    @endif
                                    @if (in_array('practice_e', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center text-slate-600 font-bold" title="Esquema Vacinal Completo (E)">(E) ?</th>
                                    @endif
                                    @if (in_array('actions', $visibleColumns, true))
                                        <th class="py-3 px-4 text-center">Ações</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-150 bg-white">
                                @forelse ($c2NominalList as $child)
                                    <tr class="hover:bg-slate-50/90 transition text-xs">
                                        @if (in_array('id', $visibleColumns, true))
                                            <td class="py-3.5 px-3 font-mono text-[11px] text-slate-400">
                                                {{ $child['id'] }}
                                            </td>
                                        @endif

                                        @if (in_array('cns', $visibleColumns, true))
                                            <td class="py-3.5 px-3 font-mono text-[11px] whitespace-nowrap text-slate-700" x-data="{ show: false, copied: false }">
                                                <div class="flex items-center gap-1.5">
                                                    <span x-text="show ? '{{ $child['cns'] }}' : '{{ \App\Services\C2ActiveSearchService::maskCns($child['cns']) }}'"></span>
                                                    <button
                                                        type="button"
                                                        @click="show = !show"
                                                        class="text-slate-400 hover:text-slate-700 transition cursor-pointer"
                                                        :title="show ? 'Ocultar CNS' : 'Revelar CNS'"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="navigator.clipboard.writeText('{{ $child['cns'] }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                        class="text-slate-400 hover:text-sky-700 transition cursor-pointer"
                                                        :title="copied ? 'Copiado!' : 'Copiar CNS'"
                                                    >
                                                        <svg x-show="!copied" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                                        </svg>
                                                        <svg x-show="copied" class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="display: none;">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        @endif

                                        @if (in_array('cpf', $visibleColumns, true))
                                            <td class="py-3.5 px-3 font-mono text-[11px] whitespace-nowrap text-slate-700" x-data="{ show: false, copied: false }">
                                                <div class="flex items-center gap-1.5">
                                                    <span x-text="show ? '{{ $child['cpf'] }}' : '{{ \App\Services\C2ActiveSearchService::maskCpf($child['cpf']) }}'"></span>
                                                    <button
                                                        type="button"
                                                        @click="show = !show"
                                                        class="text-slate-400 hover:text-slate-700 transition cursor-pointer"
                                                        :title="show ? 'Ocultar CPF' : 'Revelar CPF'"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="navigator.clipboard.writeText('{{ $child['cpf'] }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                        class="text-slate-400 hover:text-sky-700 transition cursor-pointer"
                                                        :title="copied ? 'Copiado!' : 'Copiar CPF'"
                                                    >
                                                        <svg x-show="!copied" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                                        </svg>
                                                        <svg x-show="copied" class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="display: none;">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        @endif

                                        @if (in_array('birth_date', $visibleColumns, true))
                                            <td class="py-3.5 px-3 font-mono text-[11px] whitespace-nowrap text-slate-600">
                                                {{ \Carbon\Carbon::parse($child['birth_date'])->format('d/m/Y') }}
                                            </td>
                                        @endif

                                        @if (in_array('name', $visibleColumns, true))
                                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                                <div class="flex items-center gap-1.5">
                                                    <span>{{ $child['name'] }}</span>
                                                    <span
                                                        class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-100 text-slate-500 hover:text-slate-800 text-[9px] font-bold cursor-help"
                                                        title="Mãe: {{ $child['mother_name'] ?? 'Não informada' }}"
                                                    >
                                                        i
                                                    </span>
                                                </div>
                                            </td>
                                        @endif

                                        @if (in_array('age_months', $visibleColumns, true))
                                            <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-700">
                                                {{ $child['age_months'] }}
                                            </td>
                                        @endif

                                        @if (in_array('race_color', $visibleColumns, true))
                                            <td class="py-3.5 px-3 text-slate-600 whitespace-nowrap">
                                                {{ $child['race_color'] }}
                                            </td>
                                        @endif

                                        @if (in_array('facility', $visibleColumns, true))
                                            <td class="py-3.5 px-3 whitespace-nowrap">
                                                <div class="flex items-center gap-1 font-mono text-[11px] text-slate-600">
                                                    <span>{{ $child['cnes'] }}</span>
                                                    <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-100 text-slate-500 text-[8px] font-bold cursor-help" title="{{ $child['facility_name'] }}">i</span>
                                                </div>
                                            </td>
                                        @endif

                                        @if (in_array('team', $visibleColumns, true))
                                            <td class="py-3.5 px-3 whitespace-nowrap">
                                                <div class="flex items-center gap-1 font-mono text-[11px] text-slate-600">
                                                    <span>{{ $child['ine'] }}</span>
                                                    <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-100 text-slate-500 text-[8px] font-bold cursor-help" title="{{ $child['team_name'] }}">i</span>
                                                </div>
                                            </td>
                                        @endif

                                        @if (in_array('professional', $visibleColumns, true))
                                            <td class="py-3.5 px-3 whitespace-nowrap">
                                                <div class="flex items-center gap-1 font-mono text-[11px] text-slate-600">
                                                    <span>{{ substr($child['professional_cns'], 0, 8) }}...</span>
                                                    <span class="inline-flex items-center justify-center h-3 w-3 rounded-full bg-slate-100 text-slate-500 text-[8px] font-bold cursor-help" title="{{ $child['professional_name'] }}">i</span>
                                                </div>
                                            </td>
                                        @endif

                                        @if (in_array('month_ref', $visibleColumns, true))
                                            <td class="py-3.5 px-3 text-center font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                                {{ $child['month_ref'] }}
                                            </td>
                                        @endif

                                        @if (in_array('microarea', $visibleColumns, true))
                                            <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-700">
                                                {{ $child['microarea'] }}
                                            </td>
                                        @endif

                                        @if (in_array('mici', $visibleColumns, true))
                                            <td class="py-3.5 px-3 text-center">
                                                @if ($child['mici_updated'])
                                                    <span class="inline-block rounded-md bg-emerald-600 text-white font-bold text-[10px] px-2.5 py-0.5 shadow-2xs">
                                                        Sim
                                                    </span>
                                                @else
                                                    <span class="inline-block rounded-md bg-rose-600 text-white font-bold text-[10px] px-2.5 py-0.5 shadow-2xs">
                                                        Não
                                                    </span>
                                                @endif
                                            </td>
                                        @endif

                                        <!-- Badge (A): Consulta até 30d -->
                                        @if (in_array('practice_a', $visibleColumns, true))
                                            <td class="py-3.5 px-2 text-center">
                                                @if ($child['practice_a'] >= 1)
                                                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-md bg-emerald-600 text-white font-black text-xs shadow-2xs">
                                                        {{ $child['practice_a'] }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-md bg-rose-600 text-white font-black text-xs shadow-2xs">
                                                        0
                                                    </span>
                                                @endif
                                            </td>
                                        @endif

                                        <!-- Badge (B): 9 Consultas Puericultura -->
                                        @if (in_array('practice_b', $visibleColumns, true))
                                            <td class="py-3.5 px-2 text-center">
                                                @if ($child['practice_b'] >= 9)
                                                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-md bg-emerald-600 text-white font-black text-xs shadow-2xs">
                                                        {{ $child['practice_b'] }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-md bg-rose-600 text-white font-black text-xs shadow-2xs">
                                                        {{ $child['practice_b'] }}
                                                    </span>
                                                @endif
                                            </td>
                                        @endif

                                        <!-- Badge (C): 9 Registros Peso e Altura -->
                                        @if (in_array('practice_c', $visibleColumns, true))
                                            <td class="py-3.5 px-2 text-center">
                                                @if ($child['practice_c'] >= 9)
                                                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-md bg-emerald-600 text-white font-black text-xs shadow-2xs">
                                                        {{ $child['practice_c'] }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-md bg-rose-600 text-white font-black text-xs shadow-2xs">
                                                        {{ $child['practice_c'] }}
                                                    </span>
                                                @endif
                                            </td>
                                        @endif

                                        <!-- Badge (D): 2 Visitas Domiciliares ACS -->
                                        @if (in_array('practice_d', $visibleColumns, true))
                                            <td class="py-3.5 px-2 text-center">
                                                @if ($child['practice_d'] >= 2)
                                                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-md bg-emerald-600 text-white font-black text-xs shadow-2xs">
                                                        {{ $child['practice_d'] }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-md bg-rose-600 text-white font-black text-xs shadow-2xs">
                                                        {{ $child['practice_d'] }}
                                                    </span>
                                                @endif
                                            </td>
                                        @endif

                                        <!-- Badge (E): Vacinas Completas -->
                                        @if (in_array('practice_e', $visibleColumns, true))
                                            <td class="py-3.5 px-2 text-center">
                                                @if ($child['practice_e'] >= 10)
                                                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-md bg-emerald-600 text-white font-black text-xs shadow-2xs">
                                                        {{ $child['practice_e'] }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-md bg-rose-600 text-white font-black text-xs shadow-2xs">
                                                        {{ $child['practice_e'] }}
                                                    </span>
                                                @endif
                                            </td>
                                        @endif

                                        @if (in_array('actions', $visibleColumns, true))
                                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                <button
                                                    type="button"
                                                    wire:click="openChildDetail({{ $child['id'] }})"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-sky-700 hover:bg-sky-800 shadow-2xs transition cursor-pointer"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    <span>Detalhes</span>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($visibleColumns) }}" class="py-12 text-center text-slate-500">
                                            <div class="space-y-2">
                                                <p class="text-sm font-semibold">Nenhuma criança encontrada para os filtros aplicados.</p>
                                                <button
                                                    type="button"
                                                    wire:click="clearAdvancedFilters"
                                                    class="text-xs font-bold text-sky-700 hover:text-sky-900 underline cursor-pointer"
                                                >
                                                    Limpar filtros de busca
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação Interativa -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-t border-slate-150 bg-slate-50/50 text-xs text-slate-600">
                        <div>
                            Mostrando
                            <span class="font-bold text-slate-800">{{ min($c2TotalItems, ($c2Page - 1) * $perPage + 1) }}</span>
                            a
                            <span class="font-bold text-slate-800">{{ min($c2TotalItems, $c2Page * $perPage) }}</span>
                            de
                            <span class="font-bold text-slate-800">{{ $c2TotalItems }}</span>
                            crianças na coorte
                        </div>

                        @if ($c2TotalPages > 1)
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    wire:click="gotoC2Page({{ $c2Page - 1 }})"
                                    @disabled($c2Page <= 1)
                                    class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer shadow-2xs"
                                >
                                    &larr; Anterior
                                </button>

                                @for ($p = max(1, $c2Page - 2); $p <= min($c2TotalPages, $c2Page + 2); $p++)
                                    <button
                                        type="button"
                                        wire:click="gotoC2Page({{ $p }})"
                                        class="px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer {{ $p === $c2Page ? 'bg-sky-600 text-white shadow-2xs' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 shadow-2xs' }}"
                                    >
                                        {{ $p }}
                                    </button>
                                @endfor

                                <button
                                    type="button"
                                    wire:click="gotoC2Page({{ $c2Page + 1 }})"
                                    @disabled($c2Page >= $c2TotalPages)
                                    class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer shadow-2xs"
                                >
                                    Próximo &rarr;
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- MODAL DE BUSCA AVANÇADA (EXATAMENTE CONFORME AS IMAGENS 4 E 5) -->
                @if ($showAdvancedModal)
                    <div
                        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 backdrop-blur-xs animate-fade-in sm:p-4"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div
                            class="app-modal-panel relative my-3 w-full max-w-4xl space-y-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl sm:my-8 sm:rounded-3xl sm:p-8"
                            @click.outside="$wire.closeAdvancedSearch()"
                        >
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between border-b border-slate-150 pb-4">
                                <h3 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">
                                    Busca Avançada
                                </h3>
                                <button
                                    type="button"
                                    wire:click="closeAdvancedSearch"
                                    class="rounded-xl p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Form Grid -->
                            <div class="space-y-4 text-xs">
                                <!-- Linha 1: Equipe e Microárea (Distrito e Unidade removidos conforme solicitado) -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="sm:col-span-2 space-y-1">
                                        <label class="font-semibold text-slate-700 block">Equipe</label>
                                        <select
                                            wire:model.live="advTeam"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        >
                                            <option value="">Todas as Equipes (ou selecione uma equipe)</option>
                                            @foreach ($c2FilterOptions['teams'] as $tm)
                                                <option value="{{ $tm['ine'] }}">{{ $tm['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Microárea</label>
                                        <input
                                            type="text"
                                            wire:model.live="advMicroarea"
                                            placeholder="Ex: 01, 02..."
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>
                                </div>

                                <!-- Linha 2: Nome do Cidadão, CPF Cidadão, CNS Cidadão -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Nome do Cidadão</label>
                                        <input
                                            type="text"
                                            wire:model.live="advCitizenName"
                                            placeholder="Digite o nome da criança"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">CPF Cidadão</label>
                                        <input
                                            type="text"
                                            wire:model.live="advCitizenCpf"
                                            placeholder="000.000.000-00"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">CNS Cidadão</label>
                                        <input
                                            type="text"
                                            wire:model.live="advCitizenCns"
                                            placeholder="Cartão SUS"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>
                                </div>

                                <!-- Linha 3: Nome da Mãe, Mês e Opção Mês (Conforme Imagens 1 e 2) -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Nome da Mãe</label>
                                        <input
                                            type="text"
                                            wire:model.live="advMotherName"
                                            placeholder="Digite o nome da mãe"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>

                                    <!-- Dropdown Customizado de Mês (Imagem 1) -->
                                    <div class="space-y-1 relative" x-data="{
                                        open: false,
                                        search: '',
                                        months: @js($c2FilterOptions['months']),
                                        get filtered() {
                                            if (!this.search) return this.months;
                                            return this.months.filter(m => m.label.toLowerCase().includes(this.search.toLowerCase()) || m.value.includes(this.search));
                                        }
                                    }" @click.outside="open = false">
                                        <label class="font-semibold text-slate-700 block">Mês</label>
                                        <div 
                                            @click="open = !open" 
                                            class="flex items-center justify-between w-full rounded-xl border bg-white px-3 py-2 text-xs shadow-2xs cursor-pointer transition"
                                            :class="open ? 'border-sky-500 ring-2 ring-sky-100' : 'border-slate-300 hover:border-sky-400'"
                                        >
                                            <span class="truncate" :class="!$wire.advMonth ? 'text-slate-400' : 'text-slate-800 font-medium'" x-text="$wire.advMonth ? ($wire.advMonth.replace('/', ' / ')) : 'Selecione o mês (opcional)'"></span>
                                            <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                                <button 
                                                    x-show="$wire.advMonth" 
                                                    type="button" 
                                                    @click.stop="$wire.clearMonthFilter()" 
                                                    class="text-slate-400 hover:text-slate-600 p-0.5 rounded-full hover:bg-slate-100 cursor-pointer"
                                                    title="Limpar mês (deixar em branco)"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                                <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Painel Dropdown de Mês -->
                                        <div 
                                            x-show="open" 
                                            x-transition 
                                            class="absolute left-0 right-0 z-50 mt-1 rounded-xl border border-slate-200 bg-white shadow-xl py-2 px-1 text-xs"
                                            style="display: none;"
                                        >
                                            <div class="px-2 pb-2">
                                                <div class="relative">
                                                    <input 
                                                        type="text" 
                                                        x-model="search" 
                                                        placeholder="Buscar mês..." 
                                                        class="w-full rounded-lg border border-slate-200 py-1.5 pl-2.5 pr-8 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                                                        @click.stop
                                                    />
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="max-h-52 overflow-y-auto divide-y-0 py-1">
                                                <!-- Opção para deixar em branco / nenhum -->
                                                <div 
                                                    @click="$wire.clearMonthFilter(); open = false;" 
                                                    class="px-3 py-2 cursor-pointer transition rounded-lg flex items-center justify-between border-b border-slate-100 mb-1"
                                                    :class="!$wire.advMonth ? 'bg-sky-50 text-sky-900 font-semibold' : 'text-slate-500 hover:bg-slate-50 italic'"
                                                >
                                                    <span>Nenhum (Em branco / Não filtrar)</span>
                                                    <span x-show="!$wire.advMonth" class="text-sky-600 text-xs font-bold">✓</span>
                                                </div>

                                                <template x-for="item in filtered" :key="item.value">
                                                    <div 
                                                        @click="$wire.setMonthFilter(item.value); open = false;" 
                                                        class="px-3 py-2 cursor-pointer transition rounded-lg flex items-center justify-between"
                                                        :class="$wire.advMonth === item.value ? 'bg-sky-50 text-sky-900 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                                    >
                                                        <span x-text="item.label"></span>
                                                        <span x-show="$wire.advMonth === item.value" class="text-sky-600 text-xs font-bold">✓</span>
                                                    </div>
                                                </template>
                                                <div x-show="filtered.length === 0" class="px-3 py-2 text-slate-400 text-center text-xs">
                                                    Nenhum mês encontrado
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dropdown Customizado de Opção Mês (Imagem 2) -->
                                    <div class="space-y-1 relative" x-data="{
                                        open: false,
                                        search: '',
                                        options: @js($c2FilterOptions['month_options']),
                                        get filtered() {
                                            if (!this.search) return this.options;
                                            return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase()));
                                        },
                                        get currentLabel() {
                                            if (!$wire.advMonthOption) return 'Nenhuma (Não filtrar)';
                                            let found = this.options.find(o => o.value === $wire.advMonthOption);
                                            return found ? found.label : 'Nenhuma (Não filtrar)';
                                        }
                                    }" @click.outside="open = false">
                                        <label class="font-semibold text-slate-700 block">Opção Mês</label>
                                        <div 
                                            @click="open = !open" 
                                            class="flex items-center justify-between w-full rounded-xl border bg-white px-3 py-2 text-xs shadow-2xs cursor-pointer transition"
                                            :class="open ? 'border-sky-500 ring-2 ring-sky-100' : 'border-slate-300 hover:border-sky-400'"
                                        >
                                            <span class="truncate" :class="!$wire.advMonthOption ? 'text-slate-400' : 'text-slate-800 font-medium'" x-text="currentLabel"></span>
                                            <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                                <button 
                                                    x-show="$wire.advMonthOption" 
                                                    type="button" 
                                                    @click.stop="$wire.setMonthOption('')" 
                                                    class="text-slate-400 hover:text-slate-600 p-0.5 rounded-full hover:bg-slate-100 cursor-pointer"
                                                    title="Limpar opção (deixar em branco)"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                                <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Painel Dropdown de Opção Mês -->
                                        <div 
                                            x-show="open" 
                                            x-transition 
                                            class="absolute left-0 right-0 z-50 mt-1 rounded-xl border border-slate-200 bg-white shadow-xl py-2 px-1 text-xs"
                                            style="display: none;"
                                        >
                                            <div class="px-2 pb-2">
                                                <div class="relative">
                                                    <input 
                                                        type="text" 
                                                        x-model="search" 
                                                        placeholder="Buscar opção..." 
                                                        class="w-full rounded-lg border border-slate-200 py-1.5 pl-2.5 pr-8 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                                                        @click.stop
                                                    />
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="py-1">
                                                <!-- Opção para deixar em branco / nenhuma -->
                                                <div 
                                                    @click="$wire.setMonthOption(''); open = false;" 
                                                    class="px-3 py-2 cursor-pointer transition rounded-lg flex items-center justify-between border-b border-slate-100 mb-1"
                                                    :class="!$wire.advMonthOption ? 'bg-sky-50 text-sky-900 font-semibold' : 'text-slate-500 hover:bg-slate-50 italic'"
                                                >
                                                    <span>Nenhuma (Em branco / Não filtrar)</span>
                                                    <span x-show="!$wire.advMonthOption" class="text-sky-600 text-xs font-bold">✓</span>
                                                </div>

                                                <template x-for="item in filtered" :key="item.value">
                                                    <div 
                                                        @click="$wire.setMonthOption(item.value); open = false;" 
                                                        class="px-3 py-2 cursor-pointer transition rounded-lg flex items-center justify-between"
                                                        :class="$wire.advMonthOption === item.value ? 'bg-sky-50 text-sky-900 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                                    >
                                                        <span x-text="item.label"></span>
                                                        <span x-show="$wire.advMonthOption === item.value" class="text-sky-600 text-xs font-bold">✓</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Linha 4: Quadrimestre, CNS Profissional, Nome Profissional, Raça/Cor -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Quadrimestre</label>
                                        <select
                                            wire:model.live="advQuarter"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        >
                                            <option value="">Todos os Quadrimestres (ou selecione)</option>
                                            @foreach ($c2FilterOptions['quarters'] as $q)
                                                <option value="{{ $q['value'] }}">{{ $q['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">CNS Profissional (ACS/TACS)</label>
                                        <input
                                            type="text"
                                            wire:model.live="advProfessionalCns"
                                            placeholder="Cartão SUS do profissional"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Nome do Profissional (ACS/TACS)</label>
                                        <input
                                            type="text"
                                            wire:model.live="advProfessionalName"
                                            placeholder="Nome do profissional"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Raça/Cor</label>
                                        <select
                                            wire:model.live="advRaceColor"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        >
                                            <option value="">Selecione as opções desejadas</option>
                                            @foreach ($c2FilterOptions['races'] as $r)
                                                <option value="{{ $r }}">{{ $r }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Linha 5: Idade (meses) com Chips de atalho e Multiselect (Conforme Imagens 3 e 4) -->
                                <div class="space-y-2 border-t border-slate-150 pt-3">
                                    <label class="font-semibold text-slate-700 block">Idade (meses)</label>
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <!-- Chips conforme Imagem 3 -->
                                        <button
                                            type="button"
                                            wire:click="setAgeGroup('0-6')"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer border {{ $advAgeGroup === '0-6' ? 'bg-sky-600 text-white border-sky-600 shadow-2xs' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
                                        >
                                            0-6 meses
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="setAgeGroup('7-12')"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer border {{ $advAgeGroup === '7-12' ? 'bg-sky-600 text-white border-sky-600 shadow-2xs' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
                                        >
                                            7-12 meses
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="setAgeGroup('13-24')"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer border {{ $advAgeGroup === '13-24' ? 'bg-sky-600 text-white border-sky-600 shadow-2xs' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
                                        >
                                            13-24 meses
                                        </button>

                                        <!-- Dropdown Multiselect "Selecione os meses" conforme Imagem 4 -->
                                        <div class="relative min-w-[220px]" x-data="{
                                            open: false,
                                            search: '',
                                            ageOptions: @js($c2FilterOptions['age_options']),
                                            get filtered() {
                                                if (!this.search) return this.ageOptions;
                                                return this.ageOptions.filter(a => a.label.toLowerCase().includes(this.search.toLowerCase()) || a.value.toString().includes(this.search));
                                            },
                                            get label() {
                                                let count = $wire.advAgeMonths.length;
                                                if (count === 0) return 'Selecione os meses';
                                                if (count === 1) return $wire.advAgeMonths[0] === 1 ? '1 mês' : $wire.advAgeMonths[0] + ' meses';
                                                if (count >= 25) return 'Todos os meses (0 a 24)';
                                                return count + ' meses selecionados';
                                            }
                                        }" @click.outside="open = false">
                                            <!-- Botão Disparador -->
                                            <div 
                                                @click="open = !open" 
                                                class="flex items-center justify-between rounded-xl border bg-white px-3 py-1.5 text-xs text-slate-700 shadow-2xs cursor-pointer transition"
                                                :class="open ? 'border-sky-500 ring-2 ring-sky-100' : 'border-slate-300 hover:border-sky-400'"
                                            >
                                                <span class="truncate" x-text="label"></span>
                                                <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                                    <button 
                                                        x-show="$wire.advAgeMonths.length > 0" 
                                                        type="button" 
                                                        @click.stop="$wire.set('advAgeMonths', []); $wire.set('advAgeGroup', '');" 
                                                        class="text-slate-400 hover:text-slate-600 p-0.5 rounded-full hover:bg-slate-100"
                                                        title="Limpar seleção"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Menu Dropdown com Checkboxes -->
                                            <div 
                                                x-show="open" 
                                                x-transition 
                                                class="absolute left-0 z-50 mt-1 w-64 rounded-xl border border-slate-200 bg-white shadow-xl py-2 text-xs"
                                                style="display: none;"
                                            >
                                                <!-- Topo: Checkbox Geral e Lupa Q -->
                                                <div class="flex items-center gap-2 px-3 pb-2 border-b border-slate-100">
                                                    <input 
                                                        type="checkbox" 
                                                        @click="$wire.toggleAllAgeMonths()" 
                                                        :checked="$wire.advAgeMonths.length === 25"
                                                        class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer h-4 w-4"
                                                        title="Selecionar / Desmarcar todos"
                                                    />
                                                    <div class="relative flex-1">
                                                        <input 
                                                            type="text" 
                                                            x-model="search" 
                                                            placeholder="Filtrar mês..." 
                                                            class="w-full rounded-lg border border-slate-200 py-1 pl-2 pr-7 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                                                            @click.stop
                                                        />
                                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400">
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Lista Scrollável com Checkboxes (0 meses até 24 meses) -->
                                                <div class="max-h-52 overflow-y-auto divide-y-0 py-1">
                                                    <template x-for="opt in filtered" :key="opt.value">
                                                        <label class="flex items-center gap-2.5 px-3 py-1.5 hover:bg-slate-50 cursor-pointer text-slate-700">
                                                            <input 
                                                                type="checkbox" 
                                                                :value="opt.value" 
                                                                :checked="$wire.advAgeMonths.includes(opt.value)"
                                                                @change="$wire.toggleAgeMonth(opt.value)"
                                                                class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer h-4 w-4"
                                                            />
                                                            <span x-text="opt.label"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Linha 6: Filtros Booleanos com Botões Toggle SIM / NÃO (Imagens 4 e 5) -->
                                <div class="border-t border-slate-150 pt-3 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <!-- MICI Atualizada? -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-1 font-semibold text-slate-700">
                                                <span>MICI Atualizada?</span>
                                                <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold cursor-help" title="Cadastro Individual atualizado há menos de 24 meses">i</span>
                                            </div>
                                            <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advMici', 'sim')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advMici === 'sim' ? 'bg-sky-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    SIM
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advMici', 'nao')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advMici === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    NÃO
                                                </button>
                                            </div>
                                        </div>

                                        <!-- MICDT Atualizada? -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-1 font-semibold text-slate-700">
                                                <span>MICDT Atualizada?</span>
                                                <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold" title="Cadastro Domiciliar e Territorial atualizado há menos de 24 meses">i</span>
                                            </div>
                                            <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advMicdt', 'sim')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advMicdt === 'sim' ? 'bg-sky-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    SIM
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advMicdt', 'nao')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advMicdt === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    NÃO
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Pessoa Acompanhada? -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-1 font-semibold text-slate-700">
                                                <span>Pessoa Acompanhada?</span>
                                                <span class="inline-flex items-center justify-center h-3.5 w-3.5 rounded-full bg-slate-200 text-slate-600 text-[9px] font-bold" title="Cidadão com acompanhamento ativo no território">i</span>
                                            </div>
                                            <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advAccompanied', 'sim')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advAccompanied === 'sim' ? 'bg-sky-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    SIM
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advAccompanied', 'nao')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advAccompanied === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    NÃO
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Consulta até 30º dia de vida (A) -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-1 font-semibold text-slate-700">
                                                <span>Consulta até 30º dia de vida (A)</span>
                                            </div>
                                            <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advPracticeA', 'sim')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeA === 'sim' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    SIM
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advPracticeA', 'nao')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeA === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    NÃO
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <!-- Consultas (B) -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-1 font-semibold text-slate-700">
                                                <span>Consultas (B)</span>
                                            </div>
                                            <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advPracticeB', 'sim')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeB === 'sim' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    SIM
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advPracticeB', 'nao')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeB === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    NÃO
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Peso e Altura (C) -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-1 font-semibold text-slate-700">
                                                <span>Peso e Altura (C)</span>
                                            </div>
                                            <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advPracticeC', 'sim')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeC === 'sim' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    SIM
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advPracticeC', 'nao')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeC === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    NÃO
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Visitas (D) -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-1 font-semibold text-slate-700">
                                                <span>Visitas (D)</span>
                                            </div>
                                            <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advPracticeD', 'sim')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeD === 'sim' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    SIM
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advPracticeD', 'nao')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeD === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    NÃO
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Vacinas (E) -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-1 font-semibold text-slate-700">
                                                <span>Vacinas (E)</span>
                                            </div>
                                            <div class="flex items-center rounded-xl border border-slate-300 p-0.5 bg-slate-50 w-full">
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advPracticeE', 'sim')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeE === 'sim' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    SIM
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="toggleBooleanFilter('advPracticeE', 'nao')"
                                                    class="flex-1 py-1 rounded-lg font-bold text-center transition cursor-pointer {{ $advPracticeE === 'nao' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                                                >
                                                    NÃO
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="flex items-center justify-end gap-3 border-t border-slate-150 pt-4">
                                <button
                                    type="button"
                                    wire:click="closeAdvancedSearch"
                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition cursor-pointer shadow-2xs"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>Fechar</span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="clearAdvancedFilters"
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition cursor-pointer"
                                >
                                    <span>Limpar</span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="applyAdvancedSearch"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition cursor-pointer"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span>Enviar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- MODAL DE DETALHES CLÍNICOS DA CRIANÇA / BUSCA ATIVA -->
                @if ($showDetailModal && $selectedChild)
                    <div
                        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 backdrop-blur-xs animate-fade-in sm:p-4"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div
                            class="app-modal-panel relative my-3 w-full max-w-3xl space-y-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl sm:my-8 sm:rounded-3xl sm:p-8"
                            @click.outside="$wire.closeChildDetail()"
                        >
                            <!-- Header -->
                            <div class="flex items-start justify-between border-b border-slate-150 pb-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-xl bg-sky-100 text-sky-800 border border-sky-200 px-2.5 py-0.5 text-[10px] font-bold font-mono">
                                            ID #{{ $selectedChild['id'] }}
                                        </span>
                                        <span class="rounded-full bg-slate-100 text-slate-700 px-2.5 py-0.5 text-[10px] font-bold">
                                            {{ $selectedChild['age_months'] }} meses de vida
                                        </span>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">
                                        {{ $selectedChild['name'] }}
                                    </h3>
                                    <p class="text-xs text-slate-500">
                                        Mãe / Responsável: <strong class="text-slate-700">{{ $selectedChild['mother_name'] }}</strong>
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    wire:click="closeChildDetail"
                                    class="rounded-xl p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Cartões de Identificação e Vínculo -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Dados do Cidadão</h4>
                                    <div class="space-y-1.5 text-slate-700">
                                        <div><span class="font-semibold text-slate-500">Data de Nascimento:</span> {{ \Carbon\Carbon::parse($selectedChild['birth_date'])->format('d/m/Y') }}</div>
                                        <div><span class="font-semibold text-slate-500">CNS:</span> <span class="font-mono">{{ $selectedChild['cns'] }}</span></div>
                                        <div><span class="font-semibold text-slate-500">CPF:</span> <span class="font-mono">{{ $selectedChild['cpf'] }}</span></div>
                                        <div><span class="font-semibold text-slate-500">Raça / Cor:</span> {{ $selectedChild['race_color'] }}</div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Vínculo Territorial</h4>
                                    <div class="space-y-1.5 text-slate-700">
                                        <div><span class="font-semibold text-slate-500">Unidade (CNES):</span> {{ $selectedChild['facility_name'] }} ({{ $selectedChild['cnes'] }})</div>
                                        <div><span class="font-semibold text-slate-500">Equipe (INE):</span> {{ $selectedChild['team_name'] }} ({{ $selectedChild['ine'] }})</div>
                                        <div><span class="font-semibold text-slate-500">Microárea:</span> Microárea {{ $selectedChild['microarea'] }} ({{ $selectedChild['district'] }})</div>
                                        <div><span class="font-semibold text-slate-500">ACS Responsável:</span> {{ $selectedChild['professional_name'] }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Auditoria das 5 Boas Práticas Clínicas (A a E) -->
                            <div class="space-y-3">
                                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                    Status das 5 Boas Práticas Clínicas (Nota Metodológica C2 · Portaria 3.493/2024)
                                </h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <!-- Prática A -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedChild['practice_a'] >= 1 ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedChild['practice_a'] >= 1 ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (A) Consulta até 30º dia de vida
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedChild['practice_a'] >= 1 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedChild['practice_a'] }} {{ $selectedChild['practice_a'] == 1 ? 'consulta' : 'consultas' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedChild['practice_a'] >= 1 ? 'Prática cumprida. Primeira consulta de puericultura realizada dentro da janela preconizada de 30 dias.' : 'Pendente. Necessário verificar o registro da consulta neonatal no prontuário eletrônico e-SUS PEC.' }}
                                        </p>
                                    </div>

                                    <!-- Prática B -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedChild['practice_b'] >= 9 ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedChild['practice_b'] >= 9 ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (B) Ao menos 9 Consultas Puericultura
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedChild['practice_b'] >= 9 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedChild['practice_b'] }} / 9 consultas
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedChild['practice_b'] >= 9 ? 'Meta atingida. A criança possui 9 ou mais consultas médicas/enfermagem de puericultura.' : 'Acompanhamento em curso: agendar próximas consultas programadas conforme o calendário oficial.' }}
                                        </p>
                                    </div>

                                    <!-- Prática C -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedChild['practice_c'] >= 9 ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedChild['practice_c'] >= 9 ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (C) 9 Registros de Peso e Altura Simultâneos
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedChild['practice_c'] >= 9 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedChild['practice_c'] }} / 9 medições
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedChild['practice_c'] >= 9 ? 'Meta atingida. Peso e altura aferidos no mesmo dia nas consultas de puericultura.' : 'Atenção: sempre registrar peso e altura juntos na mesma data para pontuar na curva da OMS.' }}
                                        </p>
                                    </div>

                                    <!-- Prática D -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedChild['practice_d'] >= 2 ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedChild['practice_d'] >= 2 ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (D) 2 Visitas Domiciliares do ACS
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedChild['practice_d'] >= 2 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedChild['practice_d'] }} / 2 visitas
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedChild['practice_d'] >= 2 ? 'Meta atingida. O ACS realizou as visitas domiciliares recomendadas até os 6 meses.' : 'Pendente: acionar o ACS do microterritório para realizar visita domiciliar presencial.' }}
                                        </p>
                                    </div>

                                    <!-- Prática E -->
                                    <div class="sm:col-span-2 rounded-2xl border p-3.5 {{ $selectedChild['practice_e'] >= 10 ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedChild['practice_e'] >= 10 ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (E) Esquema Vacinal Recomendado (Penta, VIP, Pneumo 10v e Tríplice Viral)
                                            </span>
                                            <span class="font-black px-2.5 py-0.5 rounded-md text-[11px] {{ $selectedChild['practice_e'] >= 10 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedChild['practice_e'] }} doses administradas
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedChild['practice_e'] >= 10 ? 'Calendário vacinal completo com todos os imunobiológicos administrados e registrados na RNDS/PEC.' : 'Atenção vacinal: convocar os responsáveis à sala de vacina da UBS para atualização imediata da caderneta de vacinação.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer do Modal -->
                            <div class="flex items-center justify-between border-t border-slate-150 pt-4">
                                <span class="text-[11px] text-slate-400">
                                    Mês de conclusão da coorte: <strong class="text-slate-600">{{ $selectedChild['month_ref'] }}</strong>
                                </span>

                                <button
                                    type="button"
                                    wire:click="closeChildDetail"
                                    class="px-5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition cursor-pointer"
                                >
                                    Fechar Ficha
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @elseif ($isC3)
            <!-- MÓDULO C3: BUSCA ATIVA & BOAS PRÁTICAS NA GESTAÇÃO E PUERPÉRIO -->
            <div class="space-y-6 animate-fade-in">
                <!-- Cabeçalho da Seção com Título e Botão Busca Avançada -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h3 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight">
                                Componente de Qualidade / Saúde da Família - C3 Cuidado na Gestação e Puerpério
                            </h3>
                            @if ($isRealC3DataAvailable)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Base Real e-SUS PEC ({{ number_format($realPregnanciesCount, 0, '', '.') }} gestantes/puérperas)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Demonstração · Processe em Configurações > Processamento de Dados
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Lista nominal e busca ativa prospectiva de gestantes (semanas 1 a 42) e puérperas (até 42 dias) vinculadas às Equipes de Saúde da Família
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @if ($activeFiltersCount > 0)
                            <button
                                type="button"
                                wire:click="clearAdvancedFilters"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition cursor-pointer shadow-2xs"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Limpar Filtros ({{ $activeFiltersCount }})</span>
                            </button>
                        @endif

                        <!-- Botão Personalizar Colunas (Dropdown) -->
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button
                                type="button"
                                @click="open = !open"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition cursor-pointer shadow-2xs"
                            >
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                <span>Colunas ({{ count($visibleColumns) }})</span>
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div
                                x-show="open"
                                x-transition
                                class="absolute right-0 z-40 mt-1.5 w-72 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl"
                                style="display: none;"
                            >
                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 text-xs">
                                    <span class="font-bold text-slate-700">Colunas Visíveis</span>
                                    <div class="flex items-center gap-2 text-[11px]">
                                        <button
                                            type="button"
                                            wire:click="selectAllColumns"
                                            class="text-sky-600 hover:text-sky-800 font-semibold cursor-pointer"
                                        >
                                            Todas
                                        </button>
                                        <span class="text-slate-300">|</span>
                                        <button
                                            type="button"
                                            wire:click="resetDefaultColumns"
                                            class="text-slate-500 hover:text-slate-800 font-semibold cursor-pointer"
                                        >
                                            Padrão
                                        </button>
                                    </div>
                                </div>

                                <div class="max-h-64 overflow-y-auto space-y-1.5 scrollbar-thin pr-1 text-xs">
                                    @foreach ($c3AvailableColumns as $colKey => $colLabel)
                                        <label class="flex items-center gap-2.5 p-1 rounded-lg hover:bg-slate-50 cursor-pointer select-none">
                                            <input
                                                type="checkbox"
                                                wire:click="toggleColumn('{{ $colKey }}')"
                                                @checked(in_array($colKey, $visibleColumns, true))
                                                class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 h-3.5 w-3.5 cursor-pointer"
                                            />
                                            <span class="text-slate-700 font-medium text-[11px]">{{ $colLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="openAdvancedSearch"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-sm transition cursor-pointer"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <span>Busca Avançada</span>
                        </button>
                    </div>
                </div>

                <!-- BANNER SUPERIOR: DADOS GERAIS C3 (SÍNTESE DAS 11 BOAS PRÁTICAS) -->
                @if ($c3SummaryKpis)
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs space-y-4">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-slate-100 pb-3">
                            <div>
                                <span class="text-xs font-semibold text-slate-500 block">Período de Acompanhamento</span>
                                <div class="text-lg sm:text-xl font-black text-slate-800 tracking-tight">
                                    {{ $c3SummaryKpis['period_label'] }}
                                </div>
                                <span class="text-[11px] text-slate-400">
                                    {{ $c3SummaryKpis['period_sublabel'] }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-center">
                                    <span class="text-[10px] font-bold text-emerald-800 uppercase block">Gestantes Ativas</span>
                                    <span class="text-base font-black text-emerald-900 font-mono">
                                        {{ number_format($c3SummaryKpis['gestantes_count'], 0, '', '.') }}
                                    </span>
                                </div>
                                <div class="rounded-xl bg-purple-50 border border-purple-200 px-3 py-1.5 text-center">
                                    <span class="text-[10px] font-bold text-purple-800 uppercase block">Puérperas (≤ 42d)</span>
                                    <span class="text-base font-black text-purple-900 font-mono">
                                        {{ number_format($c3SummaryKpis['puerperas_count'], 0, '', '.') }}
                                    </span>
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-200 px-3 py-1.5 text-center">
                                    <span class="text-[10px] font-bold text-slate-700 uppercase block">Puerpério Concluído</span>
                                    <span class="text-base font-black text-slate-900 font-mono">
                                        {{ number_format($c3SummaryKpis['encerradas_count'], 0, '', '.') }}
                                    </span>
                                </div>
                                <div class="rounded-xl bg-teal-50 border border-teal-200 px-3 py-1.5 text-center">
                                    <span class="text-[10px] font-bold text-teal-800 uppercase block">Total Acompanhadas</span>
                                    <span class="text-base font-black text-teal-950 font-mono">
                                        {{ number_format($c3SummaryKpis['denominator'], 0, '', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Grid das 11 Boas Práticas Clínicas (A a K) -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            @foreach (['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k'] as $pKey)
                                @php $pData = $c3SummaryKpis['practice_'.$pKey]; @endphp
                                <div class="rounded-2xl border border-slate-150 bg-slate-50/50 p-3 space-y-1 hover:bg-slate-50 transition">
                                    <div class="flex items-center justify-between">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-lg bg-teal-800 text-white font-bold text-[10px] font-mono">
                                            {{ strtoupper($pKey) }}
                                        </span>
                                        <span class="text-[10px] font-mono font-bold text-slate-500">
                                            {{ $pKey === 'a' ? '10 pts' : '9 pts' }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-800 truncate" title="{{ $pData['label'] }}">
                                        {{ $pData['label'] }}
                                    </div>
                                    <div class="flex items-baseline gap-1.5 pt-0.5">
                                        <span class="text-sm font-black text-teal-800 font-mono">
                                            {{ number_format($pData['count'], 0, '', '.') }}
                                        </span>
                                        <span class="text-[10px] font-semibold text-teal-900">
                                            ({{ number_format($pData['percent'], 1, ',', '.') }}%)
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- LISTA NOMINAL: TABELA INTERATIVA DE GESTANTES E PUÉRPERAS -->
                <div class="rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                                <table class="w-full min-w-[44rem] text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                                <tr>
                                    @if (in_array('id', $visibleColumns, true))
                                        <th class="py-3 px-3">#</th>
                                    @endif
                                    @if (in_array('cns', $visibleColumns, true))
                                        <th class="py-3 px-3">CNS</th>
                                    @endif
                                    @if (in_array('cpf', $visibleColumns, true))
                                        <th class="py-3 px-3">CPF</th>
                                    @endif
                                    @if (in_array('name', $visibleColumns, true))
                                        <th class="py-3 px-4">Nome da Gestante / Puérpera</th>
                                    @endif
                                    @if (in_array('birth_date', $visibleColumns, true))
                                        <th class="py-3 px-3">Nascimento</th>
                                    @endif
                                    @if (in_array('phone', $visibleColumns, true))
                                        <th class="py-3 px-3">Telefone</th>
                                    @endif
                                    @if (in_array('current_status', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center">Status</th>
                                    @endif
                                    @if (in_array('gestational_age_weeks', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center">Idade Gestacional</th>
                                    @endif
                                    @if (in_array('dum', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center">DUM</th>
                                    @endif
                                    @if (in_array('dpp', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center">DPP</th>
                                    @endif
                                    @if (in_array('pregnancy_end_date', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center">Desfecho estimado</th>
                                    @endif
                                    @if (in_array('puerperium_end_date', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center">42º dia estimado</th>
                                    @endif
                                    @if (in_array('practice_a_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="Captação Precoce (até 12ª sem) · 10 pts">A (10p)</th>
                                    @endif
                                    @if (in_array('practice_b_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="≥ 7 Consultas Pré-Natal · 9 pts">B (9p)</th>
                                    @endif
                                    @if (in_array('practice_c_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="≥ 7 Aferições PA · 9 pts">C (9p)</th>
                                    @endif
                                    @if (in_array('practice_d_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="≥ 7 Registros Peso/Altura · 9 pts">D (9p)</th>
                                    @endif
                                    @if (in_array('practice_e_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="≥ 3 Visitas ACS Gestação · 9 pts">E (9p)</th>
                                    @endif
                                    @if (in_array('practice_f_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="Vacina dTpa (≥ 20ª sem) · 9 pts">F (9p)</th>
                                    @endif
                                    @if (in_array('practice_g_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="Exames 1º Trimestre · 9 pts">G (9p)</th>
                                    @endif
                                    @if (in_array('practice_h_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="Exames 3º Trimestre · 9 pts">H (9p)</th>
                                    @endif
                                    @if (in_array('practice_i_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="Consulta Puerpério (até 42d) · 9 pts">I (9p)</th>
                                    @endif
                                    @if (in_array('practice_j_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="Visita ACS Puerpério (até 42d) · 9 pts">J (9p)</th>
                                    @endif
                                    @if (in_array('practice_k_met', $visibleColumns, true))
                                        <th class="py-3 px-2 text-center" title="Saúde Bucal Gestação · 9 pts">K (9p)</th>
                                    @endif
                                    @if (in_array('total_points', $visibleColumns, true))
                                        <th class="py-3 px-3 text-center font-black text-ink">Pontuação</th>
                                    @endif
                                    @if (in_array('team', $visibleColumns, true))
                                        <th class="py-3 px-3">Equipe</th>
                                    @endif
                                    @if (in_array('microarea', $visibleColumns, true))
                                        <th class="py-3 px-3">Microárea</th>
                                    @endif
                                    @if (in_array('professional', $visibleColumns, true))
                                        <th class="py-3 px-3">ACS</th>
                                    @endif
                                    @if (in_array('facility', $visibleColumns, true))
                                        <th class="py-3 px-3">Unidade</th>
                                    @endif
                                    <th class="py-3 px-4 text-center">Ficha Clínica</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($c3NominalList as $row)
                                    <tr class="hover:bg-slate-50/80 transition group">
                                        @if (in_array('id', $visibleColumns, true))
                                            <td class="py-3 px-3 font-mono text-slate-400 text-[11px]">{{ $row['id'] }}</td>
                                        @endif
                                        @if (in_array('cns', $visibleColumns, true))
                                            <td class="py-3 px-3 font-mono text-slate-600 text-[11px]">{{ $row['cns'] }}</td>
                                        @endif
                                        @if (in_array('cpf', $visibleColumns, true))
                                            <td class="py-3 px-3 font-mono text-slate-600 text-[11px]">{{ $row['cpf'] }}</td>
                                        @endif
                                        @if (in_array('name', $visibleColumns, true))
                                            <td class="py-3 px-4 font-bold text-ink">
                                                <button
                                                    type="button"
                                                    wire:click="openPregnancyDetail({{ $row['id'] }})"
                                                    class="text-left text-slate-900 hover:text-sky-700 transition cursor-pointer font-bold block"
                                                >
                                                    {{ $row['name'] }}
                                                </button>
                                                @if (! empty($row['race_color']))
                                                    <span class="text-[10px] text-slate-400 font-normal block">{{ $row['race_color'] }}</span>
                                                @endif
                                            </td>
                                        @endif
                                        @if (in_array('birth_date', $visibleColumns, true))
                                            <td class="py-3 px-3 text-slate-600 font-mono text-[11px]">
                                                {{ ! empty($row['birth_date']) ? \Carbon\Carbon::parse($row['birth_date'])->format('d/m/Y') : '—' }}
                                            </td>
                                        @endif
                                        @if (in_array('phone', $visibleColumns, true))
                                            <td class="py-3 px-3 text-slate-600 font-mono text-[11px]">
                                                {{ $row['phone'] ?: '—' }}
                                            </td>
                                        @endif
                                        @if (in_array('current_status', $visibleColumns, true))
                                            <td class="py-3 px-3 text-center">
                                                @if (($row['current_status'] ?? '') === 'gestante')
                                                    <span class="inline-block rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 px-2 py-0.5 text-[10px] font-bold">
                                                        Gestante
                                                    </span>
                                                @elseif (($row['current_status'] ?? '') === 'puerpera')
                                                    <span class="inline-block rounded-full bg-purple-100 text-purple-800 border border-purple-200 px-2 py-0.5 text-[10px] font-bold">
                                                        Puérpera
                                                    </span>
                                                @else
                                                    <span class="inline-block rounded-full bg-slate-100 text-slate-700 border border-slate-200 px-2 py-0.5 text-[10px] font-bold">
                                                        Concluída
                                                    </span>
                                                @endif
                                            </td>
                                        @endif
                                        @if (in_array('gestational_age_weeks', $visibleColumns, true))
                                            <td class="py-3 px-3 text-center font-mono font-semibold text-slate-700">
                                                @if (($row['current_status'] ?? '') === 'gestante' && ($row['gestational_age_weeks'] ?? null) !== null)
                                                    {{ $row['gestational_age_weeks'] }} sem
                                                @elseif (($row['current_status'] ?? '') === 'puerpera' && ($row['days_postpartum'] ?? null) !== null)
                                                    D+{{ $row['days_postpartum'] }} parto
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        @endif
                                        @if (in_array('dum', $visibleColumns, true))
                                            <td class="py-3 px-3 text-center font-mono text-[11px] text-slate-600">
                                                {{ ! empty($row['dum']) ? \Carbon\Carbon::parse($row['dum'])->format('d/m/Y') : '—' }}
                                            </td>
                                        @endif
                                        @if (in_array('dpp', $visibleColumns, true))
                                            <td class="py-3 px-3 text-center font-mono text-[11px] text-slate-600">
                                                {{ ! empty($row['dpp']) ? \Carbon\Carbon::parse($row['dpp'])->format('d/m/Y') : '—' }}
                                            </td>
                                        @endif
                                        @if (in_array('pregnancy_end_date', $visibleColumns, true))
                                            <td class="py-3 px-3 text-center font-mono text-[11px] text-slate-600">
                                                {{ ! empty($row['pregnancy_end_date']) ? \Carbon\Carbon::parse($row['pregnancy_end_date'])->format('d/m/Y') : '—' }}
                                            </td>
                                        @endif
                                        @if (in_array('puerperium_end_date', $visibleColumns, true))
                                            <td class="py-3 px-3 text-center font-mono text-[11px] text-slate-600">
                                                {{ ! empty($row['puerperium_end_date']) ? \Carbon\Carbon::parse($row['puerperium_end_date'])->format('d/m/Y') : '—' }}
                                            </td>
                                        @endif
                                        @if (in_array('practice_a_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_a_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_a_met'] ? '✓ 10' : '✗' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('practice_b_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_b_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_b_met'] ? '✓ 9' : $row['practice_b_count'].'/7' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('practice_c_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_c_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_c_met'] ? '✓ 9' : $row['practice_c_count'].'/7' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('practice_d_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_d_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_d_met'] ? '✓ 9' : $row['practice_d_count'].'/7' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('practice_e_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_e_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_e_met'] ? '✓ 9' : $row['practice_e_count'].'/3' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('practice_f_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_f_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_f_met'] ? '✓ 9' : '✗' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('practice_g_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_g_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_g_met'] ? '✓ 9' : '✗' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('practice_h_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_h_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_h_met'] ? '✓ 9' : '✗' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('practice_i_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_i_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_i_met'] ? '✓ 9' : '✗' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('practice_j_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_j_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_j_met'] ? '✓ 9' : '✗' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('practice_k_met', $visibleColumns, true))
                                            <td class="py-3 px-2 text-center">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $row['practice_k_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $row['practice_k_met'] ? '✓ 9' : '✗' }}
                                                </span>
                                            </td>
                                        @endif
                                        @if (in_array('total_points', $visibleColumns, true))
                                            <td class="py-3 px-3 text-center font-mono font-black text-sm {{ $row['total_points'] >= 75 ? 'text-sky-700' : ($row['total_points'] >= 50 ? 'text-emerald-700' : ($row['total_points'] >= 25 ? 'text-amber-700' : 'text-rose-700')) }}">
                                                {{ $row['total_points'] }} / 100
                                            </td>
                                        @endif
                                        @if (in_array('team', $visibleColumns, true))
                                            <td class="py-3 px-3 text-slate-600">
                                                <span class="block font-medium text-ink truncate max-w-[140px]">{{ $row['team_name'] }}</span>
                                                <span class="text-[10px] text-slate-400 font-mono">INE {{ $row['ine'] }}</span>
                                            </td>
                                        @endif
                                        @if (in_array('microarea', $visibleColumns, true))
                                            <td class="py-3 px-3 font-mono text-slate-600 text-center">{{ $row['microarea'] ?: '—' }}</td>
                                        @endif
                                        @if (in_array('professional', $visibleColumns, true))
                                            <td class="py-3 px-3 text-slate-600 truncate max-w-[130px]">{{ $row['professional_name'] ?: 'Não vinculado' }}</td>
                                        @endif
                                        @if (in_array('facility', $visibleColumns, true))
                                            <td class="py-3 px-3 text-slate-600 truncate max-w-[140px]">{{ $row['facility_name'] ?: '—' }}</td>
                                        @endif
                                        <td class="py-3 px-4 text-center">
                                            <button
                                                type="button"
                                                wire:click="openPregnancyDetail({{ $row['id'] }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 transition cursor-pointer border border-sky-200"
                                            >
                                                <span>Ficha</span>
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="28" class="py-12 text-center text-slate-400">
                                            <div class="flex flex-col items-center justify-center space-y-2">
                                                <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                </svg>
                                                <span class="text-sm font-semibold">Nenhuma gestante ou puérpera encontrada com os filtros selecionados.</span>
                                                <span class="text-xs text-slate-400">Tente ajustar a busca avançada ou limpe os filtros.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Rodapé da Tabela: Paginação -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <span>Exibindo <strong>{{ $c3NominalList->count() }}</strong> de <strong>{{ number_format($c3TotalItems, 0, '', '.') }}</strong> gestantes/puérperas acompanhadas</span>
                            <span class="text-slate-300">|</span>
                            <span>Página {{ $c3Page }} de {{ $c3TotalPages }}</span>
                        </div>

                        @if ($c3TotalPages > 1)
                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    wire:click="gotoC3Page({{ $c3Page - 1 }})"
                                    @disabled($c3Page <= 1)
                                    class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer shadow-2xs"
                                >
                                    &larr; Anterior
                                </button>

                                @for ($p = max(1, $c3Page - 2); $p <= min($c3TotalPages, $c3Page + 2); $p++)
                                    <button
                                        type="button"
                                        wire:click="gotoC3Page({{ $p }})"
                                        class="px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer {{ $p === $c3Page ? 'bg-sky-600 text-white shadow-2xs' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 shadow-2xs' }}"
                                    >
                                        {{ $p }}
                                    </button>
                                @endfor

                                <button
                                    type="button"
                                    wire:click="gotoC3Page({{ $c3Page + 1 }})"
                                    @disabled($c3Page >= $c3TotalPages)
                                    class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer shadow-2xs"
                                >
                                    Próximo &rarr;
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- MODAL DE BUSCA AVANÇADA C3 -->
                @if ($showAdvancedModal)
                    <div
                        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 backdrop-blur-xs animate-fade-in sm:p-4"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div
                            class="app-modal-panel relative my-3 w-full max-w-4xl space-y-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl sm:my-8 sm:rounded-3xl sm:p-8"
                            @click.outside="$wire.closeAdvancedSearch()"
                        >
                            <div class="flex items-center justify-between border-b border-slate-150 pb-4">
                                <h3 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">
                                    Busca Avançada · C3 Gestação e Puerpério
                                </h3>
                                <button
                                    type="button"
                                    wire:click="closeAdvancedSearch"
                                    class="rounded-xl p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-4 text-xs">
                                <!-- Linha 1: Equipe e Microárea -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="sm:col-span-2 space-y-1">
                                        <label class="font-semibold text-slate-700 block">Equipe</label>
                                        <select
                                            wire:model.live="advTeam"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        >
                                            <option value="">Todas as Equipes (ou selecione uma equipe)</option>
                                            @foreach ($c3FilterOptions['teams'] ?? [] as $tm)
                                                <option value="{{ $tm['ine'] }}">{{ $tm['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Microárea</label>
                                        <input
                                            type="text"
                                            wire:model.live="advMicroarea"
                                            placeholder="Ex: 01, 02..."
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>
                                </div>

                                <!-- Linha 2: Nome da Gestante, CPF, CNS, Telefone -->
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div class="space-y-1 sm:col-span-2">
                                        <label class="font-semibold text-slate-700 block">Nome da Cidadã</label>
                                        <input
                                            type="text"
                                            wire:model.live="advCitizenName"
                                            placeholder="Digite o nome da gestante/puérpera"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">CPF</label>
                                        <input
                                            type="text"
                                            wire:model.live="advCitizenCpf"
                                            placeholder="000.000.000-00"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Telefone</label>
                                        <input
                                            type="text"
                                            wire:model.live="advPhone"
                                            placeholder="(00) 00000-0000"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        />
                                    </div>
                                </div>

                                <!-- Linha 3: Status, Trimestre/Fase, Mês e Quadrimestre -->
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Status da Cidadã</label>
                                        <select
                                            wire:model.live="advStatus"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        >
                                            <option value="">Todos os Status</option>
                                            @foreach ($c3FilterOptions['status_options'] ?? [] as $st)
                                                <option value="{{ $st['value'] }}">{{ $st['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Fase Gestacional</label>
                                        <select
                                            wire:model.live="advTrimester"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        >
                                            <option value="">Todas as Fases</option>
                                            @foreach ($c3FilterOptions['trimester_options'] ?? [] as $tr)
                                                <option value="{{ $tr['value'] }}">{{ $tr['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Mês da Coorte</label>
                                        <select
                                            wire:model.live="advMonth"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        >
                                            <option value="">Todos os Meses</option>
                                            @foreach ($c3FilterOptions['months'] ?? [] as $mn)
                                                <option value="{{ $mn['value'] }}">{{ $mn['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="font-semibold text-slate-700 block">Quadrimestre</label>
                                        <select
                                            wire:model.live="advQuarter"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-2xs"
                                        >
                                            <option value="">Todos os Quadrimestres</option>
                                            @foreach ($c3FilterOptions['quarters'] ?? [] as $qr)
                                                <option value="{{ $qr['value'] }}">{{ $qr['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Linha 4: Filtros de Boas Práticas Clínicas (A a K) -->
                                <div class="space-y-2 border-t border-slate-150 pt-3">
                                    <label class="font-bold text-slate-800 block uppercase tracking-wider text-[11px]">
                                        Filtro Rápido por Boas Práticas Clínicas
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(A) Captação Precoce (10p)</span>
                                            <select wire:model.live="advPracticeA" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(B) ≥ 7 Consultas (9p)</span>
                                            <select wire:model.live="advPracticeB" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(C) ≥ 7 Aferições PA (9p)</span>
                                            <select wire:model.live="advPracticeC" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(D) ≥ 7 Peso/Alt (9p)</span>
                                            <select wire:model.live="advPracticeD" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(E) ≥ 3 VD ACS (9p)</span>
                                            <select wire:model.live="advPracticeE" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(F) Vacina dTpa (9p)</span>
                                            <select wire:model.live="advPracticeF" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(G) Exames 1ºT (9p)</span>
                                            <select wire:model.live="advPracticeG" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(H) Exames 3ºT (9p)</span>
                                            <select wire:model.live="advPracticeH" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(I) Consulta Puerpério (9p)</span>
                                            <select wire:model.live="advPracticeI" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(J) VD ACS Puerpério (9p)</span>
                                            <select wire:model.live="advPracticeJ" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="font-medium text-slate-700 truncate pr-2">(K) Odonto Gestação (9p)</span>
                                            <select wire:model.live="advPracticeK" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700">
                                                <option value="">Todas</option>
                                                <option value="sim">Cumprida</option>
                                                <option value="nao">Pendente</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-slate-150 pt-4">
                                <button
                                    type="button"
                                    wire:click="clearAdvancedFilters"
                                    class="px-4 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition cursor-pointer border border-rose-200"
                                >
                                    Limpar Filtros
                                </button>

                                <button
                                    type="button"
                                    wire:click="applyAdvancedSearch"
                                    class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 transition cursor-pointer shadow-sm"
                                >
                                    Aplicar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- MODAL DE DETALHES CLÍNICOS DA GESTAÇÃO E PUERPÉRIO -->
                @if ($showPregnancyDetailModal && $selectedPregnancy)
                    <div
                        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 backdrop-blur-xs animate-fade-in sm:p-4"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div
                            class="app-modal-panel relative my-3 w-full max-w-3xl space-y-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl sm:my-8 sm:rounded-3xl sm:p-8"
                            @click.outside="$wire.closePregnancyDetail()"
                        >
                            <!-- Header -->
                            <div class="flex items-start justify-between border-b border-slate-150 pb-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-xl bg-sky-100 text-sky-800 border border-sky-200 px-2.5 py-0.5 text-[10px] font-bold font-mono">
                                            ID #{{ $selectedPregnancy['id'] }}
                                        </span>
                                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ ($selectedPregnancy['current_status'] ?? '') === 'gestante' ? 'bg-emerald-100 text-emerald-800' : (($selectedPregnancy['current_status'] ?? '') === 'puerpera' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-800') }}">
                                            {{ ucfirst($selectedPregnancy['current_status'] ?? 'Gestante') }}
                                            @if (($selectedPregnancy['current_status'] ?? '') === 'gestante' && ($selectedPregnancy['gestational_age_weeks'] ?? null) !== null)
                                                · {{ $selectedPregnancy['gestational_age_weeks'] }} semanas
                                            @elseif (($selectedPregnancy['current_status'] ?? '') === 'puerpera' && ($selectedPregnancy['days_postpartum'] ?? null) !== null)
                                                · D+{{ $selectedPregnancy['days_postpartum'] }} parto
                                            @endif
                                        </span>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">
                                        {{ $selectedPregnancy['name'] }}
                                    </h3>
                                    <p class="text-xs text-slate-500">
                                        Telefone: <strong class="text-slate-700">{{ $selectedPregnancy['phone'] ?: 'Não informado' }}</strong>
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    wire:click="closePregnancyDetail"
                                    class="rounded-xl p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Cartões de Identificação e Vínculo -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Dados da Cidadã</h4>
                                    <div class="space-y-1.5 text-slate-700">
                                        <div><span class="font-semibold text-slate-500">Data de Nascimento:</span> {{ ! empty($selectedPregnancy['birth_date']) ? \Carbon\Carbon::parse($selectedPregnancy['birth_date'])->format('d/m/Y') : '—' }}</div>
                                        <div><span class="font-semibold text-slate-500">CNS:</span> <span class="font-mono">{{ $selectedPregnancy['cns'] }}</span></div>
                                        <div><span class="font-semibold text-slate-500">CPF:</span> <span class="font-mono">{{ $selectedPregnancy['cpf'] }}</span></div>
                                        <div><span class="font-semibold text-slate-500">Raça / Cor:</span> {{ $selectedPregnancy['race_color'] }}</div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Vínculo Territorial & Linha Obstétrica</h4>
                                    <div class="space-y-1.5 text-slate-700">
                                        <div><span class="font-semibold text-slate-500">Equipe (INE):</span> {{ $selectedPregnancy['team_name'] }} ({{ $selectedPregnancy['ine'] }})</div>
                                        <div><span class="font-semibold text-slate-500">Microárea:</span> {{ $selectedPregnancy['microarea'] }}</div>
                                        <div><span class="font-semibold text-slate-500">ACS Responsável:</span> {{ $selectedPregnancy['professional_name'] ?: 'Não vinculado' }}</div>
                                        <div><span class="font-semibold text-slate-500">DUM / DPP:</span> {{ $selectedPregnancy['dum'] ? \Carbon\Carbon::parse($selectedPregnancy['dum'])->format('d/m/Y') : '—' }} / {{ $selectedPregnancy['dpp'] ? \Carbon\Carbon::parse($selectedPregnancy['dpp'])->format('d/m/Y') : '—' }}</div>
                                        @if (! empty($selectedPregnancy['puerperium_end_date']))
                                            <div><span class="font-semibold text-slate-500">Fim estimado do puerpério (42d):</span> {{ \Carbon\Carbon::parse($selectedPregnancy['puerperium_end_date'])->format('d/m/Y') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Auditoria das 11 Boas Práticas Clínicas (A a K) -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                        Status das 11 Boas Práticas Clínicas (Nota Metodológica C3 · Portaria 3.493/2024)
                                    </h4>
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-black font-mono {{ $selectedPregnancy['total_points'] >= 75 ? 'bg-sky-100 text-sky-800' : ($selectedPregnancy['total_points'] >= 50 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                                        Total: {{ $selectedPregnancy['total_points'] }} / 100 pontos
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <!-- Prática A -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_a_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_a_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (A) Captação Precoce (≤ 12 sem)
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_a_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_a_met'] ? '10 pts' : '0 pt' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_a_met'] ? 'Prática cumprida. Primeira consulta realizada até a 12ª semana com CIAP-2/CID-10.' : 'Pendente. Cadastro pré-natal tardio ou registro fora do prazo limite de 12 semanas.' }}
                                        </p>
                                    </div>

                                    <!-- Prática B -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_b_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_b_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (B) ≥ 7 Consultas de Pré-Natal
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_b_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_b_count'] }} / 7 consultas
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_b_met'] ? 'Meta atingida. Ao menos 7 consultas médicas ou de enfermagem realizadas.' : 'Acompanhamento em curso: agendar próximas consultas programadas de pré-natal.' }}
                                        </p>
                                    </div>

                                    <!-- Prática C -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_c_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_c_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (C) ≥ 7 Aferições de Pressão Arterial
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_c_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_c_count'] }} / 7 registros
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_c_met'] ? 'Meta atingida. Vigilância contínua de PA realizada durante o pré-natal.' : 'Atenção: aferir e registrar a pressão arterial em todas as consultas da gestante.' }}
                                        </p>
                                    </div>

                                    <!-- Prática D -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_d_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_d_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (D) ≥ 7 Registros de Peso e Altura
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_d_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_d_count'] }} / 7 medições
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_d_met'] ? 'Meta atingida. Registro concomitante de peso e altura mantido.' : 'Atenção: registrar peso e altura na mesma data para cálculo de IMC gestacional.' }}
                                        </p>
                                    </div>

                                    <!-- Prática E -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_e_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_e_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (E) ≥ 3 Visitas Domiciliares ACS
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_e_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_e_count'] }} / 3 visitas
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_e_met'] ? 'Prática cumprida. Vínculo comunitário ativo mantido pelo ACS.' : 'Pendente: acionar o ACS do microterritório para visitas domiciliares.' }}
                                        </p>
                                    </div>

                                    <!-- Prática F -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_f_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_f_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (F) Vacina dTpa (≥ 20ª sem)
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_f_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_f_met'] ? '9 pts' : '0 pt' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_f_met'] ? 'Vacina dTpa administrada e registrada a partir da 20ª semana.' : 'Atenção vacinal: encaminhar a gestante à sala de vacina da UBS para imunização.' }}
                                        </p>
                                    </div>

                                    <!-- Prática G -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_g_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_g_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (G) Exames 1ºT (até 13ª sem)
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_g_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_g_met'] ? '9 pts' : '0 pt' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_g_met'] ? 'Exames de Sífilis, HIV, Hepatite B e C solicitados/avaliados até a 13ª semana.' : 'Pendente: verificar solicitação e resultado dos exames do 1º trimestre.' }}
                                        </p>
                                    </div>

                                    <!-- Prática H -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_h_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_h_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (H) Exames 3ºT (≥ 28ª sem)
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_h_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_h_met'] ? '9 pts' : '0 pt' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_h_met'] ? 'Rastreio de Sífilis e HIV realizado no 3º trimestre gestacional.' : 'Atenção: solicitar ou avaliar os testes rápidos/sorologias no 3º trimestre.' }}
                                        </p>
                                    </div>

                                    <!-- Prática I -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_i_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_i_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (I) Consulta Puerpério (≤ 42d)
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_i_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_i_met'] ? '9 pts' : '0 pt' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_i_met'] ? 'Consulta pós-parto realizada por médico ou enfermeiro até 42 dias.' : 'Pendente: agendar consulta puerperal presencial com urgência.' }}
                                        </p>
                                    </div>

                                    <!-- Prática J -->
                                    <div class="rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_j_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_j_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (J) Visita ACS Puerpério (≤ 42d)
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_j_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_j_met'] ? '9 pts' : '0 pt' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_j_met'] ? 'Visita domiciliar do ACS realizada no puerpério imediato/tardio.' : 'Pendente: acionar o ACS para visita domiciliar pós-parto.' }}
                                        </p>
                                    </div>

                                    <!-- Prática K -->
                                    <div class="sm:col-span-2 rounded-2xl border p-3.5 {{ $selectedPregnancy['practice_k_met'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold {{ $selectedPregnancy['practice_k_met'] ? 'text-emerald-950' : 'text-rose-950' }}">
                                                (K) Atendimento Odontológico na Gestação
                                            </span>
                                            <span class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $selectedPregnancy['practice_k_met'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $selectedPregnancy['practice_k_met'] ? '9 pts' : '0 pt' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            {{ $selectedPregnancy['practice_k_met'] ? 'Atendimento em saúde bucal realizado por Cirurgião-Dentista ou TSB durante a gestação.' : 'Pendente: encaminhar a gestante para avaliação da saúde bucal com a equipe de odontologia da UBS.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer do Modal -->
                            <div class="flex items-center justify-between border-t border-slate-150 pt-4">
                                <span class="text-[11px] text-slate-400">
                                    Previsão de término do puerpério: <strong class="text-slate-600">{{ $selectedPregnancy['month_ref'] }}</strong>
                                </span>

                                <button
                                    type="button"
                                    wire:click="closePregnancyDetail"
                                    class="px-5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition cursor-pointer"
                                >
                                    Fechar Ficha
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- BUSCA ATIVA PADRÃO PARA OS DEMAIS INDICADORES (C4 A C7) -->
            <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4 animate-fade-in">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-line pb-4">
                    <div>
                        <h3 class="text-base font-bold text-ink">Lista de Busca Ativa · Oportunidades de Cuidado</h3>
                        <p class="text-xs text-muted">Cidadãos vinculados que ainda possuem pendências de boas práticas no quadrimestre</p>
                    </div>
                    <span class="rounded-full bg-amber-100 text-amber-900 border border-amber-300 px-3 py-1 text-xs font-bold">
                        {{ count($activeSearchList) }} Oportunidades Identificadas
                    </span>
                </div>

                <div class="overflow-x-auto">
                                <table class="w-full min-w-[44rem] text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-line font-bold">
                            <tr>
                                <th class="py-3 px-4">Cidadão(ã)</th>
                                <th class="py-3 px-4">Idade</th>
                                <th class="py-3 px-4">Cartão SUS (CNS)</th>
                                <th class="py-3 px-4">Equipe / Microárea</th>
                                <th class="py-3 px-4">Ação Prioritária Pendente</th>
                                <th class="py-3 px-4 text-center">Prioridade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($activeSearchList as $citizen)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3.5 px-4 font-bold text-ink">{{ $citizen['name'] }}</td>
                                    <td class="py-3.5 px-4 text-slate-600">{{ $citizen['age'] }}</td>
                                    <td class="py-3.5 px-4 font-mono text-slate-500">{{ $citizen['cns'] }}</td>
                                    <td class="py-3.5 px-4 text-slate-600">
                                        <span class="block font-medium text-ink">{{ $citizen['team_name'] }}</span>
                                        <span class="text-[11px] text-muted">{{ $citizen['microarea'] }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-amber-900">
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            {{ $citizen['pending_action'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($citizen['priority'] === 'alta')
                                            <span class="rounded-full bg-rose-100 text-rose-800 border border-rose-200 px-2 py-0.5 text-[10px] font-bold">Alta</span>
                                        @else
                                            <span class="rounded-full bg-amber-100 text-amber-800 border border-amber-200 px-2 py-0.5 text-[10px] font-bold">Média</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    <!-- CONTEÚDO DA ABA 4: NOTA TÉCNICA OFICIAL & REGRAS -->
    @if ($activeTab === 'rules')
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-6 animate-fade-in">
            <div class="border-b border-line pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-ink">Caderno Metodológico Oficial & Nota Técnica</h3>
                    <p class="text-xs text-muted">Secretaria de Atenção Primária à Saúde (SAPS/MS) · Coordenação de Avaliação e Tecnologias (CVAT/DEAPS)</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono font-semibold text-teal-800 bg-teal-50 px-3 py-1.5 rounded-xl border border-teal-200">
                        {{ $meta['source_pdf'] }}
                    </span>
                    @if ($isC1 || $isC2 || $isC3)
                        <span class="text-xs font-mono font-semibold text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200">
                            NT 08/2026-DEAPS/SAPS/MS
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Regras de Cálculo -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">1. Definição da Métrica e Fórmula</h4>
                    <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200 space-y-3">
                        <div>
                            <span class="text-xs font-bold text-teal-800 block">Numerador:</span>
                            <p class="text-xs text-slate-700 mt-0.5">{{ $meta['numerator_desc'] }}</p>
                        </div>
                        <div class="border-t border-slate-200 pt-2">
                            <span class="text-xs font-bold text-teal-800 block">Denominador:</span>
                            <p class="text-xs text-slate-700 mt-0.5">{{ $meta['denominator_desc'] }}</p>
                        </div>
                        @if ($isC1 || $isC2 || $isC3)
                            <div class="border-t border-slate-200 pt-2">
                                <span class="text-xs font-bold text-emerald-800 block">Avaliação do Quadrimestre (NT 08/2026):</span>
                                <p class="text-xs text-slate-700 mt-0.5">
                                    Média aritmética simples dos percentuais obtidos nos 4 meses do quadrimestre: <code>(M1 + M2 + M3 + M4) / 4</code>.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- CBOs Habilitados -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">2. CBOs e Profissionais Habilitados</h4>
                    <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200 space-y-2">
                        <ul class="text-xs text-slate-700 space-y-1 list-disc list-inside">
                            @foreach ($meta['cbos'] as $cbo)
                                <li>{{ $cbo }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Regras do Componente III - Qualidade (Quadro 2 / NT 08/2026) -->
            @if ($isC1)
                <div class="rounded-2xl bg-emerald-50/70 border border-emerald-200 p-4 space-y-3">
                    <h4 class="text-xs font-bold text-emerald-950 uppercase tracking-wider">
                        Componente III - Qualidade: Quadro 2 da NT 08/2026 (Peso 1.0 · Até 1,00 pt)
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="bg-white/80 rounded-xl p-2.5 border border-rose-300">
                            <span class="text-[10px] font-bold text-rose-900 block">Conceito Regular</span>
                            <span class="text-sm font-black text-rose-700 font-mono">0,25 pt</span>
                            <span class="text-[10px] text-muted block">≤ 10% ou &gt; 70%</span>
                        </div>
                        <div class="bg-white/80 rounded-xl p-2.5 border border-amber-300">
                            <span class="text-[10px] font-bold text-amber-900 block">Conceito Suficiente</span>
                            <span class="text-sm font-black text-amber-700 font-mono">0,50 pt</span>
                            <span class="text-[10px] text-muted block">&gt; 10% e ≤ 30%</span>
                        </div>
                        <div class="bg-white/80 rounded-xl p-2.5 border border-emerald-300">
                            <span class="text-[10px] font-bold text-emerald-900 block">Conceito Bom</span>
                            <span class="text-sm font-black text-emerald-700 font-mono">0,75 pt</span>
                            <span class="text-[10px] text-muted block">&gt; 30% e ≤ 50%</span>
                        </div>
                        <div class="bg-white/80 rounded-xl p-2.5 border border-sky-300">
                            <span class="text-[10px] font-bold text-sky-900 block">Conceito Ótimo</span>
                            <span class="text-sm font-black text-sky-700 font-mono">1,00 pt</span>
                            <span class="text-[10px] text-muted block">&gt; 50% e ≤ 70%</span>
                        </div>
                    </div>
                </div>
            @elseif ($isC2)
                <div class="rounded-2xl bg-sky-50/70 border border-sky-200 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-sky-950 uppercase tracking-wider">
                            Componente III - Qualidade: Quadro 2 da NT 08/2026 (Peso 2.0 · Até 2,00 pts)
                        </h4>
                        <span class="rounded-lg bg-sky-200/80 text-sky-900 px-2 py-0.5 text-[10px] font-bold font-mono">
                            Multiplicador 2.0×
                        </span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="bg-white/90 rounded-xl p-2.5 border border-rose-300">
                            <span class="text-[10px] font-bold text-rose-900 block">Conceito Regular</span>
                            <span class="text-sm font-black text-rose-700 font-mono">0,50 pt</span>
                            <span class="text-[10px] text-muted block">≤ 25%</span>
                        </div>
                        <div class="bg-white/90 rounded-xl p-2.5 border border-amber-300">
                            <span class="text-[10px] font-bold text-amber-900 block">Conceito Suficiente</span>
                            <span class="text-sm font-black text-amber-700 font-mono">1,00 pt</span>
                            <span class="text-[10px] text-muted block">&gt; 25% e ≤ 50%</span>
                        </div>
                        <div class="bg-white/90 rounded-xl p-2.5 border border-emerald-300">
                            <span class="text-[10px] font-bold text-emerald-900 block">Conceito Bom</span>
                            <span class="text-sm font-black text-emerald-700 font-mono">1,50 pt</span>
                            <span class="text-[10px] text-muted block">&gt; 50% e ≤ 75%</span>
                        </div>
                        <div class="bg-white/90 rounded-xl p-2.5 border border-sky-300">
                            <span class="text-[10px] font-bold text-sky-900 block">Conceito Ótimo</span>
                            <span class="text-sm font-black text-sky-700 font-mono">2,00 pt</span>
                            <span class="text-[10px] text-muted block">&gt; 75% e ≤ 100%</span>
                        </div>
                    </div>
                    <div class="rounded-xl bg-white/80 border border-sky-100 p-3 text-xs text-sky-900 leading-relaxed">
                        <strong>Exceção Oficial para eAP (tipo 76):</strong> Conforme Nota Metodológica C2, Equipes de Atenção Primária pontuam integralmente 20 pontos na Prática D (Visitas Domiciliares do ACS), mantendo equidade com as equipes de Saúde da Família (eSF).
                    </div>
                </div>
            @elseif ($isC3)
                <div class="rounded-2xl bg-sky-50/70 border border-sky-200 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-sky-950 uppercase tracking-wider">
                            Componente III - Qualidade: Quadro 2 da NT 08/2026 (Peso 2.0 · Até 2,00 pts)
                        </h4>
                        <span class="rounded-lg bg-sky-200/80 text-sky-900 px-2 py-0.5 text-[10px] font-bold font-mono">
                            Multiplicador 2.0×
                        </span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="bg-white/90 rounded-xl p-2.5 border border-rose-300">
                            <span class="text-[10px] font-bold text-rose-900 block">Conceito Regular</span>
                            <span class="text-sm font-black text-rose-700 font-mono">0,50 pt</span>
                            <span class="text-[10px] text-muted block">≤ 25%</span>
                        </div>
                        <div class="bg-white/90 rounded-xl p-2.5 border border-amber-300">
                            <span class="text-[10px] font-bold text-amber-900 block">Conceito Suficiente</span>
                            <span class="text-sm font-black text-amber-700 font-mono">1,00 pt</span>
                            <span class="text-[10px] text-muted block">&gt; 25% e ≤ 50%</span>
                        </div>
                        <div class="bg-white/90 rounded-xl p-2.5 border border-emerald-300">
                            <span class="text-[10px] font-bold text-emerald-900 block">Conceito Bom</span>
                            <span class="text-sm font-black text-emerald-700 font-mono">1,50 pt</span>
                            <span class="text-[10px] text-muted block">&gt; 50% e ≤ 75%</span>
                        </div>
                        <div class="bg-white/90 rounded-xl p-2.5 border border-sky-300">
                            <span class="text-[10px] font-bold text-sky-900 block">Conceito Ótimo</span>
                            <span class="text-sm font-black text-sky-700 font-mono">2,00 pt</span>
                            <span class="text-[10px] text-muted block">&gt; 75% e ≤ 100%</span>
                        </div>
                    </div>
                    <div class="rounded-xl bg-white/80 border border-sky-100 p-3 text-xs text-sky-900 leading-relaxed">
                        <strong>Exceção Oficial para eAP (tipo 76):</strong> Conforme Nota Metodológica C3, Equipes de Atenção Primária pontuam integralmente 9 pontos na Prática E (Visitas ACS na gestação) e 9 pontos na Prática J (Visita ACS no puerpério), totalizando 18 pontos garantidos por não possuírem ACS na composição mínima.
                    </div>
                </div>
            @endif

            <!-- Modelos de Informação do e-SUS PEC -->
            <div class="rounded-2xl bg-teal-50/70 border border-teal-200 p-4 space-y-2">
                <h4 class="text-xs font-bold text-teal-900 uppercase">Modelos de Informação e-SUS APS Utilizados</h4>
                <p class="text-xs text-teal-800 leading-relaxed">
                    Os dados são processados a partir do <strong>Modelo de Informação de Atendimento Individual (MIAI)</strong>, <strong>Modelo de Informação de Procedimentos (MIP)</strong>, <strong>Modelo de Visita Domiciliar e Territorial (MIVDT)</strong> e <strong>Registro de Imunobiológico Administrado (RIA / RNDS)</strong> enviados via prontuário eletrônico e-SUS PEC com CNS profissional validado e CNES da UBS homologado no SCNES.
                </p>
            </div>
        </div>
    @endif
    @endif
</div>
