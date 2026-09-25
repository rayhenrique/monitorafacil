<div class="app-page space-y-6">
    @php
        $level = $snapshot?->performance_level ?? 'regular';
        $score = $snapshot?->score_percent !== null ? (float) $snapshot->score_percent : null;

        $badgeStyles = match ($level) {
            'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
            'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
            default => 'bg-rose-100 text-rose-800 border-rose-300',
        };

        $levelLabel = match ($level) {
            'otimo' => 'Desempenho Ótimo',
            'bom' => 'Desempenho Bom',
            'suficiente' => 'Desempenho Suficiente',
            default => 'Desempenho Regular',
        };

        $barColor = match ($level) {
            'otimo' => 'bg-sky-500',
            'bom' => 'bg-emerald-500',
            'suficiente' => 'bg-amber-500',
            default => 'bg-rose-500',
        };
    @endphp

    <!-- Breadcrumb e Título Superior -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#58716b] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-teal-700 transition">Visão Geral</a>
                <span>/</span>
                <a href="{{ route('oral-health.overview') }}" class="hover:text-teal-700 transition">Saúde Bucal</a>
                <span>/</span>
                <span class="text-teal-800 font-medium">{{ strtoupper($indicator) }}</span>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#16302c]">
                    {{ $meta['code'] }} · {{ $meta['panel_title'] }}
                </h1>
                <span class="rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $badgeStyles }}">
                    {{ $levelLabel }}
                </span>
            </div>
            <p class="mt-1 text-xs sm:text-sm text-[#58716b]">
                {{ $meta['full_title'] }}
            </p>
        </div>

        <!-- Botões e Seletores -->
        <div class="flex items-center gap-2 shrink-0">
            <a
                href="{{ route('oral-health.nominal') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-[#dce6e2] bg-white px-3 py-2 text-xs font-semibold text-[#16302c] shadow-2xs hover:bg-slate-50 transition"
            >
                <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <span>Busca Geral</span>
            </a>

            <a
                href="{{ route('oral-health.overview', ['ano' => $year, 'quadrimestre' => $quarter]) }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-[#dce6e2] bg-white px-3 py-2 text-xs font-semibold text-[#16302c] shadow-2xs hover:bg-slate-50 transition"
            >
                <svg class="h-4 w-4 text-[#58716b]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Voltar ao Painel</span>
            </a>
        </div>
    </div>

    <!-- Navegação de Abas do Indicador -->
    <div class="border-b border-[#dce6e2]">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button
                type="button"
                wire:click="setTab('monthly_summary')"
                class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition cursor-pointer {{ $activeTab === 'monthly_summary' ? 'border-teal-700 text-teal-800' : 'border-transparent text-[#58716b] hover:text-[#16302c] hover:border-slate-300' }}"
            >
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5" />
                    </svg>
                    <span>Resumo das Equipes</span>
                </span>
            </button>

            <button
                type="button"
                wire:click="setTab('nominal')"
                class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition cursor-pointer {{ $activeTab === 'nominal' ? 'border-teal-700 text-teal-800' : 'border-transparent text-[#58716b] hover:text-[#16302c] hover:border-slate-300' }}"
            >
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <span>Lista Nominal e Busca Ativa</span>
                    @if ($nominalMetrics['total'] > 0)
                        <span class="rounded-full bg-teal-100 px-2 py-0.5 text-[11px] font-bold text-teal-800">
                            {{ number_format($nominalMetrics['total'], 0, ',', '.') }}
                        </span>
                    @endif
                </span>
            </button>
        </nav>
    </div>

    @if ($activeTab === 'monthly_summary')
        <!-- ========================================================================= -->
        <!-- ABA 1: RESUMO MENSAL DAS EQUIPES (PADRÃO CONSOLIDADO C1-C5)               -->
        <!-- ========================================================================= -->
        <div class="space-y-6">
            <!-- Header do Indicador com Badge de Competência -->
            <div class="rounded-2xl border border-[#dce6e2] bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-teal-700">{{ $meta['category'] }}</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-[#16302c] mt-0.5">{{ $meta['panel_title'] }}</h2>
                        <p class="text-xs sm:text-sm text-[#58716b] mt-1">{{ $meta['objective'] }}</p>
                    </div>

                    <div class="sm:text-right shrink-0">
                        <span class="text-xs text-[#58716b] block">Competência</span>
                        <span class="text-2xl sm:text-3xl font-black text-[#16302c] tracking-tight">
                            {{ $year }} / Q{{ $quarter }}
                        </span>
                    </div>
                </div>

                <!-- Métricas do Consolidado Municipal -->
                <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t border-slate-100">
                    <div class="rounded-xl bg-[#f5f7f6] p-3 text-center">
                        <span class="text-[11px] font-semibold text-[#58716b] uppercase block">Numerador</span>
                        <span class="text-lg font-black text-[#16302c]">{{ number_format($snapshot?->numerator ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="rounded-xl bg-[#f5f7f6] p-3 text-center">
                        <span class="text-[11px] font-semibold text-[#58716b] uppercase block">Denominador</span>
                        <span class="text-lg font-black text-[#16302c]">{{ number_format($snapshot?->denominator ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="rounded-xl bg-[#f5f7f6] p-3 text-center">
                        <span class="text-[11px] font-semibold text-[#58716b] uppercase block">Taxa Municipal</span>
                        <span class="text-lg font-black text-[#16302c]">
                            {{ $score !== null ? number_format($score, 2, ',', '.') . (in_array($indicator, ['b2', 'b3', 'b5', 'b6']) ? '%' : '') : '—' }}
                        </span>
                    </div>
                    <div class="rounded-xl bg-[#f5f7f6] p-3 text-center">
                        <span class="text-[11px] font-semibold text-[#58716b] uppercase block">Pontos CVAT</span>
                        <span class="text-lg font-black text-teal-800">
                            {{ number_format($snapshot?->good_practices_breakdown['points'] ?? 0, 2, ',', '.') }} pts
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card: Distribuição das Equipes por Classificação (4 Quadrantes) -->
            <div class="rounded-2xl border border-[#dce6e2] bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-[#16302c]">Distribuição das Equipes por Classificação</h3>
                        <p class="text-xs text-[#58716b]">Total de equipes avaliadas: {{ $teamClassifications['total'] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
                    <!-- Regular -->
                    <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-rose-800">Regular</span>
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                        </div>
                        <div class="text-3xl font-black text-rose-900 mt-2">
                            {{ $teamClassifications['regular'] }}
                        </div>
                        <span class="text-xs text-rose-700/80 mt-0.5 block">
                            {{ $teamClassifications['total'] > 0 ? number_format(($teamClassifications['regular'] / $teamClassifications['total']) * 100, 1, ',', '.') : 0 }}% do total
                        </span>
                        <div class="w-full bg-rose-200/70 rounded-full h-1.5 mt-3 overflow-hidden">
                            <div class="bg-rose-500 h-1.5 rounded-full transition-all" style="width: {{ $teamClassifications['total'] > 0 ? ($teamClassifications['regular'] / $teamClassifications['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <!-- Suficiente -->
                    <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-amber-800">Suficiente</span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        </div>
                        <div class="text-3xl font-black text-amber-900 mt-2">
                            {{ $teamClassifications['suficiente'] }}
                        </div>
                        <span class="text-xs text-amber-700/80 mt-0.5 block">
                            {{ $teamClassifications['total'] > 0 ? number_format(($teamClassifications['suficiente'] / $teamClassifications['total']) * 100, 1, ',', '.') : 0 }}% do total
                        </span>
                        <div class="w-full bg-amber-200/70 rounded-full h-1.5 mt-3 overflow-hidden">
                            <div class="bg-amber-500 h-1.5 rounded-full transition-all" style="width: {{ $teamClassifications['total'] > 0 ? ($teamClassifications['suficiente'] / $teamClassifications['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <!-- Bom -->
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-emerald-800">Bom</span>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        </div>
                        <div class="text-3xl font-black text-emerald-900 mt-2">
                            {{ $teamClassifications['bom'] }}
                        </div>
                        <span class="text-xs text-emerald-700/80 mt-0.5 block">
                            {{ $teamClassifications['total'] > 0 ? number_format(($teamClassifications['bom'] / $teamClassifications['total']) * 100, 1, ',', '.') : 0 }}% do total
                        </span>
                        <div class="w-full bg-emerald-200/70 rounded-full h-1.5 mt-3 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: {{ $teamClassifications['total'] > 0 ? ($teamClassifications['bom'] / $teamClassifications['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <!-- Ótimo -->
                    <div class="rounded-xl border border-sky-200 bg-sky-50/50 p-4 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-sky-800">Ótimo</span>
                            <span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                        </div>
                        <div class="text-3xl font-black text-sky-900 mt-2">
                            {{ $teamClassifications['otimo'] }}
                        </div>
                        <span class="text-xs text-sky-700/80 mt-0.5 block">
                            {{ $teamClassifications['total'] > 0 ? number_format(($teamClassifications['otimo'] / $teamClassifications['total']) * 100, 1, ',', '.') : 0 }}% do total
                        </span>
                        <div class="w-full bg-sky-200/70 rounded-full h-1.5 mt-3 overflow-hidden">
                            <div class="bg-sky-500 h-1.5 rounded-full transition-all" style="width: {{ $teamClassifications['total'] > 0 ? ($teamClassifications['otimo'] / $teamClassifications['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela: Lista de Equipes por Classificação (5 Colunas Padrão) -->
            <div class="rounded-2xl border border-[#dce6e2] bg-white shadow-xs overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-[#16302c]">Lista de Equipes por Classificação</h3>
                        <p class="text-xs text-[#58716b]">Detalhamento individual de cada equipe de Saúde Bucal (eSB).</p>
                    </div>

                    <!-- Filtro por Classificação -->
                    <div class="flex items-center gap-2">
                        <label for="classFilter" class="text-xs font-semibold text-[#58716b]">Filtrar:</label>
                        <select
                            id="classFilter"
                            wire:model.live="selectedClassification"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden"
                        >
                            <option value="all">Todas as Classificações</option>
                            <option value="otimo">Ótimo</option>
                            <option value="bom">Bom</option>
                            <option value="suficiente">Suficiente</option>
                            <option value="regular">Regular</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto min-w-0">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-[#dce6e2] bg-slate-50/75 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4">UNIDADE - EQUIPE</th>
                                <th class="py-3 px-4 text-center">NUMERADOR</th>
                                <th class="py-3 px-4 text-center">DENOMINADOR</th>
                                <th class="py-3 px-4 text-center">PONTUAÇÃO</th>
                                <th class="py-3 px-4 text-center">CLASSIFICAÇÃO</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($teamsList as $team)
                                @php
                                    $tLevel = $team->performance_level ?? 'regular';
                                    $tBadge = match ($tLevel) {
                                        'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
                                        'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                                        default => 'bg-rose-100 text-rose-800 border-rose-300',
                                    };
                                    $tScore = (float) $team->score_percent;
                                    $tPoints = $team->good_practices_breakdown['points'] ?? 0.0;
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-[#16302c]">{{ $team->team_name }}</div>
                                        <div class="text-xs text-[#58716b]">
                                            {{ $team->facility_name ?: 'Unidade Básica de Saúde' }}
                                            <span class="font-mono ml-1 text-slate-400">INE: {{ $team->ine }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-medium text-slate-700">
                                        {{ number_format($team->numerator, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-medium text-slate-700">
                                        {{ number_format($team->denominator, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-[#16302c]">
                                        {{ number_format($tScore, 2, ',', '.') }}{{ in_array($indicator, ['b2', 'b3', 'b5', 'b6']) ? '%' : '' }}
                                        <span class="text-[11px] block font-normal text-teal-700">{{ number_format($tPoints, 2, ',', '.') }} pts</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $tBadge }}">
                                            {{ ucfirst($tLevel) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-[#58716b]">
                                        <p class="text-sm">Nenhuma equipe encontrada para os filtros selecionados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($activeTab === 'nominal')
        <!-- ========================================================================= -->
        <!-- ABA 2: LISTA NOMINAL E COORTE (PADRÃO CONSOLIDADO C1-C5)                  -->
        <!-- ========================================================================= -->
        <div class="space-y-6">
            <!-- Bloco Superior de Métricas da Coorte -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-[#dce6e2] bg-white p-5 shadow-xs">
                    <span class="text-xs font-semibold text-[#58716b] uppercase block">Competência</span>
                    <span class="text-2xl font-black text-[#16302c] mt-1 block">{{ $year }} / Q{{ $quarter }}</span>
                    <span class="text-xs text-teal-700 mt-1 block">Saúde Bucal · {{ strtoupper($indicator) }}</span>
                </div>

                <div class="rounded-2xl border border-[#dce6e2] bg-white p-5 shadow-xs">
                    <span class="text-xs font-semibold text-[#58716b] uppercase block">Total Nominal na Coorte</span>
                    <span class="text-2xl font-black text-[#16302c] mt-1 block">{{ number_format($nominalMetrics['total'], 0, ',', '.') }}</span>
                    <span class="text-xs text-[#58716b] mt-1 block">Registros rastreados</span>
                </div>

                <div class="rounded-2xl border border-[#dce6e2] bg-white p-5 shadow-xs">
                    <span class="text-xs font-semibold text-[#58716b] uppercase block">Tratamentos Concluídos</span>
                    <span class="text-2xl font-black text-emerald-800 mt-1 block">{{ number_format($nominalMetrics['completed'], 0, ',', '.') }}</span>
                    <span class="text-xs text-emerald-600 mt-1 block">Em dia / Concluído</span>
                </div>

                <div class="rounded-2xl border border-[#dce6e2] bg-white p-5 shadow-xs">
                    <span class="text-xs font-semibold text-[#58716b] uppercase block">Tratamentos em Andamento</span>
                    <span class="text-2xl font-black text-amber-800 mt-1 block">{{ number_format($nominalMetrics['in_progress'], 0, ',', '.') }}</span>
                    <span class="text-xs text-amber-600 mt-1 block">Acompanhamento contínuo</span>
                </div>
            </div>

            <!-- Barra de Filtros e Busca com Debounce -->
            <div class="rounded-2xl border border-[#dce6e2] bg-white p-5 shadow-xs">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="searchNominal" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Buscar Cidadão</label>
                        <input
                            id="searchNominal"
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Nome, CPF ou CNS..."
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-[#16302c] placeholder-slate-400 focus:border-teal-600 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label for="statusNominal" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Situação</label>
                        <select
                            id="statusNominal"
                            wire:model.live="statusFilter"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-[#16302c] focus:border-teal-600 focus:outline-hidden"
                        >
                            <option value="all">Todos os Status</option>
                            <option value="concluido">Tratamento Concluído</option>
                            <option value="em_andamento">Em Andamento</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-hidden cursor-pointer"
                        >
                            Limpar Filtros
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabela Nominal Compacta com Scroll Horizontal -->
            <div class="rounded-2xl border border-[#dce6e2] bg-white shadow-xs overflow-hidden">
                <div class="overflow-x-auto min-w-0">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-[#dce6e2] bg-slate-50/75 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4">Cidadão</th>
                                <th class="py-3 px-4">CPF / CNS</th>
                                <th class="py-3 px-4">Equipe (INE)</th>
                                <th class="py-3 px-4">1ª Consulta</th>
                                <th class="py-3 px-4">Conclusão</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($nominalRecords as $p)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-[#16302c]">{{ $p->name }}</div>
                                        <div class="text-xs text-[#58716b]">
                                            {{ $p->birth_date?->format('d/m/Y') }} ({{ $p->age_years }} anos)
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-slate-600">
                                        <div>{{ $p->cpf ?: '—' }}</div>
                                        <div class="text-[11px] text-[#58716b]">{{ $p->cns ?: '—' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-[#16302c]">
                                        <div class="font-medium">{{ $p->team_name }}</div>
                                        <div class="font-mono text-[11px] text-[#58716b]">INE: {{ $p->ine }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-slate-700">
                                        {{ $p->first_consultation_date?->format('d/m/Y') ?: '—' }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-slate-700">
                                        {{ $p->treatment_completed_date?->format('d/m/Y') ?: '—' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($p->treatment_status === 'concluido')
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 border border-emerald-300">
                                                Concluído
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 border border-amber-300">
                                                Em Andamento
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-[#58716b]">
                                        <p class="text-sm">Nenhum registro nominal encontrado para os critérios selecionados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                @if ($nominalRecords->hasPages())
                    <div class="border-t border-[#dce6e2] p-4">
                        {{ $nominalRecords->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
