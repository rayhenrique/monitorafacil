<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Navegação em Abas do Módulo -->
    <x-territorial-bonding-tabs
        title="Vínculo e Acompanhamento Territorial"
        subtitle="Monitoramento de Vínculo e Acompanhamento · Relação Nominal e Busca Ativa"
        activeTab="nominal"
    />

    <!-- Cabeçalho Oficial do Submódulo -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-3xl border border-line shadow-sm">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-ink flex items-center gap-2.5">
                <span class="text-teal-700">Monitoramento de Vínculo e Acompanhamento - Relação Nominal</span>
            </h1>
            <p class="text-xs text-muted mt-1 flex items-center gap-1.5">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                </svg>
                <span>Último atendimento registrado em <strong>{{ $metrics->last_record_date ? $metrics->last_record_date->format('d/m/Y') : '18/09/2026' }}</strong></span>
            </p>
        </div>

        <!-- Botão Busca Avançada -->
        <div>
            <button
                type="button"
                wire:click="openAdvancedModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition cursor-pointer"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <span>Busca Avançada</span>
            </button>
        </div>
    </div>

    <!-- BOX 1: DIMENSÃO CADASTRO (EXATA CONFORME IMAGEM 1) -->
    <div class="bg-white rounded-3xl border border-line shadow-panel overflow-hidden">
        <!-- Título da Seção -->
        <div class="bg-slate-50/60 py-2.5 px-4 text-center border-b border-line">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">DIMENSÃO CADASTRO</h2>
        </div>

        <!-- Grid de Métricas de Cadastro -->
        <div class="grid grid-cols-1 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <!-- Coluna 1: Mês -->
            <div class="p-5 flex flex-col items-center justify-center text-center">
                <span class="text-xs text-muted font-semibold mb-1">Mês</span>
                <span class="text-2xl font-black text-ink tracking-tight">{{ $metrics->year }} / M{{ str_pad((string) $metrics->month, 2, '0', STR_PAD_LEFT) }}</span>
            </div>

            <!-- Coluna 2: Total Geral de MICI -->
            <div class="p-5 space-y-3">
                <div>
                    <span class="text-xs text-slate-600 font-medium block">Total Geral de MICI</span>
                    <span class="text-2xl font-black text-ink tabular-nums">{{ number_format($metrics->mici_total, 0, '', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-500 block">MICI Atualizados</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-lg font-bold text-emerald-600 tabular-nums">{{ number_format($metrics->mici_updated, 0, '', '.') }}</span>
                        <span class="text-xs font-bold text-emerald-600">({{ $metrics->mici_total > 0 ? number_format(($metrics->mici_updated / $metrics->mici_total) * 100, 2, ',', '.') : 0 }}%)</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-500 block">MICI Desatualizados</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-lg font-bold text-rose-600 tabular-nums">{{ number_format($metrics->mici_outdated, 0, '', '.') }}</span>
                        <span class="text-xs font-bold text-rose-600">({{ $metrics->mici_total > 0 ? number_format(($metrics->mici_outdated / $metrics->mici_total) * 100, 2, ',', '.') : 0 }}%)</span>
                    </div>
                </div>
            </div>

            <!-- Coluna 3: Total Geral de MICI Sem MICDT -->
            <div class="p-5 space-y-3">
                <div>
                    <span class="text-xs text-slate-600 font-medium block">Total Geral de MICI Sem MICDT</span>
                    <span class="text-2xl font-black text-ink tabular-nums">{{ number_format($metrics->mici_without_micdt_total, 0, '', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-500 block">MICI Atua. e MICDT Desat. ou Sem</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-lg font-bold text-emerald-600 tabular-nums">{{ number_format($metrics->mici_updated_micdt_outdated_or_none, 0, '', '.') }}</span>
                        <span class="text-xs font-bold text-emerald-600">({{ $metrics->mici_total > 0 ? number_format(($metrics->mici_updated_micdt_outdated_or_none / $metrics->mici_total) * 100, 2, ',', '.') : 0 }}%)</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-500 block">MICI Atualizados e Sem MICDT</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-lg font-bold text-rose-600 tabular-nums">{{ number_format($metrics->mici_updated_without_micdt, 0, '', '.') }}</span>
                        <span class="text-xs font-bold text-rose-600">({{ $metrics->mici_without_micdt_total > 0 ? number_format(($metrics->mici_updated_without_micdt / $metrics->mici_without_micdt_total) * 100, 2, ',', '.') : 0 }}%)</span>
                    </div>
                </div>
            </div>

            <!-- Coluna 4: Total MICI Com MICDT -->
            <div class="p-5 space-y-3">
                <div>
                    <span class="text-xs text-slate-600 font-medium block">Total MICI Com MICDT</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-ink tabular-nums">{{ number_format($metrics->mici_with_micdt_total, 0, '', '.') }}</span>
                        <span class="text-xs font-bold text-slate-500">({{ $metrics->mici_total > 0 ? number_format(($metrics->mici_with_micdt_total / $metrics->mici_total) * 100, 2, ',', '.') : 0 }}%)</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-500 block">MICI e MICDT Atualizados</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-lg font-bold text-emerald-600 tabular-nums">{{ number_format($metrics->mici_and_micdt_updated, 0, '', '.') }}</span>
                        <span class="text-xs font-bold text-emerald-600">({{ $metrics->mici_with_micdt_total > 0 ? number_format(($metrics->mici_and_micdt_updated / $metrics->mici_with_micdt_total) * 100, 2, ',', '.') : 0 }}%)</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-500 block">MICI e MICDT Desatualizados</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-lg font-bold text-rose-600 tabular-nums">{{ number_format($metrics->mici_and_micdt_outdated, 0, '', '.') }}</span>
                        <span class="text-xs font-bold text-rose-600">({{ $metrics->mici_with_micdt_total > 0 ? number_format(($metrics->mici_and_micdt_outdated / $metrics->mici_with_micdt_total) * 100, 2, ',', '.') : 0 }}%)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra Inferior: Cidadãos Vinculados / Não Vinculados -->
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200 border-t border-slate-200 bg-slate-50/50 p-4 text-xs">
            <div class="flex items-center justify-between px-3">
                <span class="font-medium text-slate-700 flex items-center gap-1.5">
                    Cidadãos Vinculados
                    <span class="text-[10px] text-slate-400">ℹ️</span>
                </span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-base font-black text-emerald-600 tabular-nums">{{ number_format($metrics->citizens_linked, 0, '', '.') }}</span>
                    <span class="font-bold text-emerald-600">({{ $metrics->mici_total > 0 ? number_format(($metrics->citizens_linked / $metrics->mici_total) * 100, 2, ',', '.') : 0 }}%)</span>
                </div>
            </div>

            <div class="flex items-center justify-between px-3 pt-2 md:pt-0">
                <span class="font-medium text-slate-700">Cidadãos Não Vinculados</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-base font-black text-rose-600 tabular-nums">{{ number_format($metrics->citizens_not_linked, 0, '', '.') }}</span>
                    <span class="font-bold text-rose-600">({{ $metrics->mici_total > 0 ? number_format(($metrics->citizens_not_linked / $metrics->mici_total) * 100, 2, ',', '.') : 0 }}%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- BOX 2: DIMENSÃO ACOMPANHAMENTO (RETRÁTIL CONFORME IMAGEM 2) -->
    <div class="bg-white rounded-3xl border border-line shadow-panel overflow-hidden">
        <!-- Cabeçalho com Botão Retrátil -->
        <div
            wire:click="toggleAcompanhamento"
            class="bg-slate-50/60 py-2.5 px-5 flex items-center justify-between border-b border-line cursor-pointer hover:bg-slate-100/70 transition select-none"
        >
            <div class="w-6"></div>
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">DIMENSÃO ACOMPANHAMENTO</h2>
            <button type="button" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="h-4 w-4 transition-transform duration-200 {{ $acompanhamentoExpanded ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
        </div>

        <!-- Matriz de Vulnerabilidade e Acompanhamento -->
        @if ($acompanhamentoExpanded)
            <div class="grid grid-cols-1 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-100 p-1">
                <!-- Coluna 1: Sem Critério -->
                <div class="p-5 space-y-3">
                    <div>
                        <span class="text-xs text-slate-600 font-medium block">Sem Critério</span>
                        <span class="text-2xl font-black text-ink tabular-nums">{{ number_format($metrics->no_criteria_total, 0, '', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-500 block">Sem Critério Acompanhados</span>
                        <span class="text-lg font-bold text-emerald-600 tabular-nums block">{{ number_format($metrics->no_criteria_accompanied, 0, '', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-500 block">Sem Critério Não acompanhados</span>
                        <span class="text-lg font-bold text-rose-600 tabular-nums block">{{ number_format($metrics->no_criteria_not_accompanied, 0, '', '.') }}</span>
                    </div>
                </div>

                <!-- Coluna 2: Idoso ou Criança -->
                <div class="p-5 space-y-3">
                    <div>
                        <span class="text-xs text-slate-600 font-medium block">Idoso ou Criança</span>
                        <span class="text-2xl font-black text-ink tabular-nums">{{ number_format($metrics->elderly_or_child_total, 0, '', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-500 block">Idoso ou Criança Acompanhados</span>
                        <span class="text-lg font-bold text-emerald-600 tabular-nums block">{{ number_format($metrics->elderly_or_child_accompanied, 0, '', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-500 block">Idoso ou Criança Não acompanhados</span>
                        <span class="text-lg font-bold text-rose-600 tabular-nums block">{{ number_format($metrics->elderly_or_child_not_accompanied, 0, '', '.') }}</span>
                    </div>
                </div>

                <!-- Coluna 3: BPC ou PBF -->
                <div class="p-5 space-y-3">
                    <div>
                        <span class="text-xs text-slate-600 font-medium block">BPC ou PBF</span>
                        <span class="text-2xl font-black text-ink tabular-nums">{{ number_format($metrics->bpc_or_pbf_total, 0, '', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-500 block">BPC ou PBF Acompanhados</span>
                        <span class="text-lg font-bold text-emerald-600 tabular-nums block">{{ number_format($metrics->bpc_or_pbf_accompanied, 0, '', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-500 block">BPC ou PBF Não acompanhados</span>
                        <span class="text-lg font-bold text-rose-600 tabular-nums block">{{ number_format($metrics->bpc_or_pbf_not_accompanied, 0, '', '.') }}</span>
                    </div>
                </div>

                <!-- Coluna 4: Idoso ou Criança + BPC ou PBF -->
                <div class="p-5 space-y-3">
                    <div>
                        <span class="text-xs text-slate-600 font-medium block">Idoso ou Criança + BPC ou PBF</span>
                        <span class="text-2xl font-black text-ink tabular-nums">{{ number_format($metrics->elderly_child_and_benefit_total, 0, '', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-500 block">ID/CR + BPC/PBF Acompanhados</span>
                        <span class="text-lg font-bold text-emerald-600 tabular-nums block">{{ number_format($metrics->elderly_child_and_benefit_accompanied, 0, '', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-500 block">ID/CR + BPC/PBF Não acompanhados</span>
                        <span class="text-lg font-bold text-rose-600 tabular-nums block">{{ number_format($metrics->elderly_child_and_benefit_not_accompanied, 0, '', '.') }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- SEÇÃO DE FILTROS RÁPIDOS (EXATAMENTE COMO NAS IMAGENS 2 E 3) -->
    <div class="bg-white rounded-3xl border border-line shadow-panel p-5 space-y-4">
        <!-- Linha 1 de Inputs -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
            <div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterCns"
                    placeholder="CNS"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs text-ink placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                />
            </div>
            <div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterCpf"
                    placeholder="CPF"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs text-ink placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                />
            </div>
            <div class="col-span-2">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterName"
                    placeholder="Filtrar por Nome"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs text-ink placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                />
            </div>
            <div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterProfCns"
                    placeholder="CNS do Profissional"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs text-ink placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                />
            </div>
            <div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterProfName"
                    placeholder="Filtrar por Nome do Profissi"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs text-ink placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                />
            </div>
            <div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterCnes"
                    placeholder="CNES"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs text-ink placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                />
            </div>
            <div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="filterIne"
                    placeholder="INE"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs text-ink placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                />
            </div>
        </div>

        <!-- Linha 2: Dropdown Raça/Cor + Seletor de Paginação -->
        <div class="flex items-center justify-between gap-4 pt-1">
            <div class="flex items-center gap-3">
                <!-- Dropdown Raça/Cor -->
                <div class="w-40">
                    <select
                        wire:model.live="filterRaceColor"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-1.5 text-xs text-slate-700 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                    >
                        @foreach ($races as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Seletor Paginação -->
                <div class="w-20">
                    <select
                        wire:model.live="perPage"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-1.5 text-xs text-slate-700 font-bold focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                    >
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                @if ($filterCns || $filterCpf || $filterName || $filterProfCns || $filterProfName || $filterCnes || $filterIne || $filterRaceColor !== 'ALL' || $advMicroarea || $advMiciUpdated !== '' || $advMicdtUpdated !== '' || $advHasMicdt !== '' || $advVulnerability !== 'ALL' || $advSocialBenefit !== 'ALL' || $advAccompanied !== '')
                    <button
                        type="button"
                        wire:click="clearAllFilters"
                        class="text-xs text-rose-600 hover:text-rose-800 font-semibold transition cursor-pointer flex items-center gap-1"
                    >
                        <span>Limpar filtros</span>
                        <span>✕</span>
                    </button>
                @endif
            </div>

            <div class="text-xs text-slate-500 font-medium">
                Mostrando <strong class="text-slate-800">{{ $citizens->firstItem() ?? 0 }}</strong> a <strong class="text-slate-800">{{ $citizens->lastItem() ?? 0 }}</strong> de <strong class="text-teal-800">{{ number_format($totalRecordsCount, 0, '', '.') }}</strong> registros
            </div>
        </div>
    </div>

    <!-- TABELA NOMINAL COMPLETA (EXATA CONFORME IMAGENS 2, 3 E 4) -->
    <div class="bg-white rounded-3xl border border-line shadow-panel overflow-hidden" x-data="{ revealed: {} }">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[10px] border-b border-line">
                        <th class="py-3.5 px-3 text-center w-10">#</th>
                        <th class="py-3.5 px-3">CNS</th>
                        <th class="py-3.5 px-3">CPF</th>
                        <th class="py-3.5 px-3 text-center">CPF/CNS RESPONSÁVEL</th>
                        <th class="py-3.5 px-3 cursor-pointer hover:text-teal-700" wire:click="sortByField('birth_date')">
                            <span class="inline-flex items-center gap-1">
                                NASCIMENTO
                                @if ($sortBy === 'birth_date')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </span>
                        </th>
                        <th class="py-3.5 px-4 cursor-pointer hover:text-teal-700" wire:click="sortByField('name')">
                            <span class="inline-flex items-center gap-1">
                                NOME
                                @if ($sortBy === 'name')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </span>
                        </th>
                        <th class="py-3.5 px-3 text-center cursor-pointer hover:text-teal-700" wire:click="sortByField('age')">
                            <span class="inline-flex items-center gap-1">
                                IDADE
                                @if ($sortBy === 'age')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </span>
                        </th>
                        <th class="py-3.5 px-3 text-center">R/C</th>
                        <th class="py-3.5 px-3 text-center cursor-pointer hover:text-teal-700" wire:click="sortByField('cnes')">
                            <span class="inline-flex items-center gap-1">
                                UNIDADE
                                @if ($sortBy === 'cnes')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </span>
                        </th>
                        <th class="py-3.5 px-3 text-center cursor-pointer hover:text-teal-700" wire:click="sortByField('ine')">
                            <span class="inline-flex items-center gap-1">
                                EQUIPE
                                @if ($sortBy === 'ine')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </span>
                        </th>
                        <th class="py-3.5 px-3 text-center">PROFISSIONAL</th>
                        <th class="py-3.5 px-3 text-center cursor-pointer hover:text-teal-700" wire:click="sortByField('microarea')">
                            <span class="inline-flex items-center gap-1">
                                MICRO ÁREA
                                @if ($sortBy === 'microarea')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </span>
                        </th>
                        <th class="py-3.5 px-3 text-center cursor-pointer hover:text-teal-700" wire:click="sortByField('mici_date')">
                            <span class="inline-flex items-center gap-1">
                                MICI (ATUALIZAÇÃO)
                                @if ($sortBy === 'mici_date')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </span>
                        </th>
                        <th class="py-3.5 px-3 text-center cursor-pointer hover:text-teal-700" wire:click="sortByField('micdt_date')">
                            <span class="inline-flex items-center gap-1">
                                MICDT (ATUALIZAÇÃO)
                                @if ($sortBy === 'micdt_date')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </span>
                        </th>
                        <th class="py-3.5 px-3 text-center">VULNERABILIDADE (IDADE)</th>
                        <th class="py-3.5 px-3 text-center">BPC/PBF</th>
                        <th class="py-3.5 px-3 text-center">ACOMPANHADA</th>
                        <th class="py-3.5 px-4 text-center">AÇÕES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($citizens as $c)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- ID / PEC ID -->
                            <td class="py-3 px-3 text-center font-mono text-[11px] text-slate-500">
                                {{ $c->cidadao_pec_id }}
                            </td>

                            <!-- CNS com Mascaramento LGPD -->
                            <td class="py-3 px-3 font-mono text-[11px] text-slate-700">
                                <div class="flex items-center gap-1.5">
                                    <span x-text="revealed['cns_{{ $c->id }}'] ? '{{ $c->cns }}' : '{{ $c->masked_cns }}'"></span>
                                    <button
                                        type="button"
                                        @click="revealed['cns_{{ $c->id }}'] = !revealed['cns_{{ $c->id }}']"
                                        class="text-slate-400 hover:text-teal-600 transition"
                                        title="Revelar/Ocultar CNS"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click="navigator.clipboard.writeText('{{ $c->cns }}')"
                                        class="text-slate-400 hover:text-blue-600 transition"
                                        title="Copiar CNS"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9 9 9 0 00-9 9v1.125" />
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            <!-- CPF com Mascaramento LGPD -->
                            <td class="py-3 px-3 font-mono text-[11px] text-slate-700">
                                <div class="flex items-center gap-1.5">
                                    <span x-text="revealed['cpf_{{ $c->id }}'] ? '{{ $c->cpf }}' : '{{ $c->masked_cpf }}'"></span>
                                    <button
                                        type="button"
                                        @click="revealed['cpf_{{ $c->id }}'] = !revealed['cpf_{{ $c->id }}']"
                                        class="text-slate-400 hover:text-teal-600 transition"
                                        title="Revelar/Ocultar CPF"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click="navigator.clipboard.writeText('{{ $c->cpf }}')"
                                        class="text-slate-400 hover:text-blue-600 transition"
                                        title="Copiar CPF"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9 9 9 0 00-9 9v1.125" />
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            <!-- CPF/CNS RESPONSÁVEL -->
                            <td class="py-3 px-3 text-center font-mono text-[11px] text-slate-500">
                                {{ $c->responsible_cns_cpf ?? '---' }}
                            </td>

                            <!-- NASCIMENTO -->
                            <td class="py-3 px-3 font-mono text-[11px] text-slate-700">
                                {{ $c->birth_date ? $c->birth_date->format('d/m/Y') : '---' }}
                            </td>

                            <!-- NOME -->
                            <td class="py-3 px-4 font-bold text-ink flex items-center gap-1.5">
                                <span>{{ $c->name }}</span>
                                <button type="button" wire:click="openDetails({{ $c->id }})" class="text-slate-400 hover:text-teal-600 transition" title="Ver Prontuário">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                    </svg>
                                </button>
                            </td>

                            <!-- IDADE -->
                            <td class="py-3 px-3 text-center font-bold text-slate-700 tabular-nums">
                                {{ $c->age }}
                            </td>

                            <!-- R/C -->
                            <td class="py-3 px-3 text-center text-slate-600">
                                {{ $c->race_color }}
                            </td>

                            <!-- UNIDADE (CNES) -->
                            <td class="py-3 px-3 text-center font-mono text-slate-700">
                                <span title="{{ $c->facility_name }}">{{ $c->cnes }} ℹ️</span>
                            </td>

                            <!-- EQUIPE (INE) -->
                            <td class="py-3 px-3 text-center font-mono text-slate-700">
                                <span title="{{ $c->team_name }}">{{ $c->ine }} ℹ️</span>
                            </td>

                            <!-- PROFISSIONAL ACS -->
                            <td class="py-3 px-3 text-center font-mono text-[11px] text-slate-700">
                                <span title="{{ $c->professional_name }}">{{ $c->formatted_professional_cns }} ℹ️</span>
                            </td>

                            <!-- MICRO ÁREA -->
                            <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">
                                {{ $c->microarea }}
                            </td>

                            <!-- MICI (ATUALIZAÇÃO) -->
                            <td class="py-3 px-3 text-center">
                                @if ($c->mici_updated && $c->mici_date)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white shadow-2xs">
                                        {{ $c->mici_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white shadow-2xs">
                                        SEM MICI
                                    </span>
                                @endif
                            </td>

                            <!-- MICDT (ATUALIZAÇÃO) -->
                            <td class="py-3 px-3 text-center">
                                @if ($c->has_micdt && $c->micdt_date)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white shadow-2xs">
                                        {{ $c->micdt_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white shadow-2xs">
                                        SEM MICDT
                                    </span>
                                @endif
                            </td>

                            <!-- VULNERABILIDADE (IDADE) -->
                            <td class="py-3 px-3 text-center">
                                @if ($c->vulnerability_type === 'idoso')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-600 text-white shadow-2xs">
                                        Idoso
                                    </span>
                                @elseif ($c->vulnerability_type === 'crianca')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white shadow-2xs">
                                        Criança
                                    </span>
                                @else
                                    <span class="text-slate-500 text-[11px]">Sem Critério</span>
                                @endif
                            </td>

                            <!-- BPC/PBF -->
                            <td class="py-3 px-3 text-center font-bold text-slate-700 text-[11px]">
                                @if ($c->social_benefit === 'bpc')
                                    <span class="text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">BPC</span>
                                @elseif ($c->social_benefit === 'pbf')
                                    <span class="text-amber-800 bg-amber-50 px-2 py-0.5 rounded">PBF</span>
                                @elseif ($c->social_benefit === 'bpc_pbf')
                                    <span class="text-purple-800 bg-purple-50 px-2 py-0.5 rounded">BPC+PBF</span>
                                @else
                                    <span class="text-slate-400">---</span>
                                @endif
                            </td>

                            <!-- ACOMPANHADA -->
                            <td class="py-3 px-3 text-center">
                                @if ($c->is_accompanied)
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white shadow-2xs">
                                        Sim
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white shadow-2xs">
                                        Não
                                    </span>
                                @endif
                            </td>

                            <!-- AÇÕES: DETALHES -->
                            <td class="py-3 px-4 text-center">
                                <button
                                    type="button"
                                    wire:click="openDetails({{ $c->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-[11px] font-bold shadow-xs transition cursor-pointer"
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
                            <td colspan="18" class="py-12 text-center text-muted">
                                Nenhum cidadão encontrado com os filtros selecionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="p-4 border-t border-line bg-slate-50/50">
            {{ $citizens->links() }}
        </div>
    </div>

    <!-- MODAL DE BUSCA AVANÇADA -->
    @if ($advancedModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl border border-line shadow-2xl max-w-2xl w-full p-6 space-y-5 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-line pb-3">
                    <h3 class="text-base font-bold text-ink flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Busca Avançada · Vínculo e Território</span>
                    </h3>
                    <button type="button" wire:click="closeAdvancedModal" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 transition">✕</button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Microárea -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Microárea</label>
                        <input type="text" wire:model="advMicroarea" placeholder="Ex: 01, 02..." class="w-full rounded-xl border border-slate-200 p-2 text-xs" />
                    </div>

                    <!-- Status MICI -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status MICI</label>
                        <select wire:model="advMiciUpdated" class="w-full rounded-xl border border-slate-200 p-2 text-xs">
                            <option value="">Todos os status</option>
                            <option value="1">MICI Atualizado (últimos 24 meses)</option>
                            <option value="0">MICI Desatualizado / Sem MICI</option>
                        </select>
                    </div>

                    <!-- Status MICDT -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status MICDT</label>
                        <select wire:model="advMicdtUpdated" class="w-full rounded-xl border border-slate-200 p-2 text-xs">
                            <option value="">Todos os status</option>
                            <option value="1">MICDT Atualizado (últimos 24 meses)</option>
                            <option value="0">MICDT Desatualizado</option>
                        </select>
                    </div>

                    <!-- Presença de Domicílio -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Possui MICDT (Domicílio)</label>
                        <select wire:model="advHasMicdt" class="w-full rounded-xl border border-slate-200 p-2 text-xs">
                            <option value="">Todos</option>
                            <option value="1">Com MICDT</option>
                            <option value="0">Sem MICDT</option>
                        </select>
                    </div>

                    <!-- Vulnerabilidade por Idade -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Vulnerabilidade (Faixa Etária)</label>
                        <select wire:model="advVulnerability" class="w-full rounded-xl border border-slate-200 p-2 text-xs">
                            <option value="ALL">Todas as faixas</option>
                            <option value="idoso">Idoso (60+ anos)</option>
                            <option value="crianca">Criança (0 a 11 anos)</option>
                            <option value="sem_criterio">Sem Critério de Idade</option>
                        </select>
                    </div>

                    <!-- Benefício Social -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Benefício Social</label>
                        <select wire:model="advSocialBenefit" class="w-full rounded-xl border border-slate-200 p-2 text-xs">
                            <option value="ALL">Todos os benefícios</option>
                            <option value="bpc">BPC (Benefício de Prestação Continuada)</option>
                            <option value="pbf">PBF (Programa Bolsa Família)</option>
                            <option value="bpc_pbf">BPC + PBF</option>
                            <option value="nenhum">Sem Benefício Registrado</option>
                        </select>
                    </div>

                    <!-- Status de Acompanhamento -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Acompanhamento no Território</label>
                        <select wire:model="advAccompanied" class="w-full rounded-xl border border-slate-200 p-2 text-xs">
                            <option value="">Todos</option>
                            <option value="1">Acompanhado(a)</option>
                            <option value="0">Não Acompanhado(a)</option>
                        </select>
                    </div>

                    <!-- Vinculação -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Vínculo com Equipe</label>
                        <select wire:model="advLinked" class="w-full rounded-xl border border-slate-200 p-2 text-xs">
                            <option value="">Todos</option>
                            <option value="1">Vinculado a Equipe Homologada</option>
                            <option value="0">Não Vinculado</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-line">
                    <button
                        type="button"
                        wire:click="clearAllFilters"
                        class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-xs font-semibold transition"
                    >
                        Limpar
                    </button>
                    <button
                        type="button"
                        wire:click="applyAdvancedFilters"
                        class="px-5 py-2 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold transition shadow-xs"
                    >
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE AUDITORIA E DETALHES DO CIDADÃO (👁️ DETALHES) -->
    @if ($detailsModalOpen && $selectedCitizen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl border border-line shadow-2xl max-w-2xl w-full p-6 space-y-5 animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between border-b border-line pb-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                            Prontuário de Vínculo Territorial
                        </span>
                        <h3 class="text-lg font-bold text-ink mt-1">{{ $selectedCitizen->name }}</h3>
                        <p class="text-xs text-muted">ID PEC: {{ $selectedCitizen->cidadao_pec_id }} · Nascimento: {{ $selectedCitizen->birth_date ? $selectedCitizen->birth_date->format('d/m/Y') : '---' }} ({{ $selectedCitizen->age }} anos)</p>
                    </div>
                    <button type="button" wire:click="closeDetails" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 transition">✕</button>
                </div>

                <!-- Dados de Identificação -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 text-[10px] block uppercase font-bold">CNS</span>
                        <span class="font-mono font-bold text-slate-800">{{ $selectedCitizen->cns ?? '---' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] block uppercase font-bold">CPF</span>
                        <span class="font-mono font-bold text-slate-800">{{ $selectedCitizen->cpf ?? '---' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] block uppercase font-bold">Raça/Cor</span>
                        <span class="font-semibold text-slate-800">{{ $selectedCitizen->race_color }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] block uppercase font-bold">Sexo</span>
                        <span class="font-semibold text-slate-800">{{ $selectedCitizen->gender === 'F' ? 'Feminino' : 'Masculino' }}</span>
                    </div>
                </div>

                <!-- Vínculo Territorial e Equipe -->
                <div class="p-4 rounded-2xl border border-slate-200 space-y-2 text-xs">
                    <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Vínculo Territorial e Equipe de APS</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <span class="text-slate-500 block">Equipe de Saúde da Família (INE)</span>
                            <span class="font-bold text-ink">{{ $selectedCitizen->team_name }} ({{ $selectedCitizen->ine }})</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Unidade Básica de Saúde (CNES)</span>
                            <span class="font-bold text-ink">{{ $selectedCitizen->facility_name }} ({{ $selectedCitizen->cnes }})</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Agente Comunitário de Saúde (ACS)</span>
                            <span class="font-bold text-ink">{{ $selectedCitizen->professional_name }}</span>
                            <span class="text-[11px] font-mono text-slate-500 block">CNS: {{ $selectedCitizen->formatted_professional_cns }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Microárea</span>
                            <span class="font-bold text-teal-800 text-sm">Microárea {{ $selectedCitizen->microarea }}</span>
                        </div>
                    </div>
                    @if ($selectedCitizen->address)
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-slate-500 block">Endereço Territorial</span>
                            <span class="text-slate-700 font-medium">{{ $selectedCitizen->address }}</span>
                        </div>
                    @endif
                </div>

                <!-- Auditoria das Dimensões Cadastro e Acompanhamento -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Dimensão Cadastro -->
                    <div class="p-4 rounded-2xl border border-teal-200 bg-teal-50/40 space-y-2">
                        <span class="font-bold text-teal-900 block text-xs uppercase">Auditoria Dimensão Cadastro</span>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Cadastro Individual (MICI):</span>
                            @if ($selectedCitizen->mici_updated && $selectedCitizen->mici_date)
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">Atualizado ({{ $selectedCitizen->mici_date->format('d/m/Y') }})</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">Sem MICI</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Cadastro Domiciliar (MICDT):</span>
                            @if ($selectedCitizen->has_micdt && $selectedCitizen->micdt_date)
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">Atualizado ({{ $selectedCitizen->micdt_date->format('d/m/Y') }})</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">Sem MICDT</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Vinculação à Equipe:</span>
                            <span class="font-bold {{ $selectedCitizen->is_linked ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $selectedCitizen->is_linked ? 'Vinculado(a)' : 'Não Vinculado(a)' }}
                            </span>
                        </div>
                    </div>

                    <!-- Dimensão Acompanhamento -->
                    <div class="p-4 rounded-2xl border border-blue-200 bg-blue-50/40 space-y-2">
                        <span class="font-bold text-blue-900 block text-xs uppercase">Auditoria Dimensão Acompanhamento</span>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Vulnerabilidade (Idade):</span>
                            <span class="font-bold uppercase text-slate-800">{{ $selectedCitizen->vulnerability_type }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Benefício Social:</span>
                            <span class="font-bold uppercase text-slate-800">{{ $selectedCitizen->social_benefit }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Status Acompanhamento:</span>
                            <span class="px-2 py-0.5 rounded font-bold {{ $selectedCitizen->is_accompanied ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $selectedCitizen->is_accompanied ? 'Acompanhado(a)' : 'Não Acompanhado(a)' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-3 border-t border-line">
                    <button
                        type="button"
                        wire:click="closeDetails"
                        class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition shadow-xs"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
