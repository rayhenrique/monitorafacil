<div class="px-4 py-6 sm:px-8 max-w-7xl mx-auto space-y-6">
    <x-family-health-tabs
        :title="$meta['code'] . ' · ' . $meta['short_title']"
        :subtitle="$meta['full_title']"
        :activeIndicator="$indicator"
    />

    @php
        $level = $current['performance_level'];
        $score = $current['score_percent'];

        $badgeStyles = match ($level) {
            'otimo' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'bom' => 'bg-sky-100 text-sky-800 border-sky-300',
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
            'otimo' => 'bg-emerald-500',
            'bom' => 'bg-sky-500',
            'suficiente' => 'bg-amber-500',
            default => 'bg-rose-500',
        };
    @endphp

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
                <div class="rounded-3xl bg-white/10 border border-white/15 p-5 text-center min-w-[170px] backdrop-blur-xs">
                    <span class="text-[11px] font-semibold text-teal-300 uppercase tracking-wider block">
                        Resultado Atual
                    </span>
                    <div class="text-3xl sm:text-4xl font-black text-white tabular-nums my-1">
                        {{ number_format($score, 1, ',', '.') }}%
                    </div>
                    <span class="inline-block rounded-full px-2.5 py-0.5 text-[11px] font-bold border {{ $badgeStyles }}">
                        {{ $levelLabel }}
                    </span>
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
                @foreach ($teams as $team)
                    <option value="{{ $team->ine }}">
                        {{ $team->team_name }} (INE {{ $team->ine }}) - {{ number_format($team->score_percent, 1, ',', '.') }}%
                    </option>
                @endforeach
            </select>
        </div>

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
    </div>

    <!-- Abas Internas de Navegação -->
    <div class="border-b border-line">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-xs font-semibold">
            <button
                type="button"
                wire:click="setTab('dashboard')"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 transition cursor-pointer {{ $activeTab === 'dashboard' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span>Visão do Indicador & Boas Práticas</span>
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
                <span>Busca Ativa & Oportunidades</span>
                <span class="rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[10px] font-bold">
                    {{ $current['active_search_count'] }} pendentes
                </span>
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

    <!-- CONTEÚDO DA ABA 1: DASHBOARD & BOAS PRÁTICAS -->
    @if ($activeTab === 'dashboard')
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

    <!-- CONTEÚDO DA ABA 2: DESEMPENHO POR EQUIPE -->
    @if ($activeTab === 'teams')
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4 animate-fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-ink">Desempenho Individualizado por Equipe (INE)</h3>
                    <p class="text-xs text-muted">Resultados homologados das Equipes de Saúde da Família (eSF) e Atenção Primária (eAP)</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-line font-bold">
                        <tr>
                            <th class="py-3 px-4">Equipe / Unidade</th>
                            <th class="py-3 px-4">Código INE</th>
                            <th class="py-3 px-4">Tipo</th>
                            <th class="py-3 px-4 text-center">Numerador</th>
                            <th class="py-3 px-4 text-center">Denominador</th>
                            <th class="py-3 px-4 text-center">Desempenho</th>
                            <th class="py-3 px-4 text-center">Classificação</th>
                            <th class="py-3 px-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($teams as $team)
                            @php
                                $tLevel = $team->performance_level;
                                $tBadge = match ($tLevel) {
                                    'otimo' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'bom' => 'bg-sky-100 text-sky-800 border-sky-200',
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

    <!-- CONTEÚDO DA ABA 3: BUSCA ATIVA & OPORTUNIDADES -->
    @if ($activeTab === 'active_search')
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
                <table class="w-full text-left text-xs text-slate-700">
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

    <!-- CONTEÚDO DA ABA 4: NOTA METODOLÓGICA OFICIAL -->
    @if ($activeTab === 'rules')
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-6 animate-fade-in">
            <div class="border-b border-line pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-ink">Caderno Metodológico Oficial · Ministério da Saúde</h3>
                    <p class="text-xs text-muted">Secretaria de Atenção Primária à Saúde (SAPS/MS) · Departamento de Estratégias e Acreditação (DEAPS)</p>
                </div>
                <span class="text-xs font-mono font-semibold text-teal-800 bg-teal-50 px-3 py-1.5 rounded-xl border border-teal-200">
                    {{ $meta['source_pdf'] }}
                </span>
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

            <!-- Modelos de Informação do e-SUS PEC -->
            <div class="rounded-2xl bg-teal-50/70 border border-teal-200 p-4 space-y-2">
                <h4 class="text-xs font-bold text-teal-900 uppercase">Modelos de Informação e-SUS APS Utilizados</h4>
                <p class="text-xs text-teal-800 leading-relaxed">
                    Os dados são processados a partir do <strong>Modelo de Informação de Atendimento Individual (MIAI)</strong>, <strong>Modelo de Informação de Procedimentos (MIP)</strong>, <strong>Modelo de Visita Domiciliar e Territorial (MIVDT)</strong> e <strong>Registro de Imunobiológico Administrado (RIA / RNDS)</strong> enviados via prontuário eletrônico e-SUS PEC com CNS profissional validado e CNES da UBS homologado no SCNES.
                </p>
            </div>
        </div>
    @endif
</div>
