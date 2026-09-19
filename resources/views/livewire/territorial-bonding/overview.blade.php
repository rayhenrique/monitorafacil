<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Navegação em Abas do Módulo -->
    <x-territorial-bonding-tabs
        title="Vínculo e Acompanhamento Territorial"
        subtitle="Componente II · Avaliação Quadrimestral do Vínculo e Território na Atenção Primária"
        :activeTab="$activeTab"
    />

    <!-- Alerta Oficial sobre Q2/2026 -->
    <div class="rounded-2xl border border-amber-200/80 bg-amber-50/70 p-4 sm:p-5 flex items-start gap-3 shadow-xs">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </span>
        <div class="text-xs text-amber-900 leading-relaxed">
            <p class="font-bold text-sm text-amber-950 mb-0.5">Nota sobre os Quadrimestres e Publicação Oficial do Ministério da Saúde</p>
            <p>
                Os resultados aqui apresentados correspondem às publicações oficiais preliminares do <strong>Siaps (Sistema de Informação para a Atenção Primária à Saúde)</strong> para os 3 últimos quadrimestres homologados: <strong>Q2/2025</strong>, <strong>Q3/2025</strong> e <strong>Q1/2026</strong>.
                O resultado do <strong>2º Quadrimestre de 2026 (Q2/2026)</strong> ainda não foi divulgado pelo Ministério da Saúde.
            </p>
        </div>
    </div>

    <!-- Seletor de Quadrimestres Oficiais -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-line shadow-sm">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Quadrimestre Avaliado:</span>
            <div class="flex flex-wrap items-center gap-1.5">
                @foreach ($availableQuarters as $q)
                    @php
                        $isSelected = ($selectedYear === $q['year'] && $selectedQuarter === $q['quarter']);
                    @endphp
                    <button
                        type="button"
                        wire:click="selectPeriod({{ $q['year'] }}, {{ $q['quarter'] }})"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $isSelected ? 'bg-teal-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        <span>{{ $q['label'] }}</span>
                        @if ($q['is_latest'])
                            <span class="h-1.5 w-1.5 rounded-full {{ $isSelected ? 'bg-emerald-300' : 'bg-emerald-500' }}"></span>
                        @endif
                    </button>
                @endforeach

                <!-- Botão informativo para Q2/2026 -->
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-50 text-slate-400 border border-dashed border-slate-200 cursor-not-allowed" title="Aguardando publicação oficial do Ministério da Saúde">
                    <span>Q2/26 (Em apuração)</span>
                    <span class="text-[10px] font-mono uppercase text-amber-600 font-bold">Pendente</span>
                </span>
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="text-xs text-muted flex items-center gap-1.5">
                <span>Período Ativo:</span>
                <span class="font-bold text-teal-900 bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-200">{{ $selectedQuarterLabel }}</span>
            </div>

            <!-- Botão Importação Opcional de CSV no próprio módulo -->
            <button
                type="button"
                wire:click="openImportModal"
                class="inline-flex items-center gap-1.5 rounded-xl border border-teal-300 bg-teal-50/80 hover:bg-teal-100 px-3 py-1.5 text-xs font-bold text-teal-900 transition shadow-xs cursor-pointer"
                title="Importar arquivos CSV do Siaps diretamente no módulo"
            >
                <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span>Importar CSV Siaps (Opcional)</span>
            </button>
        </div>
    </div>

    <!-- CARDS DE SÍNTESE MUNICIPAL -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Nota Média Final do Componente II -->
        <div class="bg-white p-5 rounded-2xl border border-line shadow-panel flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted uppercase tracking-wider">Nota Média Componente II</span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-extrabold uppercase {{ $summary['municipal_classification'] === 'ÓTIMO' ? 'bg-blue-100 text-blue-800' : ($summary['municipal_classification'] === 'BOM' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                    {{ $summary['municipal_classification'] }}
                </span>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-ink tabular-nums">{{ number_format($summary['average_final_score'], 2, ',', '.') }}</span>
                    <span class="text-xs text-muted font-medium">/ 10,00 pts</span>
                </div>
                <p class="text-xs text-muted mt-1">Conforme Quadro 5 da NT 08/2026 (&gt; 8,5: Ótimo)</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Incentivo Financeiro:</span>
                <span class="font-bold text-teal-800">100% Repasse</span>
            </div>
        </div>

        <!-- Card 2: Dimensão Cadastro (Peso 3) -->
        <div class="bg-white p-5 rounded-2xl border border-line shadow-panel flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted uppercase tracking-wider">Dimensão Cadastro</span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold bg-slate-100 text-slate-700">Peso 3</span>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-teal-900 tabular-nums">{{ number_format($summary['average_registration'], 2, ',', '.') }}</span>
                    <span class="text-xs text-muted font-medium">/ 3,00 pts</span>
                </div>
                <p class="text-xs text-muted mt-1">Cadastros individuais (MICI) atualizados em 24 meses</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Aproveitamento Médio:</span>
                <span class="font-bold text-emerald-700">{{ $summary['average_registration'] > 0 ? round(($summary['average_registration'] / 3) * 100, 1) : 0 }}%</span>
            </div>
        </div>

        <!-- Card 3: Dimensão Acompanhamento (Peso 7) -->
        <div class="bg-white p-5 rounded-2xl border border-line shadow-panel flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted uppercase tracking-wider">Dimensão Acompanhamento</span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold bg-slate-100 text-slate-700">Peso 7</span>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-blue-900 tabular-nums">{{ number_format($summary['average_monitoring'], 2, ',', '.') }}</span>
                    <span class="text-xs text-muted font-medium">/ 7,00 pts</span>
                </div>
                <p class="text-xs text-muted mt-1">Visitas domiciliares e acompanhamento contínuo ACS</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Aproveitamento Médio:</span>
                <span class="font-bold text-blue-700">{{ $summary['average_monitoring'] > 0 ? round(($summary['average_monitoring'] / 7) * 100, 1) : 0 }}%</span>
            </div>
        </div>

        <!-- Card 4: Distribuição das 19 Equipes eSF -->
        <div class="bg-white p-5 rounded-2xl border border-line shadow-panel flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted uppercase tracking-wider">Equipes por Conceito</span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold bg-teal-50 text-teal-800">19 eSF</span>
            </div>
            <div class="mt-2 grid grid-cols-4 gap-1 text-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                <div>
                    <span class="block text-lg font-black text-blue-700 tabular-nums">{{ $summary['optimal_count'] }}</span>
                    <span class="text-[9px] font-bold text-slate-500 uppercase">Ótimo</span>
                </div>
                <div class="border-l border-slate-200">
                    <span class="block text-lg font-black text-emerald-700 tabular-nums">{{ $summary['good_count'] }}</span>
                    <span class="text-[9px] font-bold text-slate-500 uppercase">Bom</span>
                </div>
                <div class="border-l border-slate-200">
                    <span class="block text-lg font-black text-amber-700 tabular-nums">{{ $summary['sufficient_count'] }}</span>
                    <span class="text-[9px] font-bold text-slate-500 uppercase">Sufic.</span>
                </div>
                <div class="border-l border-slate-200">
                    <span class="block text-lg font-black text-orange-700 tabular-nums">{{ $summary['regular_count'] }}</span>
                    <span class="text-[9px] font-bold text-slate-500 uppercase">Regul.</span>
                </div>
            </div>
            <div class="mt-3 pt-2 text-center text-[11px] text-muted">
                {{ $summary['optimal_count'] + $summary['good_count'] }} de 19 equipes nas faixas mais altas ({{ round((($summary['optimal_count'] + $summary['good_count']) / 19) * 100) }}%)
            </div>
        </div>
    </div>

    <!-- SEÇÃO: GRÁFICOS DO SIAPS (REPRODUÇÃO FIEL DA IMAGEM OFICIAL) -->
    @if ($activeTab === 'overview' || $activeTab === 'cadastro' || $activeTab === 'acompanhamento')
        <div class="space-y-4">
            <div class="text-center sm:text-left">
                <h2 class="text-lg font-bold text-ink">Vínculo e Acompanhamento Territorial</h2>
                <p class="text-xs text-muted">Distribuição das equipes por Classificação da Dimensão (Série Histórica Siaps)</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- GRÁFICO 1: DIMENSÃO CADASTRO (eSF) -->
                @if ($activeTab === 'overview' || $activeTab === 'cadastro')
                    <div class="bg-white p-6 rounded-3xl border border-line shadow-panel flex flex-col justify-between">
                        <div>
                            <!-- Título do Gráfico -->
                            <div class="flex items-start justify-between gap-3 mb-6">
                                <div class="flex items-center gap-2.5">
                                    <span class="p-1.5 bg-blue-50 text-blue-700 rounded-lg">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-bold text-ink flex items-center gap-1.5">
                                            CVAT - Dimensão Cadastro - eSF
                                        </h3>
                                        <p class="text-xs text-muted">Q2/25, Q3/25, Q1/26</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-600 rounded">19 equipes</span>
                            </div>

                            <!-- Área das Barras Empilhadas -->
                            <div class="space-y-4">
                                @foreach ($chartsData['cadastro'] as $row)
                                    @php
                                        $total = $row['total'] ?: 19;
                                        $pReg = ($row['regular'] / $total) * 100;
                                        $pSuf = ($row['sufficient'] / $total) * 100;
                                        $pGood = ($row['good'] / $total) * 100;
                                        $pOpt = ($row['optimal'] / $total) * 100;
                                    @endphp
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 shrink-0 text-xs font-semibold text-slate-600 text-right">
                                            {{ $row['period'] }}
                                        </div>
                                        <div class="flex-1 h-9 flex bg-slate-100 rounded-sm overflow-hidden shadow-2xs">
                                            <!-- Regular (Laranja Queimado #b85d19) -->
                                            @if ($row['regular'] > 0)
                                                <div style="width: {{ $pReg }}%;" class="bg-[#b85d19] h-full flex items-center justify-center text-white text-[11px] font-bold border-r border-white/20" title="Regular: {{ $row['regular'] }}">
                                                    {{ $row['regular'] }}
                                                </div>
                                            @endif

                                            <!-- Suficiente (Mostarda #c89211) -->
                                            @if ($row['sufficient'] > 0)
                                                <div style="width: {{ $pSuf }}%;" class="bg-[#c89211] h-full flex items-center justify-center text-white text-[11px] font-bold border-r border-white/20" title="Suficiente: {{ $row['sufficient'] }}">
                                                    {{ $row['sufficient'] }}
                                                </div>
                                            @elseif ($row['period'] === 'Q1/26')
                                                <!-- Espaço para demonstrar o zero conforme imagem original -->
                                                <div style="width: 2px;" class="bg-[#c89211] h-full" title="Suficiente: 0"></div>
                                                <span class="text-[10px] text-slate-400 font-bold px-0.5 self-center">0</span>
                                            @endif

                                            <!-- Bom (Verde #0d9488) -->
                                            @if ($row['good'] > 0)
                                                <div style="width: {{ $pGood }}%;" class="bg-[#059669] h-full flex items-center justify-center text-white text-[11px] font-bold border-r border-white/20" title="Bom: {{ $row['good'] }}">
                                                    {{ $row['good'] }}
                                                </div>
                                            @endif

                                            <!-- Ótimo (Azul #2563eb) -->
                                            @if ($row['optimal'] > 0)
                                                <div style="width: {{ $pOpt }}%;" class="bg-[#2563eb] h-full flex items-center justify-center text-white text-[11px] font-bold" title="Ótimo: {{ $row['optimal'] }}">
                                                    {{ $row['optimal'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Eixo X (0, 3, 6, 9, 12, 15, 18 19) -->
                            <div class="mt-3 ml-15 flex justify-between text-[11px] text-slate-400 font-mono border-t border-slate-200 pt-1.5">
                                <span>0</span>
                                <span>3</span>
                                <span>6</span>
                                <span>9</span>
                                <span>12</span>
                                <span>15</span>
                                <span>18</span>
                                <span class="font-bold text-slate-600">19</span>
                            </div>
                        </div>

                        <!-- Legenda Oficial -->
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3.5 w-3.5 rounded-xs bg-[#b85d19]"></span>
                                <span class="text-slate-700 font-medium">Regular</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3.5 w-3.5 rounded-xs bg-[#c89211]"></span>
                                <span class="text-slate-700 font-medium">Suficiente</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3.5 w-3.5 rounded-xs bg-[#059669]"></span>
                                <span class="text-slate-700 font-medium">Bom</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3.5 w-3.5 rounded-xs bg-[#2563eb]"></span>
                                <span class="text-slate-700 font-medium">Ótimo</span>
                            </span>
                        </div>
                    </div>
                @endif

                <!-- GRÁFICO 2: DIMENSÃO ACOMPANHAMENTO (eSF) -->
                @if ($activeTab === 'overview' || $activeTab === 'acompanhamento')
                    <div class="bg-white p-6 rounded-3xl border border-line shadow-panel flex flex-col justify-between">
                        <div>
                            <!-- Título do Gráfico -->
                            <div class="flex items-start justify-between gap-3 mb-6">
                                <div class="flex items-center gap-2.5">
                                    <span class="p-1.5 bg-teal-50 text-teal-700 rounded-lg">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-bold text-ink flex items-center gap-1.5">
                                            CVAT - Dimensão Acompanhamento - eSF
                                        </h3>
                                        <p class="text-xs text-muted">Q2/25, Q3/25, Q1/26</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-600 rounded">19 equipes</span>
                            </div>

                            <!-- Área das Barras Empilhadas -->
                            <div class="space-y-4">
                                @foreach ($chartsData['acompanhamento'] as $row)
                                    @php
                                        $total = $row['total'] ?: 19;
                                        $pReg = ($row['regular'] / $total) * 100;
                                        $pSuf = ($row['sufficient'] / $total) * 100;
                                        $pGood = ($row['good'] / $total) * 100;
                                        $pOpt = ($row['optimal'] / $total) * 100;
                                    @endphp
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 shrink-0 text-xs font-semibold text-slate-600 text-right">
                                            {{ $row['period'] }}
                                        </div>
                                        <div class="flex-1 h-9 flex bg-slate-100 rounded-sm overflow-hidden shadow-2xs">
                                            <!-- Regular (Laranja Queimado #b85d19) -->
                                            @if ($row['regular'] > 0)
                                                <div style="width: {{ $pReg }}%;" class="bg-[#b85d19] h-full flex items-center justify-center text-white text-[11px] font-bold border-r border-white/20" title="Regular: {{ $row['regular'] }}">
                                                    {{ $row['regular'] }}
                                                </div>
                                            @endif

                                            <!-- Suficiente (Mostarda #c89211) -->
                                            @if ($row['sufficient'] > 0)
                                                <div style="width: {{ $pSuf }}%;" class="bg-[#c89211] h-full flex items-center justify-center text-white text-[11px] font-bold border-r border-white/20" title="Suficiente: {{ $row['sufficient'] }}">
                                                    {{ $row['sufficient'] }}
                                                </div>
                                            @endif

                                            <!-- Bom (Verde #0d9488) -->
                                            @if ($row['good'] > 0)
                                                <div style="width: {{ $pGood }}%;" class="bg-[#059669] h-full flex items-center justify-center text-white text-[11px] font-bold border-r border-white/20" title="Bom: {{ $row['good'] }}">
                                                    {{ $row['good'] }}
                                                </div>
                                            @endif

                                            <!-- Ótimo (Azul #2563eb) -->
                                            @if ($row['optimal'] > 0)
                                                <div style="width: {{ $pOpt }}%;" class="bg-[#2563eb] h-full flex items-center justify-center text-white text-[11px] font-bold" title="Ótimo: {{ $row['optimal'] }}">
                                                    {{ $row['optimal'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Eixo X (0, 3, 6, 9, 12, 15, 18, 19) -->
                            <div class="mt-3 ml-15 flex justify-between text-[11px] text-slate-400 font-mono border-t border-slate-200 pt-1.5">
                                <span>0</span>
                                <span>3</span>
                                <span>6</span>
                                <span>9</span>
                                <span>12</span>
                                <span>15</span>
                                <span>18</span>
                                <span class="font-bold text-slate-600">19</span>
                            </div>
                        </div>

                        <!-- Legenda Oficial -->
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3.5 w-3.5 rounded-xs bg-[#b85d19]"></span>
                                <span class="text-slate-700 font-medium">Regular</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3.5 w-3.5 rounded-xs bg-[#c89211]"></span>
                                <span class="text-slate-700 font-medium">Suficiente</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3.5 w-3.5 rounded-xs bg-[#059669]"></span>
                                <span class="text-slate-700 font-medium">Bom</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3.5 w-3.5 rounded-xs bg-[#2563eb]"></span>
                                <span class="text-slate-700 font-medium">Ótimo</span>
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- SEÇÃO: TABELA DAS 19 EQUIPES DE SAÚDE DA FAMÍLIA -->
    @if ($activeTab === 'overview' || $activeTab === 'teams')
        <div class="bg-white rounded-3xl border border-line shadow-panel overflow-hidden">
            <!-- Cabeçalho da Tabela e Filtros -->
            <div class="p-5 border-b border-line flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-ink flex items-center gap-2">
                        <span>Desempenho Quadrimestral das Equipes (eSF)</span>
                        <span class="rounded-lg bg-teal-50 px-2 py-0.5 text-xs font-extrabold text-teal-900 border border-teal-200">
                            {{ $selectedQuarterLabel }}
                        </span>
                    </h3>
                    <p class="text-xs text-muted mt-0.5">Detalhamento nominal das 19 equipes homologadas de Teotônio Vilela/AL</p>
                </div>

                <!-- Filtros e Busca Rápida -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Campo de Busca -->
                    <div class="relative min-w-[220px]">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por equipe, UBS ou INE..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-8 pr-3 py-1.5 text-xs text-ink placeholder:text-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                        />
                        <svg class="h-4 w-4 text-slate-400 absolute left-2.5 top-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>

                    <!-- Filtro por Classificação -->
                    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold">
                        <button
                            type="button"
                            wire:click="filterByClassification('ALL')"
                            class="px-2.5 py-1 rounded-lg transition {{ $classificationFilter === 'ALL' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-ink' }}"
                        >
                            Todas ({{ $summary['total_teams'] }})
                        </button>
                        <button
                            type="button"
                            wire:click="filterByClassification('ÓTIMO')"
                            class="px-2.5 py-1 rounded-lg transition {{ $classificationFilter === 'ÓTIMO' ? 'bg-blue-600 text-white shadow-xs' : 'text-blue-700 hover:bg-white/50' }}"
                        >
                            Ótimo ({{ $summary['optimal_count'] }})
                        </button>
                        <button
                            type="button"
                            wire:click="filterByClassification('BOM')"
                            class="px-2.5 py-1 rounded-lg transition {{ $classificationFilter === 'BOM' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 hover:bg-white/50' }}"
                        >
                            Bom ({{ $summary['good_count'] }})
                        </button>
                        <button
                            type="button"
                            wire:click="filterByClassification('SUFICIENTE')"
                            class="px-2.5 py-1 rounded-lg transition {{ $classificationFilter === 'SUFICIENTE' ? 'bg-amber-600 text-white shadow-xs' : 'text-amber-700 hover:bg-white/50' }}"
                        >
                            Sufic. ({{ $summary['sufficient_count'] }})
                        </button>
                        <button
                            type="button"
                            wire:click="filterByClassification('REGULAR')"
                            class="px-2.5 py-1 rounded-lg transition {{ $classificationFilter === 'REGULAR' ? 'bg-orange-700 text-white shadow-xs' : 'text-orange-800 hover:bg-white/50' }}"
                        >
                            Regul. ({{ $summary['regular_count'] }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabela de Dados -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-600 font-bold border-b border-line uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Equipe / Unidade de Saúde</th>
                            <th class="py-3 px-3 text-center">INE</th>
                            <th class="py-3 px-3 text-center">CNES</th>
                            <th class="py-3 px-3 text-center">Tipo</th>
                            <th class="py-3 px-3 text-right cursor-pointer hover:text-teal-700" wire:click="sortByField('registration_score')">
                                <span class="inline-flex items-center gap-1">
                                    Dim. Cadastro (Peso 3)
                                    @if ($sortBy === 'registration_score')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </span>
                            </th>
                            <th class="py-3 px-3 text-right cursor-pointer hover:text-teal-700" wire:click="sortByField('monitoring_score')">
                                <span class="inline-flex items-center gap-1">
                                    Dim. Acompanhamento (Peso 7)
                                    @if ($sortBy === 'monitoring_score')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </span>
                            </th>
                            <th class="py-3 px-3 text-right cursor-pointer hover:text-teal-700" wire:click="sortByField('final_score')">
                                <span class="inline-flex items-center gap-1">
                                    Nota Final (10 pts)
                                    @if ($sortBy === 'final_score')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </span>
                            </th>
                            <th class="py-3 px-4 text-center">Classificação Final</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($teams as $team)
                            @php
                                $clsUpper = mb_strtoupper($team->final_classification);
                                $badgeClass = match ($clsUpper) {
                                    'ÓTIMO', 'OTIMO' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'BOM' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'SUFICIENTE' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    default => 'bg-orange-100 text-orange-900 border-orange-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-ink">{{ $team->team_name }}</div>
                                    <div class="text-[11px] text-muted">{{ $team->facility_name }}</div>
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-medium text-slate-600">
                                    {{ $team->ine }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono text-slate-500">
                                    {{ $team->cnes }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-bold text-slate-700">
                                        {{ $team->team_type }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-700 tabular-nums">
                                    {{ number_format($team->registration_score, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-700 tabular-nums">
                                    {{ number_format($team->monitoring_score, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-extrabold text-ink tabular-nums text-sm">
                                    {{ number_format($team->final_score, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-black uppercase border {{ $badgeClass }}">
                                        {{ $team->final_classification }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-muted">
                                    @if ($selectedQuarter !== 1 || $selectedYear !== 2026)
                                        <p class="font-medium">O detalhamento individual por equipe para este quadrimestre não consta no arquivo de importação.</p>
                                        <p class="text-xs mt-1">Consulte os gráficos superiores para ver a distribuição agregada oficial das 19 equipes.</p>
                                    @else
                                        Nenhuma equipe encontrada com os filtros selecionados.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($teams->isNotEmpty())
                        <tfoot>
                            <tr class="bg-slate-100/80 font-bold text-slate-800 border-t-2 border-slate-200">
                                <td class="py-3 px-4" colspan="4">
                                    Média Municipal Consolidada ({{ $teams->count() }} equipes)
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-teal-900">
                                    {{ number_format($summary['average_registration'], 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-blue-900">
                                    {{ number_format($summary['average_monitoring'], 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-ink text-sm">
                                    {{ number_format($summary['average_final_score'], 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-black uppercase bg-blue-100 text-blue-900 border border-blue-200">
                                        {{ $summary['municipal_classification'] }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif

    <!-- SEÇÃO: CADERNO METODOLÓGICO -->
    @if ($activeTab === 'guide')
        <div class="bg-white rounded-3xl border border-line shadow-panel p-6 sm:p-8 space-y-6">
            <div class="border-b border-line pb-4">
                <div class="flex items-center gap-2 text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                    <span>Base Normativa Ministerial</span>
                    <span>·</span>
                    <span>Portaria GM/MS nº 3.493/2024</span>
                </div>
                <h3 class="text-xl font-bold text-ink">Metodologia do Componente II · Vínculo e Acompanhamento Territorial (CVAT)</h3>
                <p class="text-xs text-muted mt-1">Regras de cálculo, pesos, intervalos e faixas de repasse do incentivo financeiro federal.</p>
            </div>

            <!-- Grid com as duas dimensões -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dimensão Cadastro -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-teal-900 flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-100 text-teal-800 text-xs font-bold">1</span>
                            Dimensão Cadastro (MICI)
                        </h4>
                        <span class="px-2.5 py-1 rounded-md bg-teal-100 text-teal-900 text-xs font-black">Peso 3,0</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Avalia a proporção da população do território cadastrada e atualizada nos últimos 24 meses pelas equipes de Saúde da Família (eSF) e de Atenção Primária (eAP) no e-SUS APS PEC ou simplificado.
                    </p>
                    <div class="text-xs text-slate-500 bg-white p-3 rounded-xl border border-slate-200">
                        <strong class="text-slate-700">Fórmula:</strong> Proporção de pessoas com cadastro individual válido vinculadas à equipe sobre o parâmetro populacional normativo.
                    </div>
                </div>

                <!-- Dimensão Acompanhamento -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-blue-900 flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-800 text-xs font-bold">2</span>
                            Dimensão Acompanhamento
                        </h4>
                        <span class="px-2.5 py-1 rounded-md bg-blue-100 text-blue-900 text-xs font-black">Peso 7,0</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Avalia a regularidade do acompanhamento territorial no domicílio através das visitas domiciliares contínuas e busca ativa realizada pelos Agentes Comunitários de Saúde (ACS/TACS).
                    </p>
                    <div class="text-xs text-slate-500 bg-white p-3 rounded-xl border border-slate-200">
                        <strong class="text-slate-700">Fórmula:</strong> Proporção de pessoas cadastradas que receberam acompanhamento territorial e visitas de ACS dentro da vigência.
                    </div>
                </div>
            </div>

            <!-- Tabela de Classificação Oficial do Incentivo Financeiro (Quadro 5 da NT 08/2026) -->
            <div class="space-y-3 pt-2">
                <h4 class="text-sm font-bold text-ink">Quadro 5 · Classificação para o Incentivo Financeiro conforme a Nota Final do Componente II</h4>
                <div class="overflow-x-auto rounded-2xl border border-line">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-line">
                                <th class="py-3 px-4">Nota Final do Componente II (Soma Cadastro + Acompanhamento)</th>
                                <th class="py-3 px-4">Classificação Oficial</th>
                                <th class="py-3 px-4">Incentivo Financeiro / Repasse</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr class="bg-blue-50/40 font-semibold">
                                <td class="py-3 px-4 font-mono text-blue-950">&gt; 8,5</td>
                                <td class="py-3 px-4"><span class="px-2.5 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">Ótimo</span></td>
                                <td class="py-3 px-4 text-blue-900">Incentivo financeiro integral (100% do valor de referência)</td>
                            </tr>
                            <tr class="bg-emerald-50/40">
                                <td class="py-3 px-4 font-mono text-emerald-950">&ge; 7,0 e &le; 8,5</td>
                                <td class="py-3 px-4"><span class="px-2.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">Bom</span></td>
                                <td class="py-3 px-4 text-emerald-900">Incentivo financeiro ponderado (75% do valor de referência)</td>
                            </tr>
                            <tr class="bg-amber-50/40">
                                <td class="py-3 px-4 font-mono text-amber-950">&ge; 5,0 e &lt; 7,0</td>
                                <td class="py-3 px-4"><span class="px-2.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">Suficiente</span></td>
                                <td class="py-3 px-4 text-amber-900">Incentivo financeiro parcial (50% do valor de referência)</td>
                            </tr>
                            <tr class="bg-orange-50/40">
                                <td class="py-3 px-4 font-mono text-orange-950">&lt; 5,0</td>
                                <td class="py-3 px-4"><span class="px-2.5 py-0.5 rounded bg-orange-100 text-orange-900 font-bold">Regular</span></td>
                                <td class="py-3 px-4 text-orange-900">Incentivo financeiro mínimo (25% do valor de referência)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Documentos de Referência -->
            <div class="pt-4 border-t border-line flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500">
                <div class="space-y-1">
                    <p><strong>Fonte de Dados:</strong> Sistema de Informação para a Atenção Primária à Saúde (Siaps / Ministério da Saúde).</p>
                    <p><strong>Normativa Vigente:</strong> Nota Técnica nº 08/2026-DEAPS/SAPS/MS (revoga NT 06/2025).</p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        href="https://sisaps.saude.gov.br"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold transition"
                    >
                        <span>Acessar Portal Siaps Oficial</span>
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE IMPORTAÇÃO OPCIONAL DE CSV DO SIAPS -->
    @if ($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-fade-in" role="dialog" aria-modal="true" aria-labelledby="modal-siaps-title">
            <div class="relative w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl border border-line space-y-5 animate-scale-up" @click.outside="$wire.closeImportModal()">
                <!-- Cabeçalho do Modal -->
                <div class="flex items-start justify-between border-b border-line pb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-100 text-teal-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <h2 id="modal-siaps-title" class="text-base font-bold text-ink">Importar Arquivo CSV do Siaps</h2>
                            <p class="text-xs text-muted">Carregamento opcional de dados oficiais do Ministério da Saúde</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeImportModal"
                        class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition cursor-pointer"
                        aria-label="Fechar"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Mensagem de Feedback -->
                @if ($importMessage)
                    <div class="rounded-2xl p-4 text-xs font-medium border shadow-xs flex items-start gap-3 {{ $importStatus === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' }}">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md {{ $importStatus === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            @if ($importStatus === 'success')
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            @else
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            @endif
                        </span>
                        <div class="leading-relaxed font-medium">{{ $importMessage }}</div>
                    </div>
                @endif

                <!-- Explicação do Formato -->
                <div class="rounded-2xl bg-slate-50 border border-slate-200/80 p-3.5 text-xs text-slate-600 leading-relaxed">
                    <p class="font-bold text-slate-800 mb-1">Arquivos CSV Suportados:</p>
                    <ul class="list-disc pl-4 space-y-0.5 text-[11px]">
                        <li><strong>Desempenho Quadrimestral das Equipes</strong> (com colunas: Quadrimestre, CNES, INE, Equipe, Notas e Conceitos).</li>
                        <li><strong>Distribuição das Dimensões</strong> (com colunas: Período, UF, IBGE, Cadastro, Acompanhamento, Faixas Ótimo/Bom/Suficiente/Regular).</li>
                    </ul>
                </div>

                <!-- Formulário de Upload -->
                <form wire:submit="uploadCsv" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Selecione o arquivo CSV do seu computador:</label>
                        <div class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 hover:border-teal-500 bg-slate-50/50 p-6 text-center transition cursor-pointer">
                            <svg class="h-8 w-8 text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                            </svg>
                            <input
                                type="file"
                                wire:model="csvFile"
                                accept=".csv,.txt"
                                class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                            />
                            @if ($csvFile)
                                <p class="text-xs font-bold text-teal-800">{{ $csvFile->getClientOriginalName() }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Clique para trocar de arquivo</p>
                            @else
                                <p class="text-xs font-semibold text-slate-700">Clique ou arraste o arquivo CSV aqui</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">Tamanho máximo: 10 MB (.csv)</p>
                            @endif
                        </div>
                        @error('csvFile')
                            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Indicador de Upload em Andamento -->
                    <div wire:loading wire:target="csvFile, uploadCsv, importServerDefaults" class="w-full">
                        <div class="flex items-center gap-2 text-xs font-semibold text-teal-700">
                            <svg class="animate-spin h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Processando arquivo CSV...</span>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2.5 pt-2 border-t border-line">
                        <button
                            type="button"
                            wire:click="importServerDefaults"
                            wire:loading.attr="disabled"
                            class="text-xs font-semibold text-teal-800 hover:text-teal-950 underline underline-offset-2 transition cursor-pointer py-1"
                            title="Carrega os arquivos CSV padrão salvos na pasta de importação do município"
                        >
                            Restaurar CSVs padrão do servidor
                        </button>

                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                            <button
                                type="button"
                                wire:click="closeImportModal"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer"
                            >
                                Fechar
                            </button>

                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                @if (!$csvFile) disabled @endif
                                class="px-4 py-2 text-xs font-bold text-white bg-teal-700 hover:bg-teal-800 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-xs transition cursor-pointer flex items-center gap-2"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                <span>Importar CSV</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
