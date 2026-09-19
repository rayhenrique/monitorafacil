<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Navegação em Abas do Módulo (Relação Nominal, Equipes (Mensal), Caderno Metodológico) -->
    <x-territorial-bonding-tabs
        title="Vínculo e Acompanhamento Territorial"
        subtitle="Componente II · Monitoramento Mensal e Quadrimestral do Vínculo na Atenção Primária"
        :activeTab="$activeTab"
    />

    @if ($activeTab === 'teams')
        <!-- CABEÇALHO DO MONITORAMENTO DE EQUIPES (MENSAL) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-sky-950 tracking-tight flex items-center gap-2">
                    <span>Monitoramento de Vínculo e Acompanhamento - Equipes (Mensal)</span>
                </h1>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1 font-medium">
                    <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                    </svg>
                    <span>Último atendimento registrado em {{ $monthlySummary['last_attendance_date'] }}</span>
                </div>
            </div>

            <div class="w-full sm:w-auto">
                <!-- Botão Busca Avançada -->
                <button
                    type="button"
                    wire:click="openAdvancedModal"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white px-4 py-2.5 text-xs font-bold shadow-xs transition cursor-pointer"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <span>Busca Avançada</span>
                </button>
            </div>
        </div>

        <!-- PAINEL SUPERIOR DE SÍNTESE (REPRODUÇÃO FIEL DA IMAGEM OFICIAL) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="grid grid-cols-2 sm:grid-cols-5 divide-slate-100">
                <!-- Coluna 1: Mês -->
                <div class="p-4 sm:p-5 flex flex-col items-center justify-center text-center col-span-2 sm:col-span-1 bg-slate-50/50 border-b sm:border-b-0 sm:border-r border-slate-100">
                    <span class="text-xs font-semibold text-slate-500 mb-1">Mês</span>
                    <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-mono">
                        {{ $monthlySummary['month_label'] }}
                    </span>
                </div>

                <!-- Coluna 2: Total Ótimo -->
                <div class="p-4 sm:p-5 flex flex-col items-center justify-center text-center border-r sm:border-r-0 border-slate-100">
                    <span class="text-xs font-bold text-sky-600 mb-1">Total Ótimo</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-black text-slate-900 tabular-nums">{{ $monthlySummary['optimal'] }}</span>
                        <span class="text-xs sm:text-sm font-semibold text-slate-500 font-mono">({{ number_format($monthlySummary['optimal_pct'], 2, '.', '') }}%)</span>
                    </div>
                </div>

                <!-- Coluna 3: Total Bom -->
                <div class="p-4 sm:p-5 flex flex-col items-center justify-center text-center sm:border-l sm:border-r border-slate-100">
                    <span class="text-xs font-bold text-emerald-600 mb-1">Total Bom</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-black text-slate-900 tabular-nums">{{ $monthlySummary['good'] }}</span>
                        <span class="text-xs sm:text-sm font-semibold text-slate-500 font-mono">({{ number_format($monthlySummary['good_pct'], 2, '.', '') }}%)</span>
                    </div>
                </div>

                <!-- Coluna 4: Total Suficiente -->
                <div class="p-4 sm:p-5 flex flex-col items-center justify-center text-center border-t sm:border-t-0 border-r sm:border-r-0 border-slate-100">
                    <span class="text-xs font-bold text-amber-600 mb-1">Total Suficiente</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-black text-slate-900 tabular-nums">{{ $monthlySummary['sufficient'] }}</span>
                        <span class="text-xs sm:text-sm font-semibold text-slate-500 font-mono">({{ number_format($monthlySummary['sufficient_pct'], 2, '.', '') }}%)</span>
                    </div>
                </div>

                <!-- Coluna 5: Total Regular -->
                <div class="p-4 sm:p-5 flex flex-col items-center justify-center text-center border-t sm:border-t-0 sm:border-l border-slate-100">
                    <span class="text-xs font-bold text-rose-600 mb-1">Total Regular</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-black text-slate-900 tabular-nums">{{ $monthlySummary['regular'] }}</span>
                        <span class="text-xs sm:text-sm font-semibold text-slate-500 font-mono">({{ number_format($monthlySummary['regular_pct'], 2, '.', '') }}%)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARRA DE FILTROS RESPONSIVA (CNES, Unidade, INE, Equipe, Classificação Final, Paginação) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
            <!-- CNES -->
            <div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterCnes"
                    placeholder="CNES"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition shadow-2xs font-mono"
                />
            </div>

            <!-- Unidade -->
            <div class="col-span-1 sm:col-span-2 lg:col-span-1">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterUnit"
                    placeholder="Unidade"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition shadow-2xs"
                />
            </div>

            <!-- INE -->
            <div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterIne"
                    placeholder="INE"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition shadow-2xs font-mono"
                />
            </div>

            <!-- Equipe -->
            <div class="col-span-1 sm:col-span-2 lg:col-span-1">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterTeam"
                    placeholder="Equipe"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition shadow-2xs"
                />
            </div>

            <!-- Classificação Final -->
            <div>
                <select
                    wire:model.live="filterClassification"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition shadow-2xs font-medium cursor-pointer"
                >
                    <option value="">Classificação Final</option>
                    <option value="ÓTIMO">Ótimo</option>
                    <option value="BOM">Bom</option>
                    <option value="SUFICIENTE">Suficiente</option>
                    <option value="REGULAR">Regular</option>
                </select>
            </div>

            <!-- Paginação & Limpar Filtros -->
            <div class="flex items-center gap-2">
                <select
                    wire:model.live="perPage"
                    class="w-16 rounded-xl border border-slate-300 bg-white px-2 py-2 text-xs text-slate-800 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition shadow-2xs font-bold cursor-pointer"
                >
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="30">30</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

                <!-- Botão Limpar Filtros se houver algum ativo -->
                @if ($filterCnes || $filterUnit || $filterIne || $filterTeam || $filterClassification || $advMinScore !== null || $advMaxScore !== null)
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="flex-1 rounded-xl px-2 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 transition cursor-pointer text-center"
                        title="Limpar todos os filtros aplicados"
                    >
                        Limpar
                    </button>
                @endif
            </div>
        </div>

        <!-- TABELA DE EQUIPES COM AS 14 COLUNAS EXATAS DA IMAGEM E MIN-W RESPONSIVO -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs whitespace-nowrap min-w-[1100px]">
                    <thead>
                        <tr class="bg-slate-50/90 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <th class="py-3.5 px-3 text-center cursor-pointer hover:text-sky-700" wire:click="sortByField('cnes')">
                                CNES
                            </th>
                            <th class="py-3.5 px-3 cursor-pointer hover:text-sky-700" wire:click="sortByField('facility_name')">
                                UNIDADE
                            </th>
                            <th class="py-3.5 px-3 text-center cursor-pointer hover:text-sky-700" wire:click="sortByField('ine')">
                                INE
                            </th>
                            <th class="py-3.5 px-3 cursor-pointer hover:text-sky-700" wire:click="sortByField('team_name')">
                                EQUIPE
                            </th>
                            <th class="py-3.5 px-2.5 text-center">
                                TIPO
                            </th>
                            <th class="py-3.5 px-2.5 text-right font-medium">
                                PARÂMETRO<br>CADASTRO
                            </th>
                            <th class="py-3.5 px-2.5 text-right font-medium cursor-pointer hover:text-sky-700" wire:click="sortByField('linked_registrations')">
                                CADASTROS<br>VINCULADOS
                            </th>
                            <th class="py-3.5 px-2.5 text-center font-medium cursor-pointer hover:text-sky-700" wire:click="sortByField('linked_ratio')">
                                C.VINC/PARAM.<br>(%)
                            </th>
                            <th class="py-3.5 px-2.5 text-right font-medium cursor-pointer hover:text-sky-700" wire:click="sortByField('registration_result')">
                                RESULTADO<br>CADASTRO
                            </th>
                            <th class="py-3.5 px-2.5 text-center font-medium cursor-pointer hover:text-sky-700" wire:click="sortByField('registration_score')">
                                SCORE<br>CADASTRO (X)
                            </th>
                            <th class="py-3.5 px-2.5 text-right font-medium cursor-pointer hover:text-sky-700" wire:click="sortByField('monitoring_result')">
                                RESULTADO<br>ACOMPANHAMENTO
                            </th>
                            <th class="py-3.5 px-2.5 text-center font-medium cursor-pointer hover:text-sky-700" wire:click="sortByField('monitoring_score')">
                                SCORE<br>ACOMPANHAMENTO (Y)
                            </th>
                            <th class="py-3.5 px-2.5 text-center font-medium cursor-pointer hover:text-sky-700" wire:click="sortByField('final_score')">
                                SCORE<br>FINAL (X+Y)
                            </th>
                            <th class="py-3.5 px-3 text-center font-medium cursor-pointer hover:text-sky-700" wire:click="sortByField('final_classification')">
                                CLASSIFICAÇÃO<br>FINAL
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($teams as $team)
                            @php
                                $clsUpper = mb_strtoupper($team->final_classification);
                                $badgeCls = match ($clsUpper) {
                                    'ÓTIMO', 'OTIMO' => 'bg-sky-600 text-white',
                                    'BOM' => 'bg-emerald-600 text-white',
                                    'SUFICIENTE' => 'bg-amber-500 text-white',
                                    default => 'bg-rose-600 text-white',
                                };
                            @endphp
                            <tr class="hover:bg-sky-50/40 transition">
                                <!-- CNES -->
                                <td class="py-3 px-3 text-center font-mono text-slate-600">
                                    {{ $team->cnes }}
                                </td>

                                <!-- UNIDADE -->
                                <td class="py-3 px-3 text-slate-800 font-semibold max-w-[220px] truncate" title="{{ $team->facility_name }}">
                                    {{ $team->facility_name }}
                                </td>

                                <!-- INE -->
                                <td class="py-3 px-3 text-center font-mono text-slate-600">
                                    {{ $team->ine }}
                                </td>

                                <!-- EQUIPE -->
                                <td class="py-3 px-3 text-slate-800 font-semibold max-w-[220px] truncate" title="{{ $team->team_name }}">
                                    {{ $team->team_name }}
                                </td>

                                <!-- TIPO -->
                                <td class="py-3 px-2.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-600 text-white">
                                        {{ $team->team_type ?: 'ESF' }}
                                    </span>
                                </td>

                                <!-- PARÂMETRO CADASTRO -->
                                <td class="py-3 px-2.5 text-right font-mono text-slate-600">
                                    {{ $team->parameter ?? 2500 }}
                                </td>

                                <!-- CADASTROS VINCULADOS -->
                                <td class="py-3 px-2.5 text-right font-mono font-bold text-slate-800 tabular-nums">
                                    {{ $team->linked_registrations ?? 0 }}
                                </td>

                                <!-- C.VINC/PARAM. (%) -->
                                <td class="py-3 px-2.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-600 text-white font-mono tabular-nums">
                                        {{ number_format($team->linked_ratio ?? 0, 2, '.', '') }}%
                                    </span>
                                </td>

                                <!-- RESULTADO CADASTRO -->
                                <td class="py-3 px-2.5 text-right font-mono font-semibold text-slate-700 tabular-nums">
                                    {{ number_format($team->registration_result ?? 0, 2, '.', '') }}
                                </td>

                                <!-- SCORE CADASTRO (X) -->
                                <td class="py-3 px-2.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-500 text-white font-mono tabular-nums">
                                        {{ number_format($team->registration_score, 2, '.', '') }}
                                    </span>
                                </td>

                                <!-- RESULTADO ACOMPANHAMENTO -->
                                <td class="py-3 px-2.5 text-right font-mono font-semibold text-slate-700 tabular-nums">
                                    {{ number_format($team->monitoring_result ?? 0, 2, '.', '') }}
                                </td>

                                <!-- SCORE ACOMPANHAMENTO (Y) -->
                                <td class="py-3 px-2.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-500 text-white font-mono tabular-nums">
                                        {{ number_format($team->monitoring_score, 2, '.', '') }}
                                    </span>
                                </td>

                                <!-- SCORE FINAL (X+Y) -->
                                <td class="py-3 px-2.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-500 text-white font-mono tabular-nums">
                                        {{ number_format($team->final_score, 2, '.', '') }}
                                    </span>
                                </td>

                                <!-- CLASSIFICAÇÃO FINAL -->
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-[11px] font-bold {{ $badgeCls }}">
                                        {{ mb_convert_case($team->final_classification, MB_CASE_TITLE, 'UTF-8') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="py-10 text-center text-slate-400">
                                    Nenhuma equipe encontrada para os filtros selecionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Rodapé informativo de totais -->
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500 gap-2">
                <div>
                    Exibindo <span class="font-bold text-slate-800">{{ $teams->count() }}</span> de <span class="font-bold text-slate-800">{{ $totalTeamsFound }}</span> equipes cadastradas
                </div>
                <div class="font-medium">
                    Município de Teotônio Vilela/AL · 19 Equipes de Saúde da Família Homologadas
                </div>
            </div>
        </div>
    @endif

    <!-- SEÇÃO: CADERNO METODOLÓGICO RESPONSIVO (ALINHADO À NOTA TÉCNICA Nº 30/2025 & PORTARIA SAPS/MS Nº 161/2024) -->
    @if ($activeTab === 'guide')
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-line shadow-panel p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8">
            <!-- Cabeçalho Oficial Responsivo -->
            <div class="border-b border-line pb-4 sm:pb-5">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-[10px] sm:text-xs font-bold text-teal-800 uppercase tracking-wider mb-2">
                    <span class="bg-teal-50 px-2.5 py-0.5 rounded border border-teal-200">Nota Técnica nº 30/2025-CGESCO/DESCO/SAPS/MS</span>
                    <span class="hidden sm:inline text-slate-300">·</span>
                    <span class="bg-slate-50 px-2 py-0.5 rounded border border-slate-200 text-slate-600">Processo SEI nº 25000.178857/2024-41</span>
                    <span class="hidden sm:inline text-slate-300">·</span>
                    <span class="bg-slate-50 px-2 py-0.5 rounded border border-slate-200 text-slate-600">Portaria SAPS/MS nº 161/2024</span>
                </div>
                <h3 class="text-xl sm:text-2xl font-black text-ink tracking-tight leading-tight">
                    Caderno Metodológico · Componente Vínculo e Acompanhamento Territorial (CVAT)
                </h3>
                <p class="text-xs sm:text-sm text-muted mt-1.5 leading-relaxed">
                    Diretrizes oficiais, fórmulas matemáticas de cálculo dos índices ponderados, critérios de corte temporal, pontuação e faixas de repasse do incentivo financeiro federal na Atenção Primária à Saúde.
                </p>
            </div>

            <!-- Parâmetro Populacional e Território -->
            <div class="p-4 sm:p-5 rounded-2xl bg-teal-50/60 border border-teal-200/80 space-y-3">
                <div class="flex items-start sm:items-center gap-2.5">
                    <div class="flex h-7 w-7 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-xl bg-teal-700 text-white font-black text-xs">
                        1
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold text-teal-950 leading-snug">
                        1. Parâmetro Populacional Normativo e Território (Art. 4º da Portaria SAPS nº 161/2024)
                    </h4>
                </div>
                <p class="text-xs sm:text-sm text-teal-900 leading-relaxed">
                    O Ministério da Saúde estabelece o parâmetro populacional fixo por tipo de equipe para o cálculo do denominador dos índices de cadastro e acompanhamento:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 text-xs">
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-teal-200 shadow-2xs">
                        <span class="text-slate-500 block text-[11px] font-semibold">Equipe de Saúde da Família (eSF)</span>
                        <span class="text-base sm:text-lg font-black text-teal-900 tabular-nums">2.500 pessoas / equipe</span>
                    </div>
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-teal-200 shadow-2xs">
                        <span class="text-slate-500 block text-[11px] font-semibold">Equipe de Atenção Primária (eAP 30h)</span>
                        <span class="text-base sm:text-lg font-black text-teal-900 tabular-nums">2.000 pessoas / equipe</span>
                    </div>
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-teal-200 shadow-2xs">
                        <span class="text-slate-500 block text-[11px] font-semibold">Equipe de Atenção Primária (eAP 20h)</span>
                        <span class="text-base sm:text-lg font-black text-teal-900 tabular-nums">1.500 pessoas / equipe</span>
                    </div>
                </div>
                <p class="text-[11px] sm:text-xs text-teal-800 italic leading-relaxed">
                    <strong>Parâmetro Municipal de Teotônio Vilela/AL:</strong> Com 19 equipes de Saúde da Família (eSF) credenciadas e homologadas no SCNES, a população alvo normativa do município é de <strong>47.500 munícipes</strong> (19 &times; 2.500).
                </p>
            </div>

            <!-- Dimensão Cadastro (Índice X) -->
            <div class="p-4 sm:p-6 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
                    <div class="flex items-start sm:items-center gap-2.5">
                        <div class="flex h-7 w-7 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-xl bg-teal-800 text-white font-black text-xs">
                            2
                        </div>
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-slate-800">2. Dimensão Cadastro (Índice X · Escore de até 3,00 pontos)</h4>
                            <p class="text-[10px] sm:text-[11px] text-slate-500">MICI e MICDT com atualização nos últimos 24 meses (contados da data final do quadrimestre)</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-xl bg-teal-100 text-teal-900 text-xs font-black self-start sm:self-auto">Peso 3,0</span>
                </div>

                <div class="text-xs sm:text-sm text-slate-700 leading-relaxed space-y-2">
                    <p>
                        A Dimensão Cadastro afere o grau de conhecimento e cadastramento territorial da população pela equipe de saúde. São elegíveis os cidadãos vinculados à equipe no e-SUS PEC com cadastro individual atualizado em até 24 meses.
                    </p>
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-xs leading-relaxed">
                        <strong>Critérios de Exclusão da Dimensão Cadastro (Item 3.4 da NT 30/2025):</strong> São desconsiderados cadastros marcados com <em>"Fora de Área (FA)"</em> ou <em>"Mudança de Território (Mudou-se)"</em>. Cadastros rápidos simplificados não pontuam (fator 0).
                    </div>
                </div>

                <!-- Ponderações e Fórmula -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-xs">
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200 space-y-2">
                        <span class="font-bold text-slate-800 block uppercase tracking-wider text-[11px]">Ponderações do Cadastro</span>
                        <ul class="space-y-1.5 text-slate-600">
                            <li class="flex items-center justify-between gap-2">
                                <span class="truncate">Pessoa apenas com MICI atualizado (sem domicílio):</span>
                                <strong class="font-mono text-teal-800 bg-teal-50 px-2 py-0.5 rounded shrink-0">&times; 0,75</strong>
                            </li>
                            <li class="flex items-center justify-between gap-2">
                                <span class="truncate">Pessoa com MICI e MICDT ambos atualizados:</span>
                                <strong class="font-mono text-teal-800 bg-teal-50 px-2 py-0.5 rounded shrink-0">&times; 1,50</strong>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200 space-y-2">
                        <span class="font-bold text-slate-800 block uppercase tracking-wider text-[11px]">Fórmula Oficial do Índice X</span>
                        <div class="p-3 rounded-xl bg-slate-900 text-emerald-300 font-mono text-[11px] sm:text-xs leading-relaxed overflow-x-auto whitespace-nowrap">
                            X = [(MICI_apenas &times; 0,75) + (MICI_e_MICDT &times; 1,50)] / 47.500 &times; 100
                        </div>
                        <p class="text-[10px] sm:text-[11px] text-slate-500">O resultado percentual é confrontado com os cortes ministeriais para definir o Escore X.</p>
                    </div>
                </div>

                <!-- Tabela de Conversão do Escore X Responsiva -->
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                    <table class="w-full text-left text-xs min-w-[550px]">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-200">
                                <th class="py-2.5 px-4">Intervalo do Índice X</th>
                                <th class="py-2.5 px-4 text-center">Escore Atribuído (pts)</th>
                                <th class="py-2.5 px-4 text-center">Classificação</th>
                                <th class="py-2.5 px-4">Interpretação da Capacidade Cadastral</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="py-2.5 px-4 font-mono font-bold text-teal-900">X &ge; 100%</td>
                                <td class="py-2.5 px-4 text-center font-bold text-emerald-600">3,00 pts</td>
                                <td class="py-2.5 px-4 text-center"><span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">Ótimo</span></td>
                                <td class="py-2.5 px-4 text-slate-600">Território plenamente cadastrado e georreferenciado ao domicílio</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-mono font-bold text-teal-900">75% &le; X &lt; 100%</td>
                                <td class="py-2.5 px-4 text-center font-bold text-teal-700">2,25 pts</td>
                                <td class="py-2.5 px-4 text-center"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">Bom</span></td>
                                <td class="py-2.5 px-4 text-slate-600">Boa cobertura cadastral com oportunidades de consolidação de domicílios</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-mono font-bold text-teal-900">50% &le; X &lt; 75%</td>
                                <td class="py-2.5 px-4 text-center font-bold text-amber-600">1,50 pts</td>
                                <td class="py-2.5 px-4 text-center"><span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">Suficiente</span></td>
                                <td class="py-2.5 px-4 text-slate-600">Volume considerável de cadastros desatualizados (&gt; 24 meses) ou sem MICDT</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-mono font-bold text-teal-900">X &lt; 50%</td>
                                <td class="py-2.5 px-4 text-center font-bold text-rose-600">0,75 pts</td>
                                <td class="py-2.5 px-4 text-center"><span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">Regular</span></td>
                                <td class="py-2.5 px-4 text-slate-600">Necessidade premente de mutirão cadastral e busca ativa pelos ACS</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Dimensão Acompanhamento (Índice Y) -->
            <div class="p-4 sm:p-6 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
                    <div class="flex items-start sm:items-center gap-2.5">
                        <div class="flex h-7 w-7 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-xl bg-blue-800 text-white font-black text-xs">
                            3
                        </div>
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-slate-800">3. Dimensão Acompanhamento (Índice Y · Escore de até 7,00 pontos)</h4>
                            <p class="text-[10px] sm:text-[11px] text-slate-500">Contatos assistenciais contínuos no território nos últimos 12 meses anteriores ao fim do quadrimestre</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-xl bg-blue-100 text-blue-900 text-xs font-black self-start sm:self-auto">Peso 7,0</span>
                </div>

                <!-- Definição de Pessoa Acompanhada -->
                <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-blue-200 space-y-2 text-xs">
                    <span class="font-bold text-blue-950 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Definição Oficial de Pessoa Acompanhada (Item 2.6.4 da NT nº 30/2025)
                    </span>
                    <p class="text-slate-700 leading-relaxed text-xs sm:text-sm">
                        É considerada acompanhada a pessoa cadastrada e vinculada à equipe que tiver <strong>mais de um contato assistencial no período de um ano</strong> (12 meses anteriores à data final do quadrimestre avaliado), sendo <strong>obrigatório que ao menos um contato seja uma Prática de Cuidado</strong>:
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-[11px]">
                        <div class="bg-blue-50/60 p-3 rounded-lg border border-blue-100 space-y-1">
                            <strong class="text-blue-900 block">Práticas de Cuidado Obrigatórias (&ge; 1 contato):</strong>
                            <ul class="list-disc list-inside text-blue-800 space-y-0.5">
                                <li>Atendimento Individual Clínico (Médico / Enfermeiro)</li>
                                <li>Atendimento Individual Odontológico</li>
                                <li>Visita Domiciliar do Agente Comunitário de Saúde (ACS)</li>
                                <li>Atividade Coletiva no território</li>
                            </ul>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 space-y-1">
                            <strong class="text-slate-800 block">Demais Contatos Válidos (&ge; 2º contato):</strong>
                            <ul class="list-disc list-inside text-slate-600 space-y-0.5">
                                <li>Outra prática de cuidado médica/odonto/ACS</li>
                                <li>Registro de Procedimento Ambulatorial ou de Enfermagem</li>
                                <li>Registro de Vacinação no PEC / SIPNI</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Critérios de Vulnerabilidade e Ponderação Y -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-xs">
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200 space-y-2">
                        <span class="font-bold text-slate-800 block uppercase tracking-wider text-[11px]">Ponderadores de Vulnerabilidade (Item 3.10)</span>
                        <ul class="space-y-1.5 text-slate-600">
                            <li class="flex items-center justify-between gap-2">
                                <span class="truncate">Sem critério de vulnerabilidade:</span>
                                <strong class="font-mono text-blue-800 bg-blue-50 px-2 py-0.5 rounded shrink-0">&times; 1,00</strong>
                            </li>
                            <li class="flex items-center justify-between gap-2">
                                <span class="truncate">Idoso (&ge;60a) OU Criança (&lt;5a incompletos):</span>
                                <strong class="font-mono text-blue-800 bg-blue-50 px-2 py-0.5 rounded shrink-0">&times; 1,20</strong>
                            </li>
                            <li class="flex items-center justify-between gap-2">
                                <span class="truncate">Beneficiário BPC OU Bolsa Família (PBF):</span>
                                <strong class="font-mono text-blue-800 bg-blue-50 px-2 py-0.5 rounded shrink-0">&times; 1,30</strong>
                            </li>
                            <li class="flex items-center justify-between gap-2">
                                <span class="truncate">Vulnerabilidade Dupla (Idoso/Criança + BPC/PBF):</span>
                                <strong class="font-mono text-purple-800 bg-purple-50 px-2 py-0.5 rounded shrink-0">&times; 2,50</strong>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200 space-y-2">
                        <span class="font-bold text-slate-800 block uppercase tracking-wider text-[11px]">Fórmula Oficial do Índice Y</span>
                        <div class="p-3 rounded-xl bg-slate-900 text-sky-300 font-mono text-[11px] sm:text-xs leading-relaxed overflow-x-auto whitespace-nowrap">
                            Y = [(Acomp_sem &times; 1,0) + (Acomp_idade &times; 1,2) + (Acomp_benef &times; 1,3) + (Acomp_dupla &times; 2,5)] / 47.500 &times; 100
                        </div>
                        <p class="text-[10px] sm:text-[11px] text-slate-500">A meta ministerial para a pontuação máxima é atingir índice ponderado Y &ge; 50% da população parâmetro.</p>
                    </div>
                </div>

                <!-- Tabela de Conversão do Escore Y Responsiva -->
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                    <table class="w-full text-left text-xs min-w-[550px]">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-200">
                                <th class="py-2.5 px-4">Intervalo do Índice Y</th>
                                <th class="py-2.5 px-4 text-center">Escore Atribuído (pts)</th>
                                <th class="py-2.5 px-4 text-center">Classificação</th>
                                <th class="py-2.5 px-4">Interpretação da Assistência Territorial</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="py-2.5 px-4 font-mono font-bold text-blue-900">Y &ge; 50%</td>
                                <td class="py-2.5 px-4 text-center font-bold text-emerald-600">7,00 pts</td>
                                <td class="py-2.5 px-4 text-center"><span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">Ótimo</span></td>
                                <td class="py-2.5 px-4 text-slate-600">Acompanhamento territorial intensivo e equânime com foco prioritário em vulneráveis</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-mono font-bold text-blue-900">35% &le; Y &lt; 50%</td>
                                <td class="py-2.5 px-4 text-center font-bold text-teal-700">5,25 pts</td>
                                <td class="py-2.5 px-4 text-center"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">Bom</span></td>
                                <td class="py-2.5 px-4 text-slate-600">Bom fluxo de visitas e consultas com margem de expansão nas microáreas</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-mono font-bold text-blue-900">20% &le; Y &lt; 35%</td>
                                <td class="py-2.5 px-4 text-center font-bold text-amber-600">3,50 pts</td>
                                <td class="py-2.5 px-4 text-center"><span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">Suficiente</span></td>
                                <td class="py-2.5 px-4 text-slate-600">Muitos cidadãos sem o segundo contato assistencial no período de 12 meses</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-mono font-bold text-blue-900">Y &lt; 20%</td>
                                <td class="py-2.5 px-4 text-center font-bold text-rose-600">1,75 pts</td>
                                <td class="py-2.5 px-4 text-center"><span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">Regular</span></td>
                                <td class="py-2.5 px-4 text-slate-600">Descontinuidade do cuidado territorial; risco de perda de incentivo financeiro</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bônus de Satisfação do Usuário (Item 3.12 da NT 30/2025) -->
            <div class="p-4 sm:p-5 rounded-2xl bg-indigo-50/60 border border-indigo-200 space-y-3">
                <div class="flex items-start sm:items-center gap-2.5">
                    <div class="flex h-7 w-7 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-xl bg-indigo-700 text-white font-black text-xs">
                        4
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold text-indigo-950 leading-snug">
                        4. Bonificação por Satisfação do Usuário no Meu SUS Digital (Item 3.12)
                    </h4>
                </div>
                <p class="text-xs sm:text-sm text-indigo-900 leading-relaxed">
                    O Ministério da Saúde introduziu uma bonificação adicional na Dimensão Acompanhamento para estimular a avaliação dos atendimentos pelos munícipes no aplicativo <strong>Meu SUS Digital</strong>:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-indigo-200 shadow-2xs">
                        <span class="text-indigo-900 font-bold block mb-1">Avaliações &lt; 5% dos Atendimentos</span>
                        <span class="text-base sm:text-lg font-black text-indigo-700">+0,15 ponto de bônus</span>
                        <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Acrescido ao Escore Y da equipe no quadrimestre.</p>
                    </div>
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-indigo-200 shadow-2xs">
                        <span class="text-indigo-900 font-bold block mb-1">Avaliações &ge; 5% dos Atendimentos</span>
                        <span class="text-base sm:text-lg font-black text-emerald-700">+0,30 ponto de bônus</span>
                        <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Acrescido ao Escore Y da equipe no quadrimestre.</p>
                    </div>
                </div>
                <p class="text-[11px] sm:text-xs text-indigo-800 italic">
                    <em>* Nota: A aplicação do bônus de satisfação não pode ultrapassar o teto máximo de 7,00 pontos na Dimensão Acompanhamento.</em>
                </p>
            </div>

            <!-- Escore Final e Faixas de Repasse Financeiro -->
            <div class="p-4 sm:p-6 rounded-2xl border border-line bg-white space-y-4">
                <div class="flex items-start sm:items-center gap-2.5">
                    <div class="flex h-7 w-7 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white font-black text-xs">
                        5
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-ink">5. Escore Final do Componente II e Repasse Financeiro Federal (Art. 7º da Portaria SAPS nº 161/2024)</h4>
                        <p class="text-[10px] sm:text-[11px] text-muted">Soma dos escores das duas dimensões: Escore Final = Escore X + Escore Y (escala de 0,00 a 10,00 pontos)</p>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-line">
                    <table class="w-full text-left text-xs min-w-[580px] border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-line">
                                <th class="py-3 px-4">Faixa do Escore Final (X + Y)</th>
                                <th class="py-3 px-4 text-center">Classificação Oficial</th>
                                <th class="py-3 px-4 text-center">Percentual de Repasse</th>
                                <th class="py-3 px-4">Impacto no Financiamento da Atenção Primária</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr class="bg-blue-50/40 font-semibold">
                                <td class="py-3.5 px-4 font-mono text-blue-950 font-bold text-sm">&gt; 8,50 pts</td>
                                <td class="py-3.5 px-4 text-center"><span class="px-3 py-1 rounded-full bg-blue-600 text-white font-bold text-xs">ÓTIMO</span></td>
                                <td class="py-3.5 px-4 text-center font-bold text-blue-900 text-sm">100% Repasse</td>
                                <td class="py-3.5 px-4 text-blue-900">Transferência fundo a fundo do valor integral do incentivo fixado</td>
                            </tr>
                            <tr class="bg-emerald-50/40 font-semibold">
                                <td class="py-3.5 px-4 font-mono text-emerald-950 font-bold text-sm">7,00 a 8,50 pts</td>
                                <td class="py-3.5 px-4 text-center"><span class="px-3 py-1 rounded-full bg-emerald-600 text-white font-bold text-xs">BOM</span></td>
                                <td class="py-3.5 px-4 text-center font-bold text-emerald-900 text-sm">100% Repasse</td>
                                <td class="py-3.5 px-4 text-emerald-900">Transferência fundo a fundo do valor integral do incentivo fixado</td>
                            </tr>
                            <tr class="bg-amber-50/40 font-semibold">
                                <td class="py-3.5 px-4 font-mono text-amber-950 font-bold text-sm">5,00 a 6,99 pts</td>
                                <td class="py-3.5 px-4 text-center"><span class="px-3 py-1 rounded-full bg-amber-500 text-white font-bold text-xs">SUFICIENTE</span></td>
                                <td class="py-3.5 px-4 text-center font-bold text-amber-900 text-sm">75% Repasse</td>
                                <td class="py-3.5 px-4 text-amber-900">Retenção de 25% do incentivo federal por desempenho insuficiente</td>
                            </tr>
                            <tr class="bg-rose-50/40 font-semibold">
                                <td class="py-3.5 px-4 font-mono text-rose-950 font-bold text-sm">&lt; 5,00 pts</td>
                                <td class="py-3.5 px-4 text-center"><span class="px-3 py-1 rounded-full bg-rose-600 text-white font-bold text-xs">REGULAR</span></td>
                                <td class="py-3.5 px-4 text-center font-bold text-rose-900 text-sm">50% Repasse</td>
                                <td class="py-3.5 px-4 text-rose-900">Retenção de 50% do incentivo federal; equipe sob monitoramento crítico</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Regras de Desempate de Vinculação de Equipe (Item 3.15 da NT 30/2025) -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-start sm:items-center gap-2.5">
                    <div class="flex h-7 w-7 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-xl bg-slate-700 text-white font-black text-xs">
                        6
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900">6. Critérios Oficiais de Desempate de Vínculo de Cidadão (Item 3.15)</h4>
                </div>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                    Quando um mesmo cidadão possuir cadastros ou atendimentos associados a mais de uma equipe no período de apuração, o Ministério da Saúde aplica a seguinte ordem sucessiva e eliminatória para fixar a equipe titular de vínculo:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 text-xs">
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-teal-700 font-bold block mb-1">1º Critério (Volume)</span>
                        <strong class="text-slate-800 block text-xs">Maior Número de Atendimentos</strong>
                        <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Equipe que prestou o maior número de atendimentos individuais no ano.</p>
                    </div>
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-teal-700 font-bold block mb-1">2º Critério (Recência)</span>
                        <strong class="text-slate-800 block text-xs">Atendimento Mais Recente</strong>
                        <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Equipe cujo último atendimento individual possui a data mais recente.</p>
                    </div>
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-teal-700 font-bold block mb-1">3º Critério (Cadastro)</span>
                        <strong class="text-slate-800 block text-xs">Cadastro Mais Atualizado</strong>
                        <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Equipe com o cadastro individual (MICI) atualizado mais recente.</p>
                    </div>
                </div>
            </div>

            <!-- Rodapé e Referências Oficiais Responsivo -->
            <div class="pt-4 border-t border-line flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-slate-500">
                <div class="space-y-1">
                    <p><strong>Base Oficial:</strong> Nota Técnica nº 30/2025-CGESCO/DESCO/SAPS/MS · Ministério da Saúde.</p>
                    <p><strong>Sistemas Envolvidos:</strong> e-SUS APS PEC (DW Local) &middot; Siaps (Sistema de Informação para a Atenção Primária à Saúde).</p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        href="https://sisaps.saude.gov.br"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold transition text-xs shadow-2xs"
                    >
                        <span>Portal Siaps Oficial</span>
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE BUSCA AVANÇADA INTERATIVO RESPONSIVO -->
    @if ($showAdvancedModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs animate-fade-in" role="dialog" aria-modal="true" aria-labelledby="modal-advanced-title">
            <div class="relative w-full max-w-lg rounded-2xl sm:rounded-3xl bg-white p-4 sm:p-6 shadow-2xl border border-slate-200 space-y-4 sm:space-y-5 animate-scale-up max-h-[90vh] overflow-y-auto" @click.outside="$wire.closeAdvancedModal()">
                <!-- Cabeçalho do Modal -->
                <div class="flex items-start justify-between border-b border-slate-100 pb-3 sm:pb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-100 text-sky-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <div>
                            <h2 id="modal-advanced-title" class="text-base font-bold text-slate-900">Busca Avançada de Equipes</h2>
                            <p class="text-xs text-slate-500">Refine o monitoramento com filtros combinados de notas e parâmetros</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeAdvancedModal"
                        class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition cursor-pointer"
                        aria-label="Fechar"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Formulário de Filtros Avançados -->
                <div class="space-y-4 text-xs">
                    <!-- Faixa de Score Final -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Faixa de Score Final (0,00 a 10,00 pts):</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <span class="text-[11px] text-slate-500 block mb-0.5">Nota Mínima</span>
                                <input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="10"
                                    wire:model="advMinScore"
                                    placeholder="Ex: 7.0"
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono"
                                />
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-500 block mb-0.5">Nota Máxima</span>
                                <input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="10"
                                    wire:model="advMaxScore"
                                    placeholder="Ex: 10.0"
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Classificação Final -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Classificação Final:</label>
                        <select wire:model="advClassification" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-medium">
                            <option value="">Todas as classificações</option>
                            <option value="ÓTIMO">Ótimo (&gt; 8,5 pts)</option>
                            <option value="BOM">Bom (7,0 a 8,5 pts)</option>
                            <option value="SUFICIENTE">Suficiente (5,0 a 6,99 pts)</option>
                            <option value="REGULAR">Regular (&lt; 5,0 pts)</option>
                        </select>
                    </div>

                    <!-- Tipo de Equipe -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Tipo de Equipe:</label>
                        <select wire:model="advTeamType" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-medium">
                            <option value="">Todas (eSF, eAP)</option>
                            <option value="ESF">Equipe de Saúde da Família (eSF)</option>
                        </select>
                    </div>
                </div>

                <!-- Botões de Ação do Modal -->
                <div class="flex items-center justify-between gap-2.5 pt-4 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition cursor-pointer"
                    >
                        Limpar todos os filtros
                    </button>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="closeAdvancedModal"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            wire:click="applyAdvancedSearch"
                            class="px-5 py-2 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-xs transition cursor-pointer"
                        >
                            Aplicar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
