<div class="px-4 py-6 sm:px-8 max-w-7xl mx-auto space-y-6">
    <x-family-health-tabs
        :title="$meta['code'] . ' · ' . $meta['short_title']"
        :subtitle="$meta['full_title']"
        :activeIndicator="$indicator"
    />

    @php
        $level = $current['performance_level'];
        $score = $current['score_percent'];
        $isC1 = $indicator === 'c1';

        $badgeStyles = match ($level) {
            'otimo' => $isC1 ? 'bg-sky-100 text-sky-800 border-sky-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'bom' => $isC1 ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-sky-100 text-sky-800 border-sky-300',
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
            'otimo' => $isC1 ? 'bg-sky-500' : 'bg-emerald-500',
            'bom' => $isC1 ? 'bg-emerald-500' : 'bg-sky-500',
            'suficiente' => 'bg-amber-500',
            default => 'bg-rose-500',
        };

        $c1Summary = $data['c1_quarter_summary'] ?? null;
        $c1Monthly = $data['c1_monthly_evolution'] ?? [];
        $agendaAlerts = $data['agenda_alerts'] ?? [];
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
                    @if ($isC1)
                        <span class="rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2.5 py-0.5 text-[10px] font-bold">
                            NT 08/2026 · Média de 4 Meses
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
                        {{ $isC1 ? 'Média Quadrimestral' : 'Resultado Atual' }}
                    </span>
                    <div class="text-3xl sm:text-4xl font-black text-white tabular-nums my-1">
                        {{ number_format($score, 1, ',', '.') }}%
                    </div>
                    <div class="flex items-center justify-center gap-1.5 mt-1">
                        <span class="inline-block rounded-full px-2.5 py-0.5 text-[11px] font-bold border {{ $badgeStyles }}">
                            {{ $levelLabel }}
                        </span>
                        @if ($isC1 && $c1Summary)
                            <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-mono font-bold bg-white/20 text-white border border-white/30">
                                {{ number_format($c1Summary['component_iii_points'], 2, ',', '.') }} pt
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
                @foreach ($teams as $team)
                    <option value="{{ $team->ine }}">
                        {{ $team->team_name }} (INE {{ $team->ine }}) - {{ number_format($team->score_percent, 1, ',', '.') }}%
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
                @if ($c1Summary)
                    <div class="border-l border-slate-200 pl-4">
                        <span class="text-slate-400 block">Pontos Comp. III</span>
                        <span class="font-mono font-bold text-emerald-700 text-sm">
                            {{ number_format($c1Summary['component_iii_points'], 2, ',', '.') }} / 1,00 pt
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
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-xs font-semibold">
            <button
                type="button"
                wire:click="setTab('dashboard')"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 transition cursor-pointer {{ $activeTab === 'dashboard' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span>{{ $isC1 ? 'Acompanhamento Mensal & Avaliação Quadrimestral' : 'Visão do Indicador & Boas Práticas' }}</span>
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
                <span>{{ $isC1 ? 'Busca Ativa & Equilíbrio da Agenda' : 'Busca Ativa & Oportunidades' }}</span>
                @if ($isC1 && count($agendaAlerts) > 0)
                    <span class="rounded-full bg-rose-100 text-rose-800 px-2 py-0.5 text-[10px] font-bold">
                        {{ count($agendaAlerts) }} alertas
                    </span>
                @elseif (! $isC1)
                    <span class="rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[10px] font-bold">
                        {{ $current['active_search_count'] }} pendentes
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
                                    {{ number_format($c1Summary['component_iii_points'] ?? 0, 2, ',', '.') }} / 1,00 pt
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
                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score <= 10.0 || $score > 70.0) ? 'bg-rose-600 text-white ring-4 ring-rose-100 shadow-sm font-bold' : 'bg-rose-50 text-rose-900 border-rose-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Regular · 0,25 pt</span>
                                <span class="text-sm font-black">≤ 10% ou &gt; 70%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Agenda Desbalanceada</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score > 10.0 && $score <= 30.0) ? 'bg-amber-500 text-white ring-4 ring-amber-100 shadow-sm font-bold' : 'bg-amber-50 text-amber-900 border-amber-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Suficiente · 0,50 pt</span>
                                <span class="text-sm font-black">&gt; 10% e ≤ 30%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Predomínio Espontânea</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score > 30.0 && $score <= 50.0) ? 'bg-emerald-600 text-white ring-4 ring-emerald-100 shadow-sm font-bold' : 'bg-emerald-50 text-emerald-900 border-emerald-200' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Bom · 0,75 pt</span>
                                <span class="text-sm font-black">&gt; 30% e ≤ 50%</span>
                                <span class="text-[10px] block mt-0.5 opacity-90">Boa Oferta Programada</span>
                            </div>

                            <div class="rounded-2xl p-3.5 border text-center transition {{ ($score > 50.0 && $score <= 70.0) ? 'bg-sky-600 text-white ring-4 ring-sky-100 shadow-sm font-bold' : 'bg-sky-50 text-sky-900 border-sky-200' }}">
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
                                    'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
                                    'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    default => 'bg-rose-100 text-rose-800 border-rose-300',
                                };
                                $mBar = match ($mLevel) {
                                    'otimo' => 'bg-sky-500',
                                    'bom' => 'bg-emerald-500',
                                    'suficiente' => 'bg-amber-500',
                                    default => 'bg-rose-500',
                                };
                                $espontanea = max(0, $m['denominator'] - $m['numerator']);
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
                                        {{ ucfirst($mLevel) }}
                                    </span>
                                </div>

                                <div>
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-2xl font-black text-ink font-mono">
                                            {{ number_format($m['score_percent'], 1, ',', '.') }}%
                                        </span>
                                        <span class="text-[11px] font-mono font-semibold text-emerald-700">
                                            {{ number_format($m['component_iii_points'], 2, ',', '.') }} pt
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 mt-1.5 overflow-hidden">
                                        <div class="{{ $mBar }} h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $m['score_percent']) }}%"></div>
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
                                <table class="w-full text-left text-xs text-slate-700">
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
                                                $tScores = $team->monthly_scores ?? [1 => 0.0, 2 => 0.0, 3 => 0.0, 4 => 0.0];
                                                $tDetails = $team->monthly_details ?? [];
                                                $mDetail = $selectedMonth ? ($tDetails[$selectedMonth] ?? null) : null;
                                                $tLevel = $selectedMonth ? ($mDetail['performance_level'] ?? 'regular') : ($team->quarter_level ?? $team->performance_level);
                                                $tPoints = $selectedMonth ? ($mDetail['component_iii_points'] ?? 0.25) : ($team->component_iii_points ?? 0.25);

                                                $tBadge = match ($tLevel) {
                                                    'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
                                                    'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                                    'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                                                    default => 'bg-rose-100 text-rose-800 border-rose-300',
                                                };

                                                $currScore = $selectedMonth ? ($mDetail['score_percent'] ?? 0.0) : ($team->quarter_average ?? $team->score_percent);

                                                $statusText = match (true) {
                                                    $currScore > 70.0 => 'Fechada (>70%)',
                                                    $currScore < 30.0 => 'Espontânea (<30%)',
                                                    default => 'Equilibrada',
                                                };

                                                $statusBadge = match (true) {
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
                                                        {{ number_format($tScores[1] ?? 0, 1, ',', '.') }}%
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ number_format($tScores[2] ?? 0, 1, ',', '.') }}%
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ number_format($tScores[3] ?? 0, 1, ',', '.') }}%
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono text-slate-600">
                                                        {{ number_format($tScores[4] ?? 0, 1, ',', '.') }}%
                                                    </td>
                                                    <td class="py-3 px-4 text-center font-mono font-black text-sm text-ink bg-slate-50/50">
                                                        {{ number_format($team->quarter_average ?? $team->score_percent, 1, ',', '.') }}%
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $tBadge }}">
                                                            {{ ucfirst($tLevel) }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono font-bold text-emerald-700">
                                                        {{ number_format($team->component_iii_points ?? 0.25, 2, ',', '.') }} pt
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
                                                        {{ number_format($mDetail['score_percent'] ?? 0, 1, ',', '.') }}%
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $tBadge }}">
                                                            {{ ucfirst($tLevel) }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-mono font-bold text-emerald-700">
                                                        {{ number_format($tPoints, 2, ',', '.') }} pt
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
        @else
            <!-- MÓDULOS C2 A C7: VISÃO DO INDICADOR & BOAS PRÁTICAS -->
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
                        {{ $isC1 ? 'Acompanhamento mês a mês (M1 a M4), média aritmética quadrimestral e pontuação no Componente III conforme NT 08/2026' : 'Resultados homologados das Equipes de Saúde da Família (eSF) e Atenção Primária (eAP)' }}
                    </p>
                </div>
                @if ($isC1)
                    <span class="text-xs font-mono font-semibold text-teal-800 bg-teal-50 px-3 py-1.5 rounded-xl border border-teal-200">
                        Fórmula: (M1 + M2 + M3 + M4) / 4
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-line font-bold">
                        <tr>
                            <th class="py-3 px-4">Equipe / Unidade</th>
                            <th class="py-3 px-4">Código INE</th>
                            <th class="py-3 px-4">Tipo</th>
                            @if ($isC1)
                                <th class="py-3 px-3 text-center">Mês 1</th>
                                <th class="py-3 px-3 text-center">Mês 2</th>
                                <th class="py-3 px-3 text-center">Mês 3</th>
                                <th class="py-3 px-3 text-center">Mês 4</th>
                                <th class="py-3 px-4 text-center font-black text-ink">Média Quad.</th>
                                <th class="py-3 px-3 text-center">Conceito</th>
                                <th class="py-3 px-3 text-center">Comp. III</th>
                                <th class="py-3 px-3 text-center">Status Agenda</th>
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
                                    'otimo' => $isC1 ? 'bg-sky-100 text-sky-800 border-sky-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'bom' => $isC1 ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-sky-100 text-sky-800 border-sky-200',
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

                                @if ($isC1)
                                    @php
                                        $mScores = $team->monthly_scores ?? [1 => 0.0, 2 => 0.0, 3 => 0.0, 4 => 0.0];
                                        $points = $team->component_iii_points ?? 0.25;
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
                                    @endphp
                                    <td class="py-3.5 px-3 text-center font-mono text-slate-600">
                                        {{ number_format($mScores[1] ?? 0, 1, ',', '.') }}%
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono text-slate-600">
                                        {{ number_format($mScores[2] ?? 0, 1, ',', '.') }}%
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono text-slate-600">
                                        {{ number_format($mScores[3] ?? 0, 1, ',', '.') }}%
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono text-slate-600">
                                        {{ number_format($mScores[4] ?? 0, 1, ',', '.') }}%
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-black text-sm text-ink bg-slate-50/50">
                                        {{ number_format($team->quarter_average ?? $team->score_percent, 1, ',', '.') }}%
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $tBadge }}">
                                            {{ ucfirst($tLevel) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono font-bold text-emerald-700">
                                        {{ number_format($points, 2, ',', '.') }} pt
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
                        <table class="w-full text-left text-xs text-slate-700">
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
        @else
            <!-- BUSCA ATIVA PADRÃO PARA OS DEMAIS INDICADORES (C2 A C7) -->
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
                    @if ($isC1)
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
                        @if ($isC1)
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
                        Componente III - Qualidade: Quadro 2 da NT 08/2026
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
</div>
