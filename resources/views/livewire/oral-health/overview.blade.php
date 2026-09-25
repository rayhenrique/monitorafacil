<div class="app-page space-y-6">
    <!-- Topo / Barra Superior -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#58716b] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-teal-700 transition">Visão Geral</a>
                <span>/</span>
                <span class="text-teal-800 font-medium">Saúde Bucal</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#16302c]">
                Componente de Desempenho · Saúde Bucal (eSB)
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <!-- Botão de Acesso ao Dashboard Mensal -->
            <a
                href="{{ route('oral-health.monthly') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-teal-200 bg-teal-50 hover:bg-teal-100 text-teal-800 px-3.5 py-2 text-xs sm:text-sm font-semibold shadow-2xs transition"
            >
                <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <span>Dashboard Mensal</span>
            </a>

            <!-- Botão de Acesso à Busca Geral Nominal -->
            <a
                href="{{ route('oral-health.nominal') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-teal-700 hover:bg-teal-800 text-white px-3.5 py-2 text-xs sm:text-sm font-semibold shadow-xs transition"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <span>Busca Geral Nominal</span>
            </a>

            <!-- Seletor de Período Rápido -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex items-center gap-2 rounded-lg border border-[#dce6e2] bg-white px-3.5 py-2 text-xs sm:text-sm font-semibold text-[#16302c] shadow-2xs hover:bg-slate-50 transition cursor-pointer"
                >
                    <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    <span>Competência: {{ $overview['year'] }} / Q{{ $overview['quarter'] }}</span>
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition
                    class="absolute right-0 mt-1.5 w-48 rounded-xl border border-[#dce6e2] bg-white py-1.5 shadow-lg z-30"
                >
                    @foreach ($periods as $p)
                        <button
                            type="button"
                            wire:click="selectPeriod({{ $p['year'] }}, {{ $p['quarter'] }})"
                            @click="open = false"
                            class="flex w-full items-center justify-between px-3.5 py-2 text-xs text-left text-[#16302c] hover:bg-teal-50 hover:text-teal-800 transition cursor-pointer {{ $overview['year'] === $p['year'] && $overview['quarter'] === $p['quarter'] ? 'bg-teal-50/70 font-semibold text-teal-800' : '' }}"
                        >
                            <span>{{ $p['year'] }} / Q{{ $p['quarter'] }}</span>
                            @if ($overview['year'] === $p['year'] && $overview['quarter'] === $p['quarter'])
                                <svg class="h-3.5 w-3.5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Card Central do Módulo -->
    <div class="rounded-2xl border border-[#dce6e2] bg-white p-6 sm:p-8 text-center shadow-xs">
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-teal-50 text-teal-700 ring-4 ring-teal-50/50">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
            </svg>
        </div>

        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#16302c]">
            Saúde Bucal (eSB)
        </h2>
        <p class="mt-1 text-sm font-medium text-[#58716b]">
            Acompanhamento Analítico dos 6 Indicadores Oficiais de Saúde Bucal na APS
        </p>

        <!-- Indicador de Pontuação Municipal Consolidada -->
        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
            <div class="inline-flex items-center gap-2 rounded-full border border-[#dce6e2] bg-[#f5f7f6] px-4 py-1.5 text-xs font-semibold text-[#16302c]">
                <span>Competência: {{ $overview['year'] }} / Q{{ $overview['quarter'] }}</span>
            </div>

            @php
                $scoreLevel = $overview['municipal_classification'] ?? 'regular';
                $scoreBadge = match ($scoreLevel) {
                    'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
                    'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                    default => 'bg-rose-100 text-rose-800 border-rose-300',
                };
            @endphp
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-xs font-bold {{ $scoreBadge }}">
                <span>Pontuação Municipal: {{ number_format($overview['municipal_score'], 2, ',', '.') }} / 10,00 pts ({{ ucfirst($scoreLevel) }})</span>
            </div>
        </div>
    </div>

    <!-- Grid dos 6 Indicadores de Saúde Bucal (B1 a B6) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($overview['cards'] as $slug => $item)
            @php
                $meta = $item['meta'];
                $classifications = $item['classifications'] ?? ['otimo' => 0, 'bom' => 0, 'suficiente' => 0, 'regular' => 0, 'total' => 0];
                $score = $item['score'];
                $level = $item['level'] ?? 'regular';
                $levelBadge = match ($level) {
                    'otimo' => 'bg-sky-100 text-sky-800 border-sky-300',
                    'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'suficiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                    default => 'bg-rose-100 text-rose-800 border-rose-300',
                };
                $barColor = match ($level) {
                    'otimo' => 'bg-sky-500',
                    'bom' => 'bg-emerald-500',
                    'suficiente' => 'bg-amber-500',
                    default => 'bg-rose-500',
                };
            @endphp

            <div class="rounded-2xl border border-[#dce6e2] bg-white p-5 shadow-xs hover:border-teal-300 hover:shadow-sm transition flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-start gap-3.5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700 border border-teal-100/80">
                            <span class="text-sm font-black tracking-tight">{{ strtoupper($meta['code']) }}</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-teal-700">
                                    {{ $meta['category'] }}
                                </span>
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $levelBadge }}">
                                    {{ ucfirst($level) }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-[#16302c] leading-snug truncate mt-0.5" title="{{ $meta['panel_title'] }}">
                                {{ $meta['code'] }} · {{ $meta['short_title'] }}
                            </h3>
                            <p class="text-xs text-[#58716b] line-clamp-2 mt-0.5">
                                {{ $meta['objective'] }}
                            </p>
                        </div>
                    </div>

                    <!-- Métricas Centrais do Card -->
                    <div class="rounded-xl border border-slate-100 bg-[#f5f7f6] p-3.5">
                        <div class="flex items-baseline justify-between">
                            <div>
                                <span class="text-[11px] font-semibold text-[#58716b] uppercase">Resultado Consolidado</span>
                                <div class="text-2xl font-black text-[#16302c] tracking-tight">
                                    @if ($score !== null)
                                        {{ number_format($score, 2, ',', '.') }}{{ in_array($slug, ['b2', 'b3', 'b5', 'b6']) ? '%' : '' }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-[11px] font-semibold text-[#58716b] uppercase">Pontos CVAT</span>
                                <div class="text-base font-bold text-teal-800">
                                    {{ number_format($item['points'], 2, ',', '.') }} <span class="text-xs text-[#58716b]">/ {{ number_format($meta['weight'], 1, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de Progresso Visual -->
                        <div class="w-full bg-slate-200/80 rounded-full h-1.5 mt-2.5 overflow-hidden">
                            <div class="{{ $barColor }} h-1.5 rounded-full transition-all" style="width: {{ min(100, max(5, $score ?? 0)) }}%"></div>
                        </div>

                        <div class="mt-2.5 flex items-center justify-between text-[11px] text-[#58716b] pt-2 border-t border-slate-200/60">
                            <span>Num: <strong class="text-[#16302c]">{{ number_format($item['numerator'], 0, ',', '.') }}</strong></span>
                            <span>Den: <strong class="text-[#16302c]">{{ number_format($item['denominator'], 0, ',', '.') }}</strong></span>
                        </div>
                    </div>

                    <!-- Mini Distribuição das Equipes -->
                    <div class="space-y-1.5 pt-1">
                        <div class="flex items-center justify-between text-[11px] text-[#58716b]">
                            <span>Classificação das Equipes ({{ $classifications['total'] }})</span>
                        </div>
                        <div class="grid grid-cols-4 gap-1.5 text-center text-[10px] font-semibold">
                            <div class="rounded-md bg-rose-50 text-rose-800 py-1 border border-rose-100">
                                Reg: {{ $classifications['regular'] }}
                            </div>
                            <div class="rounded-md bg-amber-50 text-amber-800 py-1 border border-amber-100">
                                Suf: {{ $classifications['suficiente'] }}
                            </div>
                            <div class="rounded-md bg-emerald-50 text-emerald-800 py-1 border border-emerald-100">
                                Bom: {{ $classifications['bom'] }}
                            </div>
                            <div class="rounded-md bg-sky-50 text-sky-800 py-1 border border-sky-100">
                                Ót: {{ $classifications['otimo'] }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botão de Ação -->
                <div class="pt-2">
                    <a
                        href="{{ route('oral-health.indicator', ['indicator' => $slug, 'ano' => $overview['year'], 'quadrimestre' => $overview['quarter']]) }}"
                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-[#dce6e2] bg-white px-3.5 py-2 text-xs font-semibold text-[#16302c] shadow-2xs hover:bg-teal-50 hover:border-teal-300 hover:text-teal-800 transition cursor-pointer"
                    >
                        <span>Acessar Painel Detalhado</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
