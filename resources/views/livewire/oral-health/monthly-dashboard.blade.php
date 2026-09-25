<div class="app-page space-y-6">

    <!-- Navegação e Topo da Página (Conforme Screenshot 1) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-[#dce6e2] shadow-xs">
        <div>
            <nav class="flex items-center gap-2 text-xs text-[#58716b] mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#16302c] transition">Início</a>
                <span>/</span>
                <a href="{{ route('oral-health.overview') }}" class="hover:text-[#16302c] transition">Saúde Bucal</a>
                <span>/</span>
                <span class="text-teal-800 font-semibold">Dashboard Equipes</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#16302c] flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-teal-50 text-teal-700 border border-teal-200/80">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 4c-2.5 0-4 2-4 5.5 0 3.5 1.5 6 3 9.5 1 2.3 2 4.5 3 4.5s1-1.5 2-4c.5-1.3 1-2.5 1-2.5s.5 1.2 1 2.5c1 2.5 1 4 2 4s2-2.2 3-4.5c1.5-3.5 3-6 3-9.5C21 6 19.5 4 17 4c-2 0-3.5 1.5-5 1.5S9 4 7 4z" />
                    </svg>
                </span>
                <span>Componente de Qualidade / Saúde Bucal - Dashboard Equipes</span>
            </h1>
        </div>

        <!-- Botões de Ação à Direita -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Botão de Relatório com Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold shadow-xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span>Relatório</span>
                    <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-transition
                    class="absolute right-0 mt-1.5 w-44 rounded-xl border border-[#dce6e2] bg-white py-1.5 shadow-lg z-30 text-xs"
                    style="display: none;"
                >
                    <button
                        type="button"
                        wire:click="exportCsv"
                        @click="open = false"
                        class="w-full px-3.5 py-2 text-left text-slate-700 hover:bg-slate-50 hover:text-teal-800 flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        <span>Exportar CSV</span>
                    </button>
                    <button
                        type="button"
                        onclick="window.print()"
                        @click="open = false"
                        class="w-full px-3.5 py-2 text-left text-slate-700 hover:bg-slate-50 hover:text-teal-800 flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.673-2.023-1.28-2.829H4.5A2.25 2.25 0 002.25 13.25v4.5A2.25 2.25 0 004.5 20h15a2.25 2.25 0 002.25-2.25v-4.5A2.25 2.25 0 0019.5 11h-.94c-.607.806-1.04 1.753-1.28 2.829m-10.56 0A7.502 7.502 0 0012 16.5c2.316 0 4.368-.96 5.84-2.671M6.72 13.829A7.525 7.525 0 0112 11.25c2.09 0 3.974.793 5.4 2.079" /></svg>
                        <span>Imprimir / PDF</span>
                    </button>
                </div>
            </div>

            <!-- Botão de Busca Avançada -->
            <button
                type="button"
                wire:click="openAdvancedModal"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 active:bg-teal-900 text-white text-xs font-semibold shadow-xs transition cursor-pointer"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <span>Busca Avançada</span>
            </button>
        </div>
    </div>

    <!-- HERO CARD CENTRAL: SAÚDE BUCAL / MÊS (CONFORME SCREENSHOT 1) -->
    <div class="bg-white rounded-2xl border border-[#dce6e2] shadow-xs p-7 sm:p-8 flex flex-col items-center justify-center text-center">
        <!-- Ícone Redondo de Saúde Bucal -->
        <div class="w-14 h-14 rounded-full bg-teal-50 text-teal-700 border border-teal-200 flex items-center justify-center mb-3 shadow-2xs">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 4c-2.5 0-4 2-4 5.5 0 3.5 1.5 6 3 9.5 1 2.3 2 4.5 3 4.5s1-1.5 2-4c.5-1.3 1-2.5 1-2.5s.5 1.2 1 2.5c1 2.5 1 4 2 4s2-2.2 3-4.5c1.5-3.5 3-6 3-9.5C21 6 19.5 4 17 4c-2 0-3.5 1.5-5 1.5S9 4 7 4z" />
            </svg>
        </div>

        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#16302c]">
            Saúde Bucal
        </h2>
        <p class="text-xs sm:text-sm text-[#58716b] mt-1 font-medium">
            Monitoramento dos Indicadores de Atenção Básica
        </p>

        <!-- Indicador e Seletor do Mês Ativo -->
        <div class="mt-4 flex flex-wrap items-center justify-center gap-1.5">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                <svg class="w-4 h-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <span>Mês: <strong class="font-mono">{{ $selectedYear }} / M{{ $selectedMonth }}</strong></span>
            </span>

            <!-- Pílulas dos meses disponíveis para troca rápida -->
            <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-full border border-slate-200/80 ml-2">
                @foreach ($availableMonths as $m)
                    <button
                        type="button"
                        wire:click="setMonth({{ $m }})"
                        class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold transition cursor-pointer {{ $selectedMonth === $m ? 'bg-teal-700 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-200' }}"
                        title="Competência Mês {{ $m }}"
                    >
                        M{{ $m }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TÍTULO DA SEÇÃO: DETALHAMENTO POR UNIDADE (SCREENSHOT 1 & 2) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-1">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-[#16302c]">
                Detalhamento por Unidade
            </h2>
            <p class="text-xs text-[#58716b]">
                Performance detalhada de cada indicador por Equipe de Saúde Bucal (eSB)
            </p>
        </div>

        @if ($filterTeam || $filterCnes)
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-500 font-medium">Filtrado por:</span>
                @if ($filterTeam) <span class="bg-teal-50 text-teal-800 px-2 py-0.5 rounded text-[11px]">Equipe: {{ $filterTeam }}</span> @endif
                @if ($filterCnes) <span class="bg-teal-50 text-teal-800 px-2 py-0.5 rounded text-[11px]">CNES: {{ $filterCnes }}</span> @endif
                <button type="button" wire:click="clearFilters" class="text-rose-600 hover:text-rose-800 font-bold cursor-pointer">✕ Limpar</button>
            </div>
        @endif
    </div>

    <!-- CARDS INDIVIDUAIS POR EQUIPE ESB (CONFORME SCREENSHOTS 2 & 3) -->
    <div class="space-y-4">
        @forelse ($teams as $team)
            <div class="bg-white rounded-2xl border border-[#dce6e2] shadow-xs p-5 hover:border-teal-300 transition-colors">
                <!-- Cabeçalho do Card: INE - Nome da Equipe e CNES - Estabelecimento -->
                <div class="pb-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-[#16302c] tracking-tight">
                            {{ $team['ine'] }} - {{ $team['team_name'] }}
                        </h3>
                        <p class="text-xs text-[#58716b] font-medium mt-0.5">
                            {{ $team['cnes'] }} - {{ $team['facility_name'] }}
                        </p>
                    </div>

                    <a
                        href="{{ route('oral-health.nominal', ['ine' => $team['ine']]) }}"
                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-teal-700 hover:text-teal-900 transition self-start sm:self-auto"
                        title="Ver relação nominal de cidadãos desta equipe"
                    >
                        <span>Ver Lista Nominal</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    </a>
                </div>

                <!-- Grade com os 6 Indicadores Odontológicos (B1 a B6) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 pt-3">
                    @foreach (['b1', 'b2', 'b3', 'b4', 'b5', 'b6'] as $code)
                        @php
                            $ind = $team['indicators'][$code] ?? null;
                        @endphp
                        @if ($ind)
                            <div class="space-y-1.5">
                                <!-- Linha 1: Título e Soft Badge -->
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-xs font-bold text-[#16302c] truncate" title="{{ $ind['name'] }}">
                                        {{ $ind['name'] }}
                                    </span>
                                    <span class="rounded-full px-2 py-0.2 text-[10px] font-bold border shrink-0 {{ $ind['badge_class'] }}">
                                        {{ $ind['badge_label'] }}
                                    </span>
                                </div>

                                <!-- Linha 2: Pontuação e Denominador -->
                                <div class="text-[11px] text-slate-600 font-mono flex items-center justify-between">
                                    <span>Pontuação: <strong class="text-slate-800">{{ number_format($ind['score'], 2, ',', '.') }}</strong></span>
                                    <span class="text-slate-500">Denom.: {{ number_format($ind['denominator'], 0, ',', '.') }}</span>
                                </div>

                                <!-- Linha 3: Barra de Progresso com Cor do Nível -->
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div
                                        class="h-full {{ $ind['bar_color'] }} rounded-full transition-all duration-300"
                                        style="width: {{ $ind['bar_width'] }}%;"
                                    ></div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-[#dce6e2] p-12 text-center text-slate-500">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <h3 class="text-sm font-bold text-slate-700">Nenhuma equipe encontrada</h3>
                <p class="text-xs text-slate-400 mt-1">Verifique os filtros selecionados ou selecione outro mês.</p>
            </div>
        @endforelse
    </div>

    <!-- RODAPÉ DA LISTA: TOTAL DE REGISTROS E PAGINAÇÃO (SCREENSHOT 3) -->
    <div class="px-5 py-4 bg-white rounded-2xl border border-[#dce6e2] shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-xs font-semibold text-slate-600">
            Total de Registros: <span class="font-mono text-teal-800 font-bold">{{ $totalTeams }}</span>
        </div>

        @if ($teams->hasPages())
            <div>
                {{ $teams->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL DE BUSCA AVANÇADA (SCREENSHOT 1) -->
    @if ($advancedModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-xl border border-[#dce6e2] w-full max-w-xl p-6 overflow-hidden">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                    <h3 class="text-base font-bold text-[#16302c] flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Busca Avançada · Dashboard Mensal</span>
                    </h3>
                    <button type="button" wire:click="closeAdvancedModal" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 transition cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="py-5 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Mês de Referência</label>
                        <select wire:model="advMonth" class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden">
                            @foreach ($availableMonths as $m)
                                <option value="{{ $m }}">{{ $selectedYear }} / M{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Unidade / CNES</label>
                        <select wire:model="advCnes" class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden">
                            <option value="">Todas as Unidades</option>
                            @foreach ($unitOptions as $u)
                                <option value="{{ $u->cnes }}">{{ $u->cnes }} - {{ $u->facility_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Equipe de Saúde Bucal (eSB)</label>
                        <select wire:model="advIne" class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden">
                            <option value="">Todas as Equipes eSB</option>
                            @foreach ($teamOptions as $t)
                                <option value="{{ $t->ine }}">{{ $t->team_name }} (INE: {{ $t->ine }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-3">
                    <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-slate-500 hover:text-rose-600 transition cursor-pointer">
                        Limpar Filtros
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeAdvancedModal" class="px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition cursor-pointer">
                            ✕ Fechar
                        </button>
                        <button type="button" wire:click="applyAdvancedFilters" class="px-5 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                            ✓ Enviar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
