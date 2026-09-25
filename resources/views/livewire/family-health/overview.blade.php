<div class="app-page space-y-6">
    <!-- Topo / Barra Superior -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#58716b] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-teal-700 transition">Visão Geral</a>
                <span>/</span>
                <span class="text-teal-800 font-medium">Saúde da Família</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#16302c]">
                Componente de Qualidade / Saúde da Família - Quadrimestre
            </h1>
        </div>

        <div>
            <button
                type="button"
                wire:click="toggleAdvancedSearch"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-700 hover:bg-teal-800 active:bg-teal-900 text-white px-4 py-2.5 text-sm font-semibold shadow-xs transition cursor-pointer"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <span>Busca Avançada</span>
            </button>
        </div>
    </div>

    <!-- Hero Header Card Central -->
    <div class="rounded-2xl border border-[#dce6e2] bg-white p-6 sm:p-8 text-center shadow-xs">
        <!-- Ícone Central Circular -->
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-teal-50 text-teal-700 ring-4 ring-teal-50/50">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
            </svg>
        </div>

        <!-- Título e Subtítulo -->
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#16302c]">
            Saúde da Família
        </h2>
        <p class="mt-1 text-sm font-medium text-[#58716b]">
            Monitoramento dos Indicadores de Atenção Básica
            <span class="sr-only">Desempenho Geral nos Indicadores Clínicos · Portaria GM/MS nº 3.493/2024</span>
        </p>

        <!-- Badge do Quadrimestre Avaliado -->
        <div class="mt-4 flex items-center justify-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-[#dce6e2] bg-[#f5f7f6] px-4 py-1.5 text-xs font-semibold text-[#16302c] shadow-2xs">
                <svg class="h-4 w-4 text-[#58716b]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <span>Quadrimestre: {{ $overview['year'] }} / Q{{ $overview['quarter'] }}</span>
            </div>
        </div>
    </div>

    <!-- Grid dos 7 Indicadores (C1 ao C7) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($overview['indicators'] as $slug => $item)
            @php
                $meta = $item['meta'];
                $classifications = $item['classifications'] ?? ['otimo' => 0, 'bom' => 0, 'suficiente' => 0, 'regular' => 0, 'total' => 0];
            @endphp

            <div class="rounded-2xl border border-[#dce6e2] bg-white p-5 shadow-xs hover:border-teal-300 hover:shadow-sm transition flex flex-col justify-between space-y-4">
                <!-- Cabeçalho do Card -->
                <div class="space-y-3">
                    <div class="flex items-start gap-3.5">
                        <!-- Ícone do Indicador -->
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700 border border-teal-100/80">
                            @if ($slug === 'c1')
                                <!-- Acesso / Clínica -->
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A1.5 1.5 0 0 0 18 9H6a1.5 1.5 0 0 0-1.5 1.333V21" />
                                </svg>
                            @elseif ($slug === 'c2')
                                <!-- Criança / Puericultura -->
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            @elseif ($slug === 'c3')
                                <!-- Gestante / Materna -->
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm3.5 6a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-7.5 7a4 4 0 0 1 8 0v2H8v-2Z" />
                                </svg>
                            @elseif ($slug === 'c4')
                                <!-- Diabetes / Glicemia -->
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m-7-9h14M8.5 7.5l7 7m0-7-7 7" />
                                </svg>
                            @elseif ($slug === 'c5')
                                <!-- Hipertensão / Cardíaco -->
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                            @elseif ($slug === 'c6')
                                <!-- Idoso / Longevidade -->
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0" />
                                </svg>
                            @else
                                <!-- Saúde da Mulher / Prevenção Câncer -->
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                </svg>
                            @endif
                        </div>

                        <!-- Título e Subtítulo -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="rounded-md bg-teal-50 px-2 py-0.5 text-[11px] font-bold text-teal-800 border border-teal-200/80 font-mono shrink-0">
                                    {{ $meta['code'] }}
                                </span>
                                <h3 class="text-base font-bold text-[#16302c] truncate hover:text-teal-700 transition">
                                    <a href="{{ route('family-health.indicator', ['indicator' => $slug]) }}">
                                        {{ $meta['panel_title'] ?? $meta['short_title'] }}
                                    </a>
                                </h3>
                            </div>
                            <p class="mt-1 text-xs text-[#58716b] leading-snug line-clamp-2" title="{{ $meta['panel_subtitle'] ?? $meta['full_title'] }}">
                                {{ $meta['panel_subtitle'] ?? $meta['full_title'] }}
                                <span class="sr-only">{{ $meta['short_title'] }} · {{ $meta['full_title'] }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Grade 2x2 de Classificações das Equipes -->
                    <div class="rounded-xl bg-[#f5f7f6] border border-[#dce6e2] p-3.5">
                        <div class="grid grid-cols-2 gap-x-4 gap-y-2.5">
                            <!-- Ótimo (dot emerald) -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs font-medium text-[#58716b]">Ótimo</span>
                                </div>
                                <span class="text-sm font-bold text-emerald-700 tabular-nums">
                                    {{ $classifications['otimo'] }}
                                </span>
                            </div>

                            <!-- Bom (dot teal) -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-teal-600"></span>
                                    <span class="text-xs font-medium text-[#58716b]">Bom</span>
                                </div>
                                <span class="text-sm font-bold text-teal-700 tabular-nums">
                                    {{ $classifications['bom'] }}
                                </span>
                            </div>

                            <!-- Suficiente (dot amber) -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                    <span class="text-xs font-medium text-[#58716b]">Suficiente</span>
                                </div>
                                <span class="text-sm font-bold text-amber-700 tabular-nums">
                                    {{ $classifications['suficiente'] }}
                                </span>
                            </div>

                            <!-- Regular (dot red) -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                    <span class="text-xs font-medium text-[#58716b]">Regular</span>
                                </div>
                                <span class="text-sm font-bold text-rose-700 tabular-nums">
                                    {{ $classifications['regular'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rodapé do Card com Ação -->
                <div class="pt-2 border-t border-[#dce6e2] flex items-center justify-between text-xs">
                    <span class="text-[#58716b] text-[11px]">
                        {{ $classifications['total'] }} {{ $classifications['total'] === 1 ? 'equipe avaliada' : 'equipes avaliadas' }}
                    </span>

                    <a
                        href="{{ route('family-health.indicator', ['indicator' => $slug]) }}"
                        class="inline-flex items-center gap-1 font-semibold text-teal-700 hover:text-teal-900 transition group"
                    >
                        <span>Ver detalhes</span>
                        <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal de Busca Avançada -->
    @if ($advancedSearchOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-[#dce6e2] overflow-hidden">
                <!-- Cabeçalho do Modal -->
                <div class="px-6 py-5 border-b border-[#dce6e2] flex items-center justify-between bg-[#f5f7f6]">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-700 border border-teal-200/60">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#16302c]">Busca Avançada · Saúde da Família</h3>
                            <p class="text-xs text-[#58716b]">Pesquise equipes, indicadores e selecione o quadrimestre</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="closeAdvancedSearch"
                        class="rounded-lg p-2 text-slate-400 hover:text-[#16302c] hover:bg-slate-100 transition cursor-pointer"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Corpo do Modal -->
                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    <!-- Seletor Rápido de Período -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#58716b]">Período de Avaliação (Quadrimestre)</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($periods as $p)
                                <button
                                    type="button"
                                    wire:click="selectPeriod({{ $p['year'] }}, {{ $p['quarter'] }})"
                                    class="rounded-lg px-3.5 py-1.5 text-xs font-bold transition cursor-pointer {{ $year === $p['year'] && $quarter === $p['quarter'] ? 'bg-teal-700 text-white shadow-xs' : 'bg-slate-100 text-[#16302c] hover:bg-slate-200' }}"
                                >
                                    {{ $p['year'] }} / Q{{ $p['quarter'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Atalho Direto aos Indicadores -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#58716b]">Acesso Direto ao Indicador</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach ($overview['indicators'] as $code => $ind)
                                <a
                                    href="{{ route('family-health.indicator', ['indicator' => $code]) }}"
                                    class="rounded-xl border border-[#dce6e2] bg-[#f5f7f6] hover:bg-teal-50 hover:border-teal-300 p-2.5 text-center transition group"
                                >
                                    <span class="block text-xs font-bold text-teal-800 font-mono">{{ $ind['meta']['code'] }}</span>
                                    <span class="block text-[11px] font-medium text-[#16302c] group-hover:text-teal-900 truncate">{{ $ind['meta']['panel_title'] ?? $ind['meta']['short_title'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Campo de Busca por Equipe / INE -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#58716b]">Localizar Equipe de Saúde (eSF / eAP)</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchTeamQuery"
                                placeholder="Digite o nome da equipe ou código INE..."
                                class="w-full rounded-lg border border-[#dce6e2] bg-[#f5f7f6] pl-9 pr-4 py-2.5 text-sm text-[#16302c] placeholder-slate-400 focus:border-teal-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-600/20 transition"
                            />
                        </div>
                    </div>

                    <!-- Lista de Equipes Filtradas -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs text-[#58716b]">
                            <span>Equipes disponíveis ({{ count($teams) }})</span>
                            @if (! empty($searchTeamQuery))
                                <span class="text-teal-700 font-medium">Filtrado por: "{{ $searchTeamQuery }}"</span>
                            @endif
                        </div>

                        <div class="divide-y divide-slate-100 rounded-xl border border-[#dce6e2] bg-white max-h-48 overflow-y-auto">
                            @forelse ($teams as $t)
                                <div class="p-3 flex items-center justify-between hover:bg-slate-50 transition">
                                    <div>
                                        <p class="text-xs font-bold text-[#16302c]">{{ $t['name'] }}</p>
                                        <p class="text-[11px] text-[#58716b] font-mono">INE: {{ $t['ine'] }} · Tipo: {{ $t['type'] ?? '70' }}</p>
                                    </div>
                                    <a
                                        href="{{ route('family-health.indicator', ['indicator' => 'c1', 'ine' => $t['ine']]) }}"
                                        class="rounded-lg bg-teal-50 hover:bg-teal-700 hover:text-white px-2.5 py-1 text-xs font-bold text-teal-800 transition"
                                    >
                                        Ver C1
                                    </a>
                                </div>
                            @empty
                                <div class="p-4 text-center text-xs text-[#58716b]">
                                    Nenhuma equipe encontrada para o filtro informado.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Rodapé do Modal -->
                <div class="px-6 py-4 border-t border-[#dce6e2] bg-[#f5f7f6] flex items-center justify-end">
                    <button
                        type="button"
                        wire:click="closeAdvancedSearch"
                        class="rounded-lg bg-white border border-[#dce6e2] hover:bg-slate-50 text-[#16302c] px-4 py-2 text-xs font-semibold shadow-xs transition cursor-pointer"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
