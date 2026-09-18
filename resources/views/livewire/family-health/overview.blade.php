<div class="px-4 py-6 sm:px-8 max-w-7xl mx-auto space-y-6">
    <x-family-health-tabs
        title="Saúde da Família · Indicadores C1 ao C7"
        subtitle="Consolidado municipal do Componente de Qualidade do Cofinanciamento Federal (Portaria GM/MS nº 3.493/2024)"
        activeIndicator="overview"
    />

    <!-- Seletor de Período e Resumo Executivo -->
    <div class="rounded-3xl border border-line bg-gradient-to-br from-[#0c1f1c] via-[#0f2d26] to-[#081714] text-white p-6 sm:p-8 shadow-md">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-500/25 px-3 py-1 text-[11px] font-semibold text-teal-300 border border-teal-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-400"></span>
                        Ano {{ $overview['year'] }} · Quadrimestre Q{{ $overview['quarter'] }}
                    </span>
                    <span class="text-xs text-slate-400">Equipes Homologadas: {{ $overview['active_teams_count'] }} eSF / eAP</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                    Desempenho Geral nos Indicadores Clínicos (C1 a C7)
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Acompanhamento em tempo real do cumprimento das boas práticas de cuidado nos ciclos de vida, doenças crônicas e atenção à mulher e à criança.
                </p>
            </div>

            <!-- Seletor de Quadrimestre e Média Geral -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 shrink-0">
                <!-- Seletor de Período -->
                <div class="bg-white/10 backdrop-blur-xs border border-white/10 rounded-2xl p-2 flex items-center gap-1">
                    @foreach ($periods as $p)
                        <button
                            type="button"
                            wire:click="setPeriod({{ $p['year'] }}, {{ $p['quarter'] }})"
                            class="px-3 py-2 rounded-xl text-xs font-semibold transition cursor-pointer {{ $year === $p['year'] && $quarter === $p['quarter'] ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                        >
                            {{ $p['year'] }}/Q{{ $p['quarter'] }}
                        </button>
                    @endforeach
                </div>

                <!-- Card de Média Municipal -->
                <div class="rounded-2xl bg-teal-500/15 border border-teal-500/30 px-6 py-3 text-center min-w-[140px]">
                    <p class="text-[11px] text-teal-300 uppercase font-semibold">Média Municipal</p>
                    <p class="text-2xl sm:text-3xl font-black text-white mt-0.5">
                        {{ number_format($overview['municipal_average_score'], 1, ',', '.') }}%
                    </p>
                    <span class="inline-block mt-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-teal-400/20 text-teal-200">
                        Índice Sintético
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid com os 7 Indicadores Oficiais C1 a C7 -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-ink">Painel de Indicadores Clínicos Oficiais</h3>
            <span class="text-xs text-muted">7 Indicadores Monitorados</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($overview['indicators'] as $slug => $item)
                @php
                    $meta = $item['meta'];
                    $score = $item['score_percent'];
                    $level = $item['performance_level'];

                    $badgeStyles = match ($level) {
                        null => 'bg-slate-100 text-slate-700 border-slate-200',
                        'otimo' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'bom' => 'bg-sky-100 text-sky-800 border-sky-200',
                        'suficiente' => 'bg-amber-100 text-amber-800 border-amber-200',
                        default => 'bg-rose-100 text-rose-800 border-rose-200',
                    };

                    $levelLabel = match ($level) {
                        null => 'Sem resultado',
                        'otimo' => 'Ótimo',
                        'bom' => 'Bom',
                        'suficiente' => 'Suficiente',
                        default => 'Regular',
                    };

                    $barColor = match ($level) {
                        null => 'bg-slate-300',
                        'otimo' => 'bg-emerald-500',
                        'bom' => 'bg-sky-500',
                        'suficiente' => 'bg-amber-500',
                        default => 'bg-rose-500',
                    };
                @endphp

                <div class="rounded-3xl border border-line bg-white p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-4">
                    <!-- Topo do Card -->
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <span class="rounded-xl bg-teal-100 text-teal-900 px-3 py-1 text-xs font-black font-mono">
                                {{ $meta['code'] }}
                            </span>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold border {{ $badgeStyles }}">
                                {{ $levelLabel }}
                            </span>
                        </div>

                        <div>
                            <h4 class="text-base font-bold text-ink hover:text-teal-800 transition">
                                <a href="{{ route('family-health.indicator', ['indicator' => $slug]) }}">
                                    {{ $meta['short_title'] }}
                                </a>
                            </h4>
                            <p class="text-[11px] text-teal-700 font-medium">{{ $meta['category'] }}</p>
                            <p class="text-xs text-muted mt-1 line-clamp-2 leading-relaxed">
                                {{ $meta['objective'] }}
                            </p>
                        </div>
                    </div>

                    <!-- Métricas Centrais -->
                    <div class="space-y-3 pt-2 border-t border-slate-100">
                        <div class="flex items-end justify-between">
                            <div>
                                <span class="text-2xl font-black text-ink tabular-nums">
                                    {{ $score !== null ? number_format($score, 1, ',', '.').'%' : '—' }}
                                </span>
                                <span class="text-xs text-muted block">
                                    @if ($slug === 'c2')
                                        {{ $score !== null ? ($item['is_preview'] ? 'Prévia do DW PEC · M1–M4' : 'Estimativa local do DW PEC') : 'Aguardando nova extração do C2' }}
                                    @else
                                        {{ number_format($item['numerator'], 0, '', '.') }} de {{ number_format($item['denominator'], 0, '', '.') }}
                                    @endif
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] text-slate-500 block">{{ $slug === 'c2' ? 'Completam 2 anos no quadrimestre' : 'Público Elegível' }}</span>
                                <span class="text-xs font-semibold text-slate-700">
                                    {{ $slug === 'c2' ? ($item['cohort_total'] !== null ? number_format($item['cohort_total'], 0, '', '.').' crianças' : '—') : number_format($item['denominator'], 0, '', '.').' pessoas' }}
                                </span>
                                @if ($slug === 'c2' && $item['cohort_total'] !== null)
                                    <span class="text-[10px] text-slate-500 block">{{ number_format($item['evaluated_total'], 0, '', '.') }} já completaram 2 anos{{ $item['cohort_as_of'] ? ' até '.$item['cohort_as_of'] : '' }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Barra de Progresso Visual -->
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $score ?? 0) }}%"></div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                            <span>Meta Ótimo: > 75%</span>
                            @if ($slug === 'c2' && $score !== null)
                                <span class="text-sky-700 font-semibold">Resultado preliminar</span>
                            @elseif ($item['active_search_count'] > 0)
                                <span class="text-amber-700 font-semibold inline-flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    {{ $item['active_search_count'] }} em busca ativa
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Ação: Ver Detalhes do Indicador -->
                    <div class="pt-2">
                        <a
                            href="{{ route('family-health.indicator', ['indicator' => $slug]) }}"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-50 hover:bg-teal-700 hover:text-white px-4 py-2.5 text-xs font-bold text-teal-900 border border-slate-200/80 transition group"
                        >
                            <span>Detalhamento do {{ $meta['code'] }}</span>
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Informações sobre o Componente de Qualidade -->
    <div class="rounded-3xl border border-line bg-white p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-800 border border-teal-200">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-ink">Diretrizes da Portaria GM/MS nº 3.493/2024 e Notas Metodológicas C1 a C7</h4>
                <p class="text-xs text-muted leading-relaxed">
                    O Componente de Qualidade remunera os municípios de acordo com o alcance de resultados nos 7 indicadores clínicos de Saúde da Família e Atenção Primária. O cálculo avalia boas práticas contínuas realizadas por médicos, enfermeiros e agentes comunitários de saúde registrados no e-SUS APS. Mantenha os envios diários para assegurar que a pontuação máxima de incentivo financeiro seja transferida fundo a fundo ao município.
                </p>
            </div>
        </div>
    </div>
</div>
