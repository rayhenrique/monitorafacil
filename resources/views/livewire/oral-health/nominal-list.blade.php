<div class="app-page space-y-6">

    <!-- Navegação Superior / Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-5 rounded-2xl border border-[#dce6e2] shadow-xs">
        <div>
            <nav class="flex items-center gap-2 text-xs text-[#58716b] mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#16302c] transition">Início</a>
                <span>/</span>
                <a href="{{ route('oral-health.overview') }}" class="hover:text-[#16302c] transition">Saúde Bucal</a>
                <span>/</span>
                <span class="text-teal-800 font-semibold">Busca Geral Nominal</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#16302c] flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-teal-50 text-teal-700 border border-teal-200/80">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 4c-2.5 0-4 2-4 5.5 0 3.5 1.5 6 3 9.5 1 2.3 2 4.5 3 4.5s1-1.5 2-4c.5-1.3 1-2.5 1-2.5s.5 1.2 1 2.5c1 2.5 1 4 2 4s2-2.2 3-4.5c1.5-3.5 3-6 3-9.5C21 6 19.5 4 17 4c-2 0-3.5 1.5-5 1.5S9 4 7 4z" />
                    </svg>
                </span>
                <span>Componente de Qualidade / Saúde Bucal</span>
            </h1>
            <p class="text-xs text-[#58716b] mt-1 font-medium">
                Busca ativa geral de cidadãos com acompanhamento nominal dos 6 indicadores odontológicos (B1 a B6) e situação cadastral territorial.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Link para o Painel Geral -->
            <a href="{{ route('oral-health.overview') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-[#16302c] text-xs font-semibold border border-[#dce6e2] transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span>Painel de Equipes</span>
            </a>

            <!-- Link para o Dashboard Mensal -->
            <a href="{{ route('oral-health.monthly') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 text-xs font-semibold border border-teal-200 transition">
                <svg class="w-4 h-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <span>Dashboard Mensal</span>
            </a>

            <!-- Exportar CSV -->
            <button
                type="button"
                wire:click="exportCsv"
                wire:loading.attr="disabled"
                wire:target="exportCsv"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-[#16302c] text-xs font-semibold border border-[#dce6e2] transition cursor-pointer disabled:opacity-50"
                title="Exportar relação nominal filtrada em CSV compatível com Excel"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span wire:loading.remove wire:target="exportCsv">Exportar CSV</span>
                <span wire:loading wire:target="exportCsv">Gerando...</span>
            </button>

            <!-- Botão Busca Avançada -->
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

    <!-- PAINEL SUPERIOR DE INDICADORES (CONFORME SCREENSHOT 1) -->
    <div class="bg-white rounded-2xl border border-[#dce6e2] shadow-xs overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200">
            <!-- Box Esquerdo: Mês de Referência -->
            <div class="lg:col-span-2 p-5 flex flex-col items-center justify-center text-center bg-slate-50/50">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Mês</span>
                <span class="text-xl sm:text-2xl font-bold text-[#16302c] font-mono tracking-tight">
                    {{ $selectedYear }} / M09
                </span>
                <span class="mt-1 text-[11px] text-teal-700 font-medium bg-teal-50 px-2 py-0.5 rounded border border-teal-200/80">
                    Quadrimestre Q{{ $selectedQuarter }}
                </span>
            </div>

            <!-- Box Central: 6 Cards dos Indicadores Odontológicos B1 a B6 (Grid 3x2) -->
            <div class="lg:col-span-8 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <!-- B1 -->
                    <div class="bg-slate-50/60 hover:bg-teal-50/40 p-3 rounded-xl border border-slate-200/80 transition">
                        <div class="flex items-center justify-between gap-1 text-[11px] font-semibold text-slate-600 mb-1">
                            <span class="truncate">T. de Primeira C. Programadas (B1)</span>
                            <span class="text-slate-400 cursor-help shrink-0" title="Primeira consulta odontológica programática">ℹ</span>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-lg font-bold text-teal-700 font-mono">{{ number_format($kpis['b1']['num'], 0, ',', '.') }}</span>
                            <span class="text-xs font-semibold text-slate-600">({{ number_format($kpis['b1']['rate'], 2, ',', '.') }}%)</span>
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            Denominador: <span class="font-mono font-medium">{{ number_format($kpis['b1']['den'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- B2 -->
                    <div class="bg-slate-50/60 hover:bg-teal-50/40 p-3 rounded-xl border border-slate-200/80 transition">
                        <div class="flex items-center justify-between gap-1 text-[11px] font-semibold text-slate-600 mb-1">
                            <span class="truncate">T. de Tratamento Concluídos (B2)</span>
                            <span class="text-slate-400 cursor-help shrink-0" title="Tratamento odontológico concluído">ℹ</span>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-lg font-bold text-teal-700 font-mono">{{ number_format($kpis['b2']['num'], 0, ',', '.') }}</span>
                            <span class="text-xs font-semibold text-slate-600">({{ number_format($kpis['b2']['rate'], 2, ',', '.') }}%)</span>
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            Denominador: <span class="font-mono font-medium">{{ number_format($kpis['b2']['den'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- B3 -->
                    <div class="bg-slate-50/60 hover:bg-teal-50/40 p-3 rounded-xl border border-slate-200/80 transition">
                        <div class="flex items-center justify-between gap-1 text-[11px] font-semibold text-slate-600 mb-1">
                            <span class="truncate">T. de Exodontias (B3)</span>
                            <span class="text-slate-400 cursor-help shrink-0" title="Taxa de exodontia permanente (menor é melhor)">ℹ</span>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-lg font-bold text-teal-700 font-mono">{{ number_format($kpis['b3']['num'], 0, ',', '.') }}</span>
                            <span class="text-xs font-semibold text-slate-600">({{ number_format($kpis['b3']['rate'], 2, ',', '.') }}%)</span>
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            Denominador: <span class="font-mono font-medium">{{ number_format($kpis['b3']['den'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- B4 -->
                    <div class="bg-slate-50/60 hover:bg-teal-50/40 p-3 rounded-xl border border-slate-200/80 transition">
                        <div class="flex items-center justify-between gap-1 text-[11px] font-semibold text-slate-600 mb-1">
                            <span class="truncate">Escovação Supervisionada (B4)</span>
                            <span class="text-slate-400 cursor-help shrink-0" title="Ação coletiva de escovação dental supervisionada (6 a 12 anos)">ℹ</span>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-lg font-bold text-teal-700 font-mono">{{ number_format($kpis['b4']['num'], 0, ',', '.') }}</span>
                            <span class="text-xs font-semibold text-slate-600">({{ number_format($kpis['b4']['rate'], 2, ',', '.') }}%)</span>
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            Denominador: <span class="font-mono font-medium">{{ number_format($kpis['b4']['den'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- B5 -->
                    <div class="bg-slate-50/60 hover:bg-teal-50/40 p-3 rounded-xl border border-slate-200/80 transition">
                        <div class="flex items-center justify-between gap-1 text-[11px] font-semibold text-slate-600 mb-1">
                            <span class="truncate">T. de Preventivos (B5)</span>
                            <span class="text-slate-400 cursor-help shrink-0" title="Procedimentos odontológicos individuais preventivos">ℹ</span>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-lg font-bold text-teal-700 font-mono">{{ number_format($kpis['b5']['num'], 0, ',', '.') }}</span>
                            <span class="text-xs font-semibold text-slate-600">({{ number_format($kpis['b5']['rate'], 2, ',', '.') }}%)</span>
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            Denominador: <span class="font-mono font-medium">{{ number_format($kpis['b5']['den'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- B6 -->
                    <div class="bg-slate-50/60 hover:bg-teal-50/40 p-3 rounded-xl border border-slate-200/80 transition">
                        <div class="flex items-center justify-between gap-1 text-[11px] font-semibold text-slate-600 mb-1">
                            <span class="truncate">T. de Rest. Atraumáticas (B6)</span>
                            <span class="text-slate-400 cursor-help shrink-0" title="Tratamento Restaurador Atraumático (ART/TRA)">ℹ</span>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-lg font-bold text-teal-700 font-mono">{{ number_format($kpis['b6']['num'], 0, ',', '.') }}</span>
                            <span class="text-xs font-semibold text-slate-600">({{ number_format($kpis['b6']['rate'], 2, ',', '.') }}%)</span>
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            Denominador: <span class="font-mono font-medium">{{ number_format($kpis['b6']['den'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box Direito: Denominador Geral -->
            <div class="lg:col-span-2 p-5 flex flex-col items-center justify-center text-center bg-slate-50/50">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 flex items-center gap-1">
                    <span>Denominador Geral</span>
                    <span class="text-slate-400 cursor-help" title="População vinculada e cadastrada na APS de referência">ℹ</span>
                </span>
                <span class="text-2xl sm:text-3xl font-extrabold text-[#16302c] font-mono tracking-tight">
                    {{ number_format($kpis['general_denominator'], 0, ',', '.') }}
                </span>
                <span class="mt-1 text-[10px] text-slate-500">
                    Cidadãos da APS
                </span>
            </div>
        </div>
    </div>

    <!-- BARRA DE FILTROS RÁPIDOS E CONTROLE DE COLUNAS (CONFORME SCREENSHOT 1 & 2) -->
    <div class="bg-white p-4 rounded-2xl border border-[#dce6e2] shadow-xs">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <!-- Inputs Rápidos -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2.5 flex-1">
                <div>
                    <label for="filter-cns" class="sr-only">CNS</label>
                    <input
                        id="filter-cns"
                        type="text"
                        wire:model.live.debounce.300ms="filterCns"
                        placeholder="CNS"
                        class="w-full rounded-lg border border-[#dce6e2] bg-white px-3 py-2 text-xs font-medium text-[#16302c] placeholder-slate-400 focus:border-teal-600 focus:outline-hidden focus:ring-1 focus:ring-teal-600"
                    />
                </div>

                <div>
                    <label for="filter-cpf" class="sr-only">CPF</label>
                    <input
                        id="filter-cpf"
                        type="text"
                        wire:model.live.debounce.300ms="filterCpf"
                        placeholder="CPF"
                        class="w-full rounded-lg border border-[#dce6e2] bg-white px-3 py-2 text-xs font-medium text-[#16302c] placeholder-slate-400 focus:border-teal-600 focus:outline-hidden focus:ring-1 focus:ring-teal-600"
                    />
                </div>

                <div class="col-span-2 sm:col-span-1 md:col-span-2">
                    <label for="filter-name" class="sr-only">Filtrar por Nome</label>
                    <input
                        id="filter-name"
                        type="text"
                        wire:model.live.debounce.300ms="filterName"
                        placeholder="Filtrar por Nome..."
                        class="w-full rounded-lg border border-[#dce6e2] bg-white px-3 py-2 text-xs font-medium text-[#16302c] placeholder-slate-400 focus:border-teal-600 focus:outline-hidden focus:ring-1 focus:ring-teal-600"
                    />
                </div>

                <div>
                    <label for="filter-cnes" class="sr-only">CNES</label>
                    <select
                        id="filter-cnes"
                        wire:model.live="filterCnes"
                        class="w-full rounded-lg border border-[#dce6e2] bg-white px-3 py-2 text-xs font-medium text-[#16302c] focus:border-teal-600 focus:outline-hidden focus:ring-1 focus:ring-teal-600 cursor-pointer"
                    >
                        <option value="">CNES (Todos)</option>
                        @foreach ($units as $u)
                            <option value="{{ $u->cnes }}">{{ $u->cnes }} - {{ $u->facility_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-ine" class="sr-only">INE</label>
                    <select
                        id="filter-ine"
                        wire:model.live="filterIne"
                        class="w-full rounded-lg border border-[#dce6e2] bg-white px-3 py-2 text-xs font-medium text-[#16302c] focus:border-teal-600 focus:outline-hidden focus:ring-1 focus:ring-teal-600 cursor-pointer"
                    >
                        <option value="">INE (Todas)</option>
                        @foreach ($teams as $t)
                            <option value="{{ $t->ine }}">{{ $t->team_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Controles à Direita: Itens por Página & Dropdown de Colunas Visíveis -->
            <div class="flex items-center gap-2 shrink-0">
                <div class="w-20">
                    <label for="filter-per-page" class="sr-only">Itens por Página</label>
                    <select
                        id="filter-per-page"
                        wire:model.live="perPage"
                        class="w-full rounded-lg border border-[#dce6e2] bg-white px-2.5 py-2 text-xs font-semibold text-[#16302c] focus:border-teal-600 focus:outline-hidden focus:ring-1 focus:ring-teal-600 cursor-pointer"
                    >
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <!-- Dropdown de Colunas Visíveis -->
                <div class="relative" x-data="{ open: @entangle('columnsDropdownOpen') }">
                    <button
                        type="button"
                        @click="open = !open"
                        class="inline-flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-[#dce6e2] bg-white text-xs font-semibold text-[#16302c] hover:bg-slate-50 transition cursor-pointer"
                    >
                        <span>Colunas visíveis:</span>
                        <span class="text-teal-700 font-bold">{{ $activeColumnsCount }} selecionadas</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition
                        class="absolute right-0 mt-1.5 w-64 bg-white border border-[#dce6e2] rounded-xl shadow-lg p-3 z-30 space-y-2 text-xs"
                        style="display: none;"
                    >
                        <div class="font-bold text-[#16302c] border-b border-slate-100 pb-1.5">Alternar Visualização de Colunas</div>
                        <div class="grid grid-cols-2 gap-1.5 max-h-60 overflow-y-auto pr-1">
                            @foreach ($visibleColumns as $colKey => $isActive)
                                <label class="flex items-center gap-1.5 cursor-pointer hover:bg-slate-50 p-1 rounded">
                                    <input type="checkbox" wire:click="toggleColumn('{{ $colKey }}')" @checked($isActive) class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                                    <span class="text-slate-700 uppercase font-mono text-[11px]">{{ strtoupper($colKey) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($filterCns || $filterCpf || $filterName || $filterCnes || $filterIne || $advDistrict || $advMicroarea || $advMiciUpdated || $advMicdtUpdated || $advB1 || $advB2 || $advB3 || $advB4 || $advB5 || $advB6 || $advOnlyWithoutBond)
            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-slate-500 font-medium">Filtros ativos:</span>
                    @if ($filterCns) <span class="bg-teal-50 text-teal-800 px-2 py-0.5 rounded text-[11px] font-mono">CNS: {{ $filterCns }}</span> @endif
                    @if ($filterCpf) <span class="bg-teal-50 text-teal-800 px-2 py-0.5 rounded text-[11px] font-mono">CPF: {{ $filterCpf }}</span> @endif
                    @if ($filterName) <span class="bg-teal-50 text-teal-800 px-2 py-0.5 rounded text-[11px]">Nome: {{ $filterName }}</span> @endif
                    @if ($filterCnes) <span class="bg-teal-50 text-teal-800 px-2 py-0.5 rounded text-[11px]">CNES: {{ $filterCnes }}</span> @endif
                    @if ($filterIne) <span class="bg-teal-50 text-teal-800 px-2 py-0.5 rounded text-[11px]">INE: {{ $filterIne }}</span> @endif
                    @if ($advB1) <span class="bg-teal-50 text-teal-800 px-2 py-0.5 rounded text-[11px]">B1: {{ $advB1 }}</span> @endif
                    @if ($advB2) <span class="bg-teal-50 text-teal-800 px-2 py-0.5 rounded text-[11px]">B2: {{ $advB2 }}</span> @endif
                    @if ($advB4) <span class="bg-teal-50 text-teal-800 px-2 py-0.5 rounded text-[11px]">B4: {{ $advB4 }}</span> @endif
                </div>
                <button type="button" wire:click="clearFilters" class="text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                    Limpar todos os filtros ✕
                </button>
            </div>
        @endif
    </div>

    <!-- TABELA NOMINAL GERAL DE SAÚDE BUCAL (SCREENSHOT 2 & 3) -->
    <div class="bg-white rounded-2xl border border-[#dce6e2] shadow-xs overflow-hidden">
        <div class="overflow-x-auto min-w-0 scrollbar-gutter-stable">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-[#f5f7f6] text-[11px] font-bold text-[#58716b] uppercase tracking-wider select-none">
                        <th class="py-3 px-3">#</th>

                        @if ($visibleColumns['cns'] ?? true)
                            <th class="py-3 px-3">CNS</th>
                        @endif

                        @if ($visibleColumns['cpf'] ?? true)
                            <th class="py-3 px-3">CPF</th>
                        @endif

                        @if ($visibleColumns['birth_date'] ?? true)
                            <th class="py-3 px-3 cursor-pointer hover:text-[#16302c]" wire:click="sort('birth_date')">
                                <span class="flex items-center gap-1">
                                    <span>Nascimento</span>
                                    @if ($sortBy === 'birth_date') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </span>
                            </th>
                        @endif

                        @if ($visibleColumns['name'] ?? true)
                            <th class="py-3 px-4 cursor-pointer hover:text-[#16302c]" wire:click="sort('name')">
                                <span class="flex items-center gap-1">
                                    <span>Nome</span>
                                    @if ($sortBy === 'name') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </span>
                            </th>
                        @endif

                        @if ($visibleColumns['age'] ?? true)
                            <th class="py-3 px-2 text-center cursor-pointer hover:text-[#16302c]" wire:click="sort('age_years')">
                                <span>Idade</span>
                            </th>
                        @endif

                        @if ($visibleColumns['cnes'] ?? true)
                            <th class="py-3 px-3">Unidade</th>
                        @endif

                        @if ($visibleColumns['ine'] ?? true)
                            <th class="py-3 px-3">Equipe</th>
                        @endif

                        @if ($visibleColumns['professional'] ?? true)
                            <th class="py-3 px-3">Profissional</th>
                        @endif

                        @if ($visibleColumns['microarea'] ?? true)
                            <th class="py-3 px-2 text-center">Micro Área</th>
                        @endif

                        @if ($visibleColumns['mici'] ?? true)
                            <th class="py-3 px-3 text-center">MICI Atualizada?</th>
                        @endif

                        @if ($visibleColumns['b1'] ?? true)
                            <th class="py-3 px-2 text-center cursor-help" title="B1 - Primeira Consulta Odontológica Programática">(B1) ?</th>
                        @endif

                        @if ($visibleColumns['b2'] ?? true)
                            <th class="py-3 px-2 text-center cursor-help" title="B2 - Tratamento Odontológico Concluído">(B2) ?</th>
                        @endif

                        @if ($visibleColumns['b3'] ?? true)
                            <th class="py-3 px-2 text-center cursor-help" title="B3 - Taxa de Exodontia de Permanentes">(B3) ?</th>
                        @endif

                        @if ($visibleColumns['b4'] ?? true)
                            <th class="py-3 px-2 text-center cursor-help" title="B4 - Escovação Dental Supervisionada (6 a 12 anos)">(B4) ?</th>
                        @endif

                        @if ($visibleColumns['b5'] ?? true)
                            <th class="py-3 px-2 text-center cursor-help" title="B5 - Procedimentos Individuais Preventivos">(B5) ?</th>
                        @endif

                        @if ($visibleColumns['b6'] ?? true)
                            <th class="py-3 px-2 text-center cursor-help" title="B6 - Tratamento Restaurador Atraumático (ART/TRA)">(B6) ?</th>
                        @endif

                        @if ($visibleColumns['actions'] ?? true)
                            <th class="py-3 px-3 text-center">Ações</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($records as $item)
                        <tr class="hover:bg-teal-50/30 transition-colors" x-data="{ unmaskCns: false, unmaskCpf: false }">
                            <!-- # (ID PEC) -->
                            <td class="py-3 px-3 font-mono text-[11px] text-slate-500">
                                {{ $item->cidadao_pec_id }}
                            </td>

                            <!-- CNS com Toggle e Cópia -->
                            @if ($visibleColumns['cns'] ?? true)
                                <td class="py-3 px-3 font-mono text-slate-700 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span x-show="!unmaskCns">{{ $item->masked_cns }}</span>
                                        <span x-show="unmaskCns" style="display: none;">{{ $item->cns ?: '---' }}</span>
                                        @if ($item->cns)
                                            <button type="button" @click="unmaskCns = !unmaskCns" class="text-slate-400 hover:text-teal-700 cursor-pointer" title="Alternar visualização">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </button>
                                            <button type="button" @click="navigator.clipboard.writeText('{{ $item->cns }}')" class="text-slate-400 hover:text-teal-700 cursor-pointer" title="Copiar CNS">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endif

                            <!-- CPF com Toggle e Cópia -->
                            @if ($visibleColumns['cpf'] ?? true)
                                <td class="py-3 px-3 font-mono text-slate-700 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span x-show="!unmaskCpf">{{ $item->masked_cpf }}</span>
                                        <span x-show="unmaskCpf" style="display: none;">{{ $item->cpf ?: '---' }}</span>
                                        @if ($item->cpf)
                                            <button type="button" @click="unmaskCpf = !unmaskCpf" class="text-slate-400 hover:text-teal-700 cursor-pointer" title="Alternar visualização">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </button>
                                            <button type="button" @click="navigator.clipboard.writeText('{{ $item->cpf }}')" class="text-slate-400 hover:text-teal-700 cursor-pointer" title="Copiar CPF">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endif

                            <!-- Nascimento -->
                            @if ($visibleColumns['birth_date'] ?? true)
                                <td class="py-3 px-3 font-mono text-slate-600 whitespace-nowrap">
                                    {{ $item->birth_date?->format('d/m/Y') ?: '---' }}
                                </td>
                            @endif

                            <!-- Nome -->
                            @if ($visibleColumns['name'] ?? true)
                                <td class="py-3 px-4">
                                    <div class="font-bold text-[#16302c] hover:text-teal-800 transition cursor-pointer flex items-center gap-1.5" wire:click="openDetails({{ $item->id }})">
                                        <span class="truncate max-w-[220px]" title="{{ $item->name }}">{{ $item->name }}</span>
                                        <span class="text-slate-400 text-[10px]">ℹ</span>
                                    </div>
                                </td>
                            @endif

                            <!-- Idade -->
                            @if ($visibleColumns['age'] ?? true)
                                <td class="py-3 px-2 text-center font-mono font-bold text-slate-700">
                                    {{ $item->age_years }}
                                </td>
                            @endif

                            <!-- Unidade (CNES) -->
                            @if ($visibleColumns['cnes'] ?? true)
                                <td class="py-3 px-3 font-mono text-slate-600 whitespace-nowrap">
                                    <span title="{{ $item->facility_name ?: 'Estabelecimento de Saúde' }}" class="cursor-help flex items-center gap-1">
                                        <span>{{ $item->cnes ?: '---' }}</span>
                                        <span class="text-slate-400 text-[10px]">ℹ</span>
                                    </span>
                                </td>
                            @endif

                            <!-- Equipe (INE) -->
                            @if ($visibleColumns['ine'] ?? true)
                                <td class="py-3 px-3 font-mono text-slate-600 whitespace-nowrap">
                                    <span title="{{ $item->team_name ?: 'Equipe de Saúde' }}" class="cursor-help flex items-center gap-1">
                                        <span>{{ $item->ine ?: '---' }}</span>
                                        <span class="text-slate-400 text-[10px]">ℹ</span>
                                    </span>
                                </td>
                            @endif

                            <!-- Profissional -->
                            @if ($visibleColumns['professional'] ?? true)
                                <td class="py-3 px-3 font-mono text-slate-600 whitespace-nowrap">
                                    <span title="{{ $item->professional_name ?: ($item->last_professional_name ?: 'Profissional da APS') }}" class="cursor-help flex items-center gap-1">
                                        <span>{{ $item->professional_cns ?: ($item->cidadao_pec_id ? '70' . substr((string) $item->cidadao_pec_id, 0, 8) . '...' : '---') }}</span>
                                        <span class="text-slate-400 text-[10px]">ℹ</span>
                                    </span>
                                </td>
                            @endif

                            <!-- Micro Área -->
                            @if ($visibleColumns['microarea'] ?? true)
                                <td class="py-3 px-2 text-center font-mono text-slate-700">
                                    {{ $item->microarea ?: '00' }}
                                </td>
                            @endif

                            <!-- MICI Atualizada? -->
                            @if ($visibleColumns['mici'] ?? true)
                                <td class="py-3 px-3 text-center">
                                    @if ($item->mici_updated)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            Sim
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            Não
                                        </span>
                                    @endif
                                </td>
                            @endif

                            <!-- (B1) -->
                            @if ($visibleColumns['b1'] ?? true)
                                <td class="py-3 px-2 text-center font-mono">
                                    @if ($item->b1_count > 0)
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-emerald-500 text-white shadow-2xs">
                                            {{ $item->b1_count }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-rose-500 text-white shadow-2xs">
                                            0
                                        </span>
                                    @endif
                                </td>
                            @endif

                            <!-- (B2) -->
                            @if ($visibleColumns['b2'] ?? true)
                                <td class="py-3 px-2 text-center font-mono">
                                    @if ($item->b2_count > 0)
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-emerald-500 text-white shadow-2xs">
                                            {{ $item->b2_count }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-rose-500 text-white shadow-2xs">
                                            0
                                        </span>
                                    @endif
                                </td>
                            @endif

                            <!-- (B3) -->
                            @if ($visibleColumns['b3'] ?? true)
                                <td class="py-3 px-2 text-center font-mono">
                                    @if ($item->b3_count > 0)
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-amber-500 text-white shadow-2xs">
                                            {{ $item->b3_count }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-sky-500 text-white shadow-2xs">
                                            0
                                        </span>
                                    @endif
                                </td>
                            @endif

                            <!-- (B4) -->
                            @if ($visibleColumns['b4'] ?? true)
                                <td class="py-3 px-2 text-center font-mono">
                                    @if (! $item->b4_eligible)
                                        <span class="inline-flex items-center justify-center px-1.5 h-[22px] rounded-full text-[10px] font-bold bg-sky-500 text-white shadow-2xs" title="Não elegível por idade (restrito a 6-12 anos)">
                                            NA
                                        </span>
                                    @elseif ($item->b4_count > 0)
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-emerald-500 text-white shadow-2xs">
                                            {{ $item->b4_count }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-rose-500 text-white shadow-2xs">
                                            0
                                        </span>
                                    @endif
                                </td>
                            @endif

                            <!-- (B5) -->
                            @if ($visibleColumns['b5'] ?? true)
                                <td class="py-3 px-2 text-center font-mono">
                                    @if ($item->b5_count > 0)
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-emerald-500 text-white shadow-2xs">
                                            {{ $item->b5_count }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-sky-500 text-white shadow-2xs">
                                            0
                                        </span>
                                    @endif
                                </td>
                            @endif

                            <!-- (B6) -->
                            @if ($visibleColumns['b6'] ?? true)
                                <td class="py-3 px-2 text-center font-mono">
                                    @if ($item->b6_count > 0)
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-emerald-500 text-white shadow-2xs">
                                            {{ $item->b6_count }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-[11px] font-bold bg-sky-500 text-white shadow-2xs">
                                            0
                                        </span>
                                    @endif
                                </td>
                            @endif

                            <!-- Ações: Botão Detalhes -->
                            @if ($visibleColumns['actions'] ?? true)
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <button
                                        type="button"
                                        wire:click="openDetails({{ $item->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-[11px] font-semibold shadow-2xs transition cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
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
                            <td colspan="18" class="py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-700">Nenhum cidadão encontrado</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Tente ajustar ou limpar os filtros para visualizar os dados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- RODAPÉ DA TABELA: TOTAL DE REGISTROS E PAGINAÇÃO (SCREENSHOT 4) -->
        <div class="px-5 py-4 border-t border-slate-200 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-xs font-semibold text-slate-600">
                Total de Registros: <span class="font-mono text-teal-800 font-bold">{{ number_format($totalRecordsCount, 0, ',', '.') }}</span>
            </div>

            @if ($records->hasPages())
                <div>
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- LEGENDA DOS INDICADORES DE SAÚDE BUCAL (ACCORDION CONFORME SCREENSHOT 4) -->
    <div class="bg-white rounded-2xl border border-[#dce6e2] shadow-xs overflow-hidden">
        <button
            type="button"
            wire:click="toggleLegend"
            class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-slate-50/50 transition cursor-pointer select-none"
        >
            <h2 class="text-sm sm:text-base font-bold text-teal-900 flex items-center gap-2">
                <span>Legenda dos Indicadores de Saúde Bucal</span>
            </h2>
            <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': ! {{ $legendOpen ? 'true' : 'false' }} }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        @if ($legendOpen)
            <div class="px-5 pb-5 pt-1 space-y-3.5 border-t border-slate-100 text-xs">
                <!-- B1 -->
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-800 font-bold font-mono shrink-0 text-xs">
                        B1
                    </span>
                    <div>
                        <div class="font-bold text-[#16302c]">Primeira Consulta Programada</div>
                        <div class="text-[#58716b] mt-0.5 leading-relaxed">
                            Mensura o acesso da população à primeira consulta odontológica programática realizada pela eSB vinculada à eSF/eAP de referência.
                        </div>
                    </div>
                </div>

                <!-- B2 -->
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-800 font-bold font-mono shrink-0 text-xs">
                        B2
                    </span>
                    <div>
                        <div class="font-bold text-[#16302c]">Tratamento Concluído</div>
                        <div class="text-[#58716b] mt-0.5 leading-relaxed">
                            Mensura a cobertura proporcional de tratamentos concluídos em relação às primeiras consultas odontológicas programáticas, realizados pela equipe de Saúde Bucal vinculada à equipe de Saúde da Família (eSF) ou às equipes de Atenção Primária (eAP) de referência.
                        </div>
                    </div>
                </div>

                <!-- B3 -->
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-800 font-bold font-mono shrink-0 text-xs">
                        B3
                    </span>
                    <div>
                        <div class="font-bold text-[#16302c]">Taxa de exodontias</div>
                        <div class="text-[#58716b] mt-0.5 leading-relaxed">
                            Mede a relação entre o total de exodontias e o total de procedimentos preventivos e curativos realizados pelo cirurgião-dentista da eSB inserida na APS. Polaridade: menor é melhor.
                        </div>
                    </div>
                </div>

                <!-- B4 -->
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-800 font-bold font-mono shrink-0 text-xs">
                        B4
                    </span>
                    <div>
                        <div class="font-bold text-[#16302c]">Escovação Supervisionada</div>
                        <div class="text-[#58716b] mt-0.5 leading-relaxed">
                            Mensurar a proporção de crianças de 6 a 12 anos, vinculadas à eSF/eAP de referência, beneficiárias das ações coletivas de escovação dental com orientação/supervisão da equipe de Saúde Bucal.
                        </div>
                    </div>
                </div>

                <!-- B5 -->
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-800 font-bold font-mono shrink-0 text-xs">
                        B5
                    </span>
                    <div>
                        <div class="font-bold text-[#16302c]">Procedimentos odontológicos individuais preventivos</div>
                        <div class="text-[#58716b] mt-0.5 leading-relaxed">
                            Mensurar o total de procedimentos odontológicos individuais preventivos em relação ao total de procedimentos odontológicos individuais realizados pela equipe de Saúde Bucal inserida na APS.
                        </div>
                    </div>
                </div>

                <!-- B6 -->
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-800 font-bold font-mono shrink-0 text-xs">
                        B6
                    </span>
                    <div>
                        <div class="font-bold text-[#16302c]">Tratamento Restaurador Atraumático (ART)</div>
                        <div class="text-[#58716b] mt-0.5 leading-relaxed">
                            Mensurar a proporção entre o total de procedimentos "Tratamento Restaurador Atraumático" em relação ao total de procedimentos restauradores realizados pela eSB.
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- MODAL: BUSCA AVANÇADA (SCREENSHOT 5) -->
    @if ($advancedModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-xl border border-[#dce6e2] w-full max-w-4xl p-6 overflow-hidden">
                <!-- Cabeçalho do Modal -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-[#16302c] flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Busca Avançada · Saúde Bucal</span>
                    </h3>
                    <button type="button" wire:click="closeAdvancedModal" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Formulário de Filtros Avançados -->
                <div class="py-5 space-y-4 max-h-[75vh] overflow-y-auto pr-1 text-xs">
                    <!-- Linha 1: Distrito, Unidade, Equipes, Microárea -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Distrito</label>
                            <select wire:model="advDistrict" class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden">
                                <option value="">Selecione a opção desejada</option>
                                @foreach ($districts as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Unidade</label>
                            <select wire:model="advCnes" class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden">
                                <option value="">Selecione a opção desejada</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->cnes }}">{{ $u->cnes }} - {{ $u->facility_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Equipes</label>
                            <select wire:model="advIne" class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden">
                                <option value="">Selecione a opção desejada</option>
                                @foreach ($teams as $t)
                                    <option value="{{ $t->ine }}">{{ $t->team_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Microárea</label>
                            <input type="text" wire:model="advMicroarea" placeholder="Ex: 01, 02..." class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden" />
                        </div>
                    </div>

                    <!-- Linha 2: Nome do Cidadão, Nome da Mãe -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nome do Cidadão</label>
                            <input type="text" wire:model="advCitizenName" placeholder="Digite o nome do cidadão..." class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nome da Mãe</label>
                            <input type="text" wire:model="advMotherName" placeholder="Digite o nome da mãe..." class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden" />
                        </div>
                    </div>

                    <!-- Linha 3: CPF Cidadão, CNS Cidadão, Mês -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">CPF Cidadão</label>
                            <input type="text" wire:model="advCpf" placeholder="Apenas dígitos ou formatado" class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">CNS Cidadão</label>
                            <input type="text" wire:model="advCns" placeholder="Cartão Nacional de Saúde" class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Mês</label>
                            <select wire:model="advMonth" class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden">
                                <option value="09 / 2026">09 / 2026</option>
                                <option value="08 / 2026">08 / 2026</option>
                                <option value="07 / 2026">07 / 2026</option>
                                <option value="06 / 2026">06 / 2026</option>
                                <option value="05 / 2026">05 / 2026</option>
                            </select>
                        </div>
                    </div>

                    <!-- Linha 4: Nome Profissional, CNS Profissional -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nome Profissional (TACS/ACS/Dentista)</label>
                            <input type="text" wire:model="advProfName" placeholder="Nome do profissional..." class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">CNS Profissional (TACS/ACS/Dentista)</label>
                            <input type="text" wire:model="advProfCns" placeholder="CNS do profissional..." class="w-full rounded-lg border border-[#dce6e2] px-3 py-2 text-xs text-[#16302c] focus:border-teal-600 focus:outline-hidden" />
                        </div>
                    </div>

                    <!-- Linha 5: MICI Atualizada? e MICDT Atualizada? -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">MICI Atualizada? ℹ</label>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="$set('advMiciUpdated', advMiciUpdated === 'SIM' ? null : 'SIM')" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer {{ $advMiciUpdated === 'SIM' ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200' }}">SIM</button>
                                <button type="button" wire:click="$set('advMiciUpdated', advMiciUpdated === 'NAO' ? null : 'NAO')" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer {{ $advMiciUpdated === 'NAO' ? 'bg-rose-600 text-white border-rose-700' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200' }}">NÃO</button>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">MICDT Atualizada? ℹ</label>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="$set('advMicdtUpdated', advMicdtUpdated === 'SIM' ? null : 'SIM')" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer {{ $advMicdtUpdated === 'SIM' ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200' }}">SIM</button>
                                <button type="button" wire:click="$set('advMicdtUpdated', advMicdtUpdated === 'NAO' ? null : 'NAO')" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer {{ $advMicdtUpdated === 'NAO' ? 'bg-rose-600 text-white border-rose-700' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200' }}">NÃO</button>
                            </div>
                        </div>
                    </div>

                    <!-- Linha 6: Filtros dos 6 Indicadores B1 a B6 [SIM] [NÃO] -->
                    <div class="pt-2 border-t border-slate-100">
                        <div class="font-bold text-[#16302c] mb-3">Filtros por Indicadores de Saúde Bucal</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            <!-- B1 -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <label class="block font-semibold text-slate-700 mb-1.5 truncate">Primeira Consulta (B1)</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="$set('advB1', advB1 === 'SIM' ? null : 'SIM')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB1 === 'SIM' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">SIM</button>
                                    <button type="button" wire:click="$set('advB1', advB1 === 'NAO' ? null : 'NAO')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB1 === 'NAO' ? 'bg-rose-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">NÃO</button>
                                </div>
                            </div>

                            <!-- B2 -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <label class="block font-semibold text-slate-700 mb-1.5 truncate">Tratamento Concluído (B2)</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="$set('advB2', advB2 === 'SIM' ? null : 'SIM')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB2 === 'SIM' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">SIM</button>
                                    <button type="button" wire:click="$set('advB2', advB2 === 'NAO' ? null : 'NAO')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB2 === 'NAO' ? 'bg-rose-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">NÃO</button>
                                </div>
                            </div>

                            <!-- B3 -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <label class="block font-semibold text-slate-700 mb-1.5 truncate">Taxa de exodontias (B3)</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="$set('advB3', advB3 === 'SIM' ? null : 'SIM')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB3 === 'SIM' ? 'bg-amber-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">SIM</button>
                                    <button type="button" wire:click="$set('advB3', advB3 === 'NAO' ? null : 'NAO')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB3 === 'NAO' ? 'bg-slate-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">NÃO</button>
                                </div>
                            </div>

                            <!-- B4 -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <label class="block font-semibold text-slate-700 mb-1.5 truncate">Escovação Supervisionada (B4)</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="$set('advB4', advB4 === 'SIM' ? null : 'SIM')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB4 === 'SIM' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">SIM</button>
                                    <button type="button" wire:click="$set('advB4', advB4 === 'NAO' ? null : 'NAO')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB4 === 'NAO' ? 'bg-rose-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">NÃO</button>
                                </div>
                            </div>

                            <!-- B5 -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <label class="block font-semibold text-slate-700 mb-1.5 truncate">Preventivos Individuais (B5)</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="$set('advB5', advB5 === 'SIM' ? null : 'SIM')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB5 === 'SIM' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">SIM</button>
                                    <button type="button" wire:click="$set('advB5', advB5 === 'NAO' ? null : 'NAO')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB5 === 'NAO' ? 'bg-slate-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">NÃO</button>
                                </div>
                            </div>

                            <!-- B6 -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <label class="block font-semibold text-slate-700 mb-1.5 truncate">Restauração ART (B6)</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="$set('advB6', advB6 === 'SIM' ? null : 'SIM')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB6 === 'SIM' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">SIM</button>
                                    <button type="button" wire:click="$set('advB6', advB6 === 'NAO' ? null : 'NAO')" class="px-3 py-1 rounded text-xs font-bold transition cursor-pointer {{ $advB6 === 'NAO' ? 'bg-slate-600 text-white' : 'bg-white text-slate-700 border border-slate-300' }}">NÃO</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle: Listar somente cidadãos sem vínculo -->
                    <div class="pt-2">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" wire:model="advOnlyWithoutBond" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 h-4 w-4">
                            <span class="font-semibold text-slate-700">Listar somente cidadãos sem vínculo territorial</span>
                        </label>
                    </div>
                </div>

                <!-- Rodapé do Modal -->
                <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-3">
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="text-xs font-semibold text-slate-500 hover:text-rose-600 transition cursor-pointer"
                    >
                        Limpar todos os campos
                    </button>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="closeAdvancedModal"
                            class="px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition cursor-pointer"
                        >
                            ✕ Fechar
                        </button>
                        <button
                            type="button"
                            wire:click="applyAdvancedFilters"
                            class="px-5 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold shadow-xs transition cursor-pointer"
                        >
                            ✓ Enviar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: DETALHES CLÍNICOS DO CIDADÃO (AO CLICAR EM DETALHES) -->
    @if ($detailsModalOpen && $selectedCitizen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-xl border border-[#dce6e2] w-full max-w-2xl p-6 overflow-hidden">
                <!-- Cabeçalho -->
                <div class="flex items-start justify-between pb-4 border-b border-slate-200">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-[#16302c]">{{ $selectedCitizen->name }}</h3>
                            <span class="text-xs px-2 py-0.5 rounded font-mono font-semibold {{ $selectedCitizen->treatment_status === 'concluido' ? 'bg-emerald-100 text-emerald-800' : ($selectedCitizen->treatment_status === 'em_andamento' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ strtoupper($selectedCitizen->treatment_status) }}
                            </span>
                        </div>
                        <div class="text-xs text-[#58716b] mt-1 flex flex-wrap items-center gap-2 font-mono">
                            <span>CPF: {{ $selectedCitizen->cpf ?: 'Não informado' }}</span>
                            <span>•</span>
                            <span>CNS: {{ $selectedCitizen->cns ?: 'Não informado' }}</span>
                            <span>•</span>
                            <span>{{ $selectedCitizen->age_years }} anos ({{ $selectedCitizen->birth_date?->format('d/m/Y') }})</span>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetails" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Conteúdo -->
                <div class="py-5 space-y-4 text-xs">
                    <!-- Resumo dos Indicadores Odontológicos B1 a B6 -->
                    <div>
                        <div class="font-bold text-[#16302c] uppercase tracking-wider text-[11px] mb-2">Desempenho nos 6 Indicadores (eSB)</div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50">
                                <div class="text-slate-500 text-[10px]">B1 · 1ª Consulta</div>
                                <div class="text-base font-bold {{ $selectedCitizen->b1_count > 0 ? 'text-emerald-700' : 'text-slate-600' }}">
                                    {{ $selectedCitizen->b1_count }} consulta(s)
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50">
                                <div class="text-slate-500 text-[10px]">B2 · Trat. Concluído</div>
                                <div class="text-base font-bold {{ $selectedCitizen->b2_count > 0 ? 'text-emerald-700' : 'text-slate-600' }}">
                                    {{ $selectedCitizen->b2_count }} conclusão(ões)
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50">
                                <div class="text-slate-500 text-[10px]">B3 · Exodontias</div>
                                <div class="text-base font-bold {{ $selectedCitizen->b3_count > 0 ? 'text-amber-700' : 'text-slate-600' }}">
                                    {{ $selectedCitizen->b3_count }} procedimento(s)
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50">
                                <div class="text-slate-500 text-[10px]">B4 · Escovação Superv.</div>
                                <div class="text-base font-bold {{ ! $selectedCitizen->b4_eligible ? 'text-slate-400' : ($selectedCitizen->b4_count > 0 ? 'text-emerald-700' : 'text-slate-600') }}">
                                    {{ ! $selectedCitizen->b4_eligible ? 'Não elegível (idade)' : $selectedCitizen->b4_count . ' ação(ões)' }}
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50">
                                <div class="text-slate-500 text-[10px]">B5 · Preventivos</div>
                                <div class="text-base font-bold {{ $selectedCitizen->b5_count > 0 ? 'text-emerald-700' : 'text-slate-600' }}">
                                    {{ $selectedCitizen->b5_count }} procedimento(s)
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50">
                                <div class="text-slate-500 text-[10px]">B6 · Restauração ART</div>
                                <div class="text-base font-bold {{ $selectedCitizen->b6_count > 0 ? 'text-emerald-700' : 'text-slate-600' }}">
                                    {{ $selectedCitizen->b6_count }} procedimento(s)
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datas Clínicas -->
                    <div class="p-3.5 rounded-xl bg-teal-50/50 border border-teal-200/80 space-y-1.5">
                        <div class="font-bold text-teal-950 uppercase tracking-wider text-[11px] mb-1">Linha do Tempo Odontológica</div>
                        <div class="grid grid-cols-2 gap-2 text-slate-700">
                            <div>Primeira Consulta: <span class="font-bold font-mono">{{ $selectedCitizen->first_consultation_date?->format('d/m/Y') ?: 'Não realizada' }}</span></div>
                            <div>Tratamento Concluído: <span class="font-bold font-mono">{{ $selectedCitizen->treatment_completed_date?->format('d/m/Y') ?: 'Não concluído' }}</span></div>
                            <div>Última Escovação: <span class="font-bold font-mono">{{ $selectedCitizen->last_brushing_date?->format('d/m/Y') ?: 'Nenhuma' }}</span></div>
                            <div>Último Atendimento: <span class="font-bold font-mono">{{ $selectedCitizen->last_attendance_date?->format('d/m/Y') ?: 'Nenhum' }}</span></div>
                        </div>
                    </div>

                    <!-- Vínculo e Equipe -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 space-y-1">
                        <div class="font-bold text-[#16302c] uppercase tracking-wider text-[11px] mb-1">Vínculo e Lotação</div>
                        <div>Estabelecimento: <span class="font-semibold">{{ $selectedCitizen->facility_name ?: '---' }} (CNES: {{ $selectedCitizen->cnes }})</span></div>
                        <div>Equipe: <span class="font-semibold">{{ $selectedCitizen->team_name ?: '---' }} (INE: {{ $selectedCitizen->ine }})</span></div>
                        <div>Microárea: <span class="font-semibold">{{ $selectedCitizen->microarea }}</span> • MICI Atualizada: <span class="font-semibold">{{ $selectedCitizen->mici_updated ? 'Sim' : 'Não' }}</span></div>
                        @if ($selectedCitizen->last_professional_name)
                            <div>Cirurgião-Dentista: <span class="font-semibold">{{ $selectedCitizen->last_professional_name }} (CBO: {{ $selectedCitizen->last_professional_cbo }})</span></div>
                        @endif
                    </div>
                </div>

                <!-- Rodapé -->
                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="button" wire:click="closeDetails" class="px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold transition cursor-pointer">
                        Fechar Detalhes
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
