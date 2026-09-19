<div class="px-4 py-6 sm:px-8 max-w-7xl mx-auto space-y-6">
    <x-settings-tabs
        title="Processamento de Dados"
        subtitle="Consolidação e sincronização analítica das tabelas do e-SUS PEC para os Indicadores da APS"
        activeTab="data-processing"
    />

    <!-- Feedback da Execução Manual -->
    @if ($processMessage)
        <div class="rounded-2xl p-4 text-xs font-medium border shadow-sm flex items-start gap-3 animate-fade-in {{ $processStatus === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' }}">
            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg {{ $processStatus === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                @if ($processStatus === 'success')
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                @else
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                @endif
            </div>
            <div class="min-w-0 flex-1 whitespace-pre-wrap font-medium leading-relaxed">{{ $processMessage }}</div>
        </div>
    @endif

    <!-- Card de Ação Principal & Barra de Progresso -->
    <div
        class="rounded-3xl border border-line bg-white p-6 sm:p-8 shadow-sm space-y-6"
        x-data="{
            estimatedProgress: 8,
            estimatedStep: 'Preparando o processamento...',
            processingScope: 'dados do e-SUS PEC',
            progressTimer: null,
            startProgress(scope) {
                window.clearInterval(this.progressTimer);
                this.processingScope = scope;
                this.estimatedProgress = 8;
                this.estimatedStep = `Iniciando ${scope}...`;
                this.progressTimer = window.setInterval(() => {
                    if (this.estimatedProgress >= 92) {
                        window.clearInterval(this.progressTimer);
                        return;
                    }

                    const increment = this.estimatedProgress < 45 ? 4 : (this.estimatedProgress < 75 ? 2 : 1);
                    this.estimatedProgress = Math.min(92, this.estimatedProgress + increment);

                    if (this.estimatedProgress >= 75) {
                        this.estimatedStep = `Finalizando ${this.processingScope}...`;
                    } else if (this.estimatedProgress >= 45) {
                        this.estimatedStep = `Consolidando ${this.processingScope}...`;
                    } else if (this.estimatedProgress >= 20) {
                        this.estimatedStep = 'Validando a conexão e as tabelas necessárias...';
                    }
                }, 900);
            }
        }"
    >
        <!-- Cabeçalho Expandido (Largura Total sem Compressão) -->
        <div class="space-y-2.5 border-b border-line pb-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-800 border border-teal-200">
                    <span class="h-2 w-2 rounded-full bg-teal-600 animate-pulse"></span>
                    Cofinanciamento Federal · Portaria GM/MS 3.493/2024
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600 border border-slate-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Sincronização Direta com PostgreSQL e-SUS PEC
                </span>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight flex items-center gap-2.5 mt-1">
                    <svg class="h-6 w-6 text-teal-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Processar Dados do e-SUS PEC</span>
                </h2>
                <p class="text-xs sm:text-sm text-muted mt-1.5 leading-relaxed max-w-4xl">
                    Executa a leitura, validação e consolidação analítica nas tabelas do e-SUS PEC. Escolha abaixo o escopo desejado: processe sob demanda um componente específico para resposta rápida ou acione a rotina geral completa.
                </p>
            </div>
        </div>

        <!-- Grid Responsivo de Ações por Escopo -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-muted">Selecione o Escopo de Processamento</span>
                <span class="text-[11px] text-slate-500 hidden sm:inline">Clique no card correspondente para iniciar</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3.5">
                <!-- Card 1: Vínculo & Território (CVAT) -->
                <button
                    type="button"
                    wire:click="processCvat"
                    x-on:click="startProgress('o Vínculo e Acompanhamento Territorial (CVAT)')"
                    wire:loading.attr="disabled"
                    class="group relative flex flex-col justify-between p-4 sm:p-4.5 rounded-2xl border transition-all duration-200 text-left cursor-pointer bg-gradient-to-b from-emerald-50/80 to-white border-emerald-300/80 hover:border-emerald-500 hover:shadow-md hover:shadow-emerald-950/10 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 disabled:opacity-60"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded-lg bg-emerald-100/90 text-emerald-800 p-2 group-hover:scale-105 transition">
                                <svg class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider rounded-full px-2 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-200">
                                CVAT
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-ink group-hover:text-emerald-900 transition leading-snug">
                                Vínculo & Território
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-normal">
                                MICI, MICDT & Nominal
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-emerald-100 flex items-center justify-between text-xs font-semibold text-emerald-800">
                        <span wire:loading.remove wire:target="processCvat" class="inline-flex items-center gap-1">
                            <span>Processar</span>
                            <svg class="h-3.5 w-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="processCvat" class="inline-flex items-center gap-1.5 text-emerald-700 font-bold">
                            <svg class="animate-spin h-3.5 w-3.5 text-emerald-700" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Extraindo...</span>
                        </span>
                    </div>
                </button>

                <!-- Card 2: Indicador C1 -->
                <button
                    type="button"
                    wire:click="processC1"
                    x-on:click="startProgress('o Indicador C1')"
                    wire:loading.attr="disabled"
                    class="group relative flex flex-col justify-between p-4 sm:p-4.5 rounded-2xl border transition-all duration-200 text-left cursor-pointer bg-gradient-to-b from-teal-50/80 to-white border-teal-300/80 hover:border-teal-500 hover:shadow-md hover:shadow-teal-950/10 focus:outline-none focus:ring-2 focus:ring-teal-500/40 disabled:opacity-60"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded-lg bg-teal-100/90 text-teal-800 p-2 group-hover:scale-105 transition">
                                <svg class="h-5 w-5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider rounded-full px-2 py-0.5 bg-teal-100 text-teal-800 border border-teal-200">
                                Indicador C1
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-ink group-hover:text-teal-900 transition leading-snug">
                                Mais Acesso
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-normal">
                                Atendimentos Médicos & Enf.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-teal-100 flex items-center justify-between text-xs font-semibold text-teal-800">
                        <span wire:loading.remove wire:target="processC1" class="inline-flex items-center gap-1">
                            <span>Processar</span>
                            <svg class="h-3.5 w-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="processC1" class="inline-flex items-center gap-1.5 text-teal-700 font-bold">
                            <svg class="animate-spin h-3.5 w-3.5 text-teal-700" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Processando...</span>
                        </span>
                    </div>
                </button>

                <!-- Card 3: Indicador C2 -->
                <button
                    type="button"
                    wire:click="processC2"
                    x-on:click="startProgress('o Indicador C2 e a lista de crianças')"
                    wire:loading.attr="disabled"
                    class="group relative flex flex-col justify-between p-4 sm:p-4.5 rounded-2xl border transition-all duration-200 text-left cursor-pointer bg-gradient-to-b from-indigo-50/80 to-white border-indigo-300/80 hover:border-indigo-500 hover:shadow-md hover:shadow-indigo-950/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 disabled:opacity-60"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded-lg bg-indigo-100/90 text-indigo-800 p-2 group-hover:scale-105 transition">
                                <svg class="h-5 w-5 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider rounded-full px-2 py-0.5 bg-indigo-100 text-indigo-800 border border-indigo-200">
                                Indicador C2
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-ink group-hover:text-indigo-900 transition leading-snug">
                                Crianças (C2)
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-normal">
                                Desenvolvimento Infantil
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-indigo-100 flex items-center justify-between text-xs font-semibold text-indigo-800">
                        <span wire:loading.remove wire:target="processC2" class="inline-flex items-center gap-1">
                            <span>Processar</span>
                            <svg class="h-3.5 w-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="processC2" class="inline-flex items-center gap-1.5 text-indigo-700 font-bold">
                            <svg class="animate-spin h-3.5 w-3.5 text-indigo-700" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Processando...</span>
                        </span>
                    </div>
                </button>

                <!-- Card 4: Indicador C3 -->
                <button
                    type="button"
                    wire:click="processC3"
                    x-on:click="startProgress('o Indicador C3 e a lista de gestantes')"
                    wire:loading.attr="disabled"
                    class="group relative flex flex-col justify-between p-4 sm:p-4.5 rounded-2xl border transition-all duration-200 text-left cursor-pointer bg-gradient-to-b from-rose-50/80 to-white border-rose-300/80 hover:border-rose-500 hover:shadow-md hover:shadow-rose-950/10 focus:outline-none focus:ring-2 focus:ring-rose-500/40 disabled:opacity-60"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded-lg bg-rose-100/90 text-rose-800 p-2 group-hover:scale-105 transition">
                                <svg class="h-5 w-5 text-rose-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider rounded-full px-2 py-0.5 bg-rose-100 text-rose-800 border border-rose-200">
                                Indicador C3
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-ink group-hover:text-rose-900 transition leading-snug">
                                Gestantes (C3)
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-normal">
                                Pré-Natal & Puerpério
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-rose-100 flex items-center justify-between text-xs font-semibold text-rose-800">
                        <span wire:loading.remove wire:target="processC3" class="inline-flex items-center gap-1">
                            <span>Processar</span>
                            <svg class="h-3.5 w-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="processC3" class="inline-flex items-center gap-1.5 text-rose-700 font-bold">
                            <svg class="animate-spin h-3.5 w-3.5 text-rose-700" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Processando...</span>
                        </span>
                    </div>
                </button>

                <!-- Card 5: Processamento Geral Completo -->
                <button
                    type="button"
                    wire:click="processAll"
                    x-on:click="startProgress('o processamento geral')"
                    wire:loading.attr="disabled"
                    class="group relative flex flex-col justify-between p-4 sm:p-4.5 rounded-2xl border transition-all duration-200 text-left cursor-pointer bg-slate-900 border-slate-700/80 hover:bg-slate-800 hover:border-slate-600 hover:shadow-lg hover:shadow-slate-950/20 focus:outline-none focus:ring-2 focus:ring-slate-400 disabled:opacity-60 text-white"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded-lg bg-slate-800 text-teal-400 p-2 group-hover:scale-105 transition border border-slate-700">
                                <svg class="h-5 w-5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider rounded-full px-2 py-0.5 bg-teal-950/80 text-teal-300 border border-teal-800/80">
                                Geral
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white group-hover:text-teal-200 transition leading-snug">
                                Processamento Geral
                            </h3>
                            <p class="text-[11px] text-slate-300 mt-0.5 leading-normal">
                                Todos os Indicadores & Dados
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-xs font-semibold text-teal-300">
                        <span wire:loading.remove wire:target="processAll" class="inline-flex items-center gap-1">
                            <span>Processar Tudo</span>
                            <svg class="h-3.5 w-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="processAll" class="inline-flex items-center gap-1.5 text-teal-300 font-bold">
                            <svg class="animate-spin h-3.5 w-3.5 text-teal-300" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Processando...</span>
                        </span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Alerta Informativo sobre Rotina Agendada -->
        <div class="rounded-2xl border border-teal-200/80 bg-teal-50/40 p-4 flex items-start gap-3.5 text-xs">
            <div class="p-1.5 rounded-xl bg-teal-100 text-teal-800 shrink-0 mt-0.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="text-teal-950 leading-relaxed">
                <strong class="font-bold text-teal-900">Rotina Noturna Agendada Automática:</strong> O processamento automatizado no servidor executa diariamente às <strong>03:30 (horário de Brasília)</strong> sempre no modo geral completo (<span class="font-mono font-bold bg-teal-100/70 px-1.5 py-0.5 rounded text-[11px]">--scope=all</span>), auditando e consolidando todos os indicadores clínicos e territoriais (MICI e MICDT).
            </div>
        </div>

        <!-- Barra estimada visível durante toda a requisição Livewire -->
        <div
            class="hidden border-t border-line pt-5 space-y-3"
            wire:loading.class.remove="hidden"
            wire:loading.class.add="block"
            wire:target="processC1,processC2,processC3,processAll,processNow,processCvat"
        >
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between text-xs" aria-live="polite" aria-atomic="true">
                <div class="min-w-0 flex items-start gap-2">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-teal-600 motion-safe:animate-pulse"></span>
                    <div class="min-w-0">
                        <span class="font-bold text-ink">Progresso estimado</span>
                        <p class="mt-0.5 break-words font-mono text-[11px] leading-relaxed text-muted" x-text="estimatedStep"></p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:justify-end">
                    <span class="rounded-full border border-teal-200 bg-teal-50 px-2.5 py-1 text-[10px] font-bold text-teal-800">Carregando</span>
                    <span class="min-w-11 text-right font-mono font-bold text-teal-800" x-text="`${estimatedProgress}%`"></span>
                </div>
            </div>

            <div
                class="h-3.5 w-full overflow-hidden rounded-full border border-slate-200/80 bg-slate-100 p-0.5"
                role="progressbar"
                aria-label="Progresso estimado do processamento dos dados do e-SUS PEC"
                aria-valuemin="0"
                aria-valuemax="100"
                x-bind:aria-valuenow="estimatedProgress"
                x-bind:aria-valuetext="`${estimatedProgress} por cento. ${estimatedStep}`"
                aria-busy="true"
            >
                <div
                    class="relative h-full overflow-hidden rounded-full bg-gradient-to-r from-teal-700 via-emerald-500 to-teal-500 transition-[width] duration-500 ease-out"
                    x-bind:style="`width: ${estimatedProgress}%`"
                >
                    <span class="absolute inset-0 bg-white/20 motion-safe:animate-pulse"></span>
                </div>
            </div>

            <p class="text-[11px] leading-relaxed text-muted">Mantenha esta página aberta. A barra avança enquanto o servidor processa e confirma o resultado ao terminar.</p>
        </div>

        @if ($progressPercent > 0 && $processStatus !== null)
            <div wire:loading.remove wire:target="processC1,processC2,processC3,processAll,processNow,processCvat">
                <x-processing-progress
                    :percent="$progressPercent"
                    :step="$currentStep ?: 'Preparando o processamento...'"
                    :running="false"
                    :status="$processStatus"
                />
            </div>
        @endif
    </div>

    <!-- Diagnóstico de Tabelas do e-SUS PEC Processadas -->
    @if (!empty($tablesReport))
        <div class="rounded-3xl border border-line bg-white p-6 sm:p-8 shadow-sm space-y-4 animate-fade-in">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-line pb-4">
                <div>
                    <h3 class="text-base font-bold text-ink flex items-center gap-2">
                        <span>Tabelas do e-SUS PEC Processadas</span>
                        <span class="rounded-full bg-teal-100 text-teal-800 px-2.5 py-0.5 text-[10px] font-bold">
                            {{ count($tablesReport) }} tabelas auditadas
                        </span>
                    </h3>
                    <p class="text-xs text-muted">Status de extração, contagem de registros e integridade dos dados clínicos e territoriais</p>
                </div>
                @if ($executionTimeMs)
                    <span class="text-xs font-mono font-semibold text-slate-500 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        Tempo de Execução: {{ number_format($executionTimeMs / 1000, 2, ',', '.') }} s
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-line font-bold">
                        <tr>
                            <th class="py-3 px-4">Tabela e-SUS PEC</th>
                            <th class="py-3 px-4">Finalidade do Indicador</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Registros Processados</th>
                            <th class="py-3 px-4">Detalhamento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($tablesReport as $tabKey => $table)
                            @php
                                $statusBadge = match ($table['status']) {
                                    'success' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'simulated' => 'bg-sky-100 text-sky-800 border-sky-200',
                                    'warning' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };

                                $statusLabel = match ($table['status']) {
                                    'success' => 'Processada',
                                    'simulated' => 'Consolidada',
                                    'warning' => 'Aviso',
                                    default => 'Pendente',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-ink">{{ $table['name'] }}</td>
                                <td class="py-3.5 px-4 text-slate-600">{{ $table['description'] }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $statusBadge }}">
                                        @if ($table['status'] === 'success' || $table['status'] === 'simulated')
                                            <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        @endif
                                        <span>{{ $statusLabel }}</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-ink">
                                    {{ number_format($table['rows'], 0, '', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 text-xs">
                                    {{ $table['message'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Resumo do Último Snapshot Gravado -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Status Geral do Último Snapshot -->
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted">Último Snapshot Gravado</h3>

            @if ($latestRegistration)
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-ink font-mono">{{ $latestRegistration->year }} / Q{{ $latestRegistration->quarter }}</span>
                    <span class="rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-semibold text-teal-800 border border-teal-200">Consolidado</span>
                </div>
                <div class="text-xs text-muted border-t border-line pt-3 space-y-2">
                    <div class="flex justify-between">
                        <span>Gravado em:</span>
                        <span class="font-medium text-ink tabular-nums">{{ $latestRegistration->updated_at ? $latestRegistration->updated_at->format('d/m/Y H:i') : '-' }}</span>
                    </div>
                    @if ($lastLog)
                        <div class="flex justify-between">
                            <span>Status da última rotina:</span>
                            <span class="font-semibold {{ $lastLog->status->value === 'success' ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ ucfirst($lastLog->status->value) }}
                            </span>
                        </div>
                    @endif
                </div>
            @else
                <div class="py-6 text-center text-muted">
                    <p class="text-xs">Nenhum snapshot foi consolidado ainda no banco local.</p>
                </div>
            @endif
        </div>

        <!-- Equipes Consolidadas -->
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted">Equipes Homologadas Ativas</h3>

            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="rounded-2xl border border-line bg-slate-50/70 p-3">
                    <span class="text-[10px] text-muted block">eSF</span>
                    <span class="text-xl font-bold text-ink font-mono mt-1 block">{{ $teams['esf'] ?? 0 }}</span>
                </div>
                <div class="rounded-2xl border border-line bg-slate-50/70 p-3">
                    <span class="text-[10px] text-muted block">eSB</span>
                    <span class="text-xl font-bold text-ink font-mono mt-1 block">{{ $teams['saude_bucal'] ?? 0 }}</span>
                </div>
                <div class="rounded-2xl border border-line bg-slate-50/70 p-3">
                    <span class="text-[10px] text-muted block">eMulti</span>
                    <span class="text-xl font-bold text-ink font-mono mt-1 block">{{ $teams['emulti'] ?? 0 }}</span>
                </div>
            </div>
            <p class="text-[11px] text-muted leading-relaxed">
                Quantitativo de equipes ativas apurado a partir de <span class="font-mono text-slate-700">tb_dim_equipe</span>.
            </p>
        </div>

        <!-- Cadastros MICI e MICDT -->
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted">Cadastros Estruturantes</h3>

            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="rounded-2xl border border-line bg-slate-50/70 p-3">
                    <span class="text-[10px] text-muted block">MICI (Indivíduos)</span>
                    <span class="text-xl font-bold text-ink font-mono mt-1 block">
                        {{ number_format(($latestRegistration->mici_updated_count ?? 0) + ($latestRegistration->mici_outdated_count ?? 0), 0, '', '.') }}
                    </span>
                    <span class="text-[10px] text-emerald-700 font-semibold block mt-1">
                        {{ number_format($latestRegistration->mici_updated_count ?? 0, 0, '', '.') }} vigentes
                    </span>
                </div>
                <div class="rounded-2xl border border-line bg-slate-50/70 p-3">
                    <span class="text-[10px] text-muted block">MICDT (Domicílios)</span>
                    <span class="text-xl font-bold text-ink font-mono mt-1 block">
                        {{ number_format(($latestRegistration->micdt_updated_count ?? 0) + ($latestRegistration->micdt_outdated_count ?? 0), 0, '', '.') }}
                    </span>
                    <span class="text-[10px] text-emerald-700 font-semibold block mt-1">
                        {{ number_format($latestRegistration->micdt_updated_count ?? 0, 0, '', '.') }} vigentes
                    </span>
                </div>
            </div>
            <p class="text-[11px] text-muted leading-relaxed">
                Apurado a partir de <span class="font-mono text-slate-700">tb_fat_cad_individual</span> e <span class="font-mono text-slate-700">tb_fat_cad_domiciliar</span>.
            </p>
        </div>
    </div>
</div>
