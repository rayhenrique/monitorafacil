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
    <div class="rounded-3xl border border-line bg-white p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div class="max-w-2xl space-y-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-2.5 py-0.5 text-[11px] font-bold text-teal-800 border border-teal-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-600 animate-pulse"></span>
                        Cofinanciamento Federal · Portaria GM/MS 3.493/2024
                    </span>
                </div>
                <h2 class="text-base sm:text-lg font-bold text-ink">Processar Dados do e-SUS PEC</h2>
                <p class="text-xs text-muted leading-relaxed">
                    Executa a rotina de leitura e consolidação direta nas tabelas fatos e dimensões do e-SUS PEC (<span class="font-mono text-slate-800 font-medium">tb_fat_atendimento_individual</span>, <span class="font-mono text-slate-800 font-medium">tb_dim_equipe</span>, <span class="font-mono text-slate-800 font-medium">tb_dim_tempo</span>, <span class="font-mono text-slate-800 font-medium">tb_fat_cad_*</span>), alimentando o acompanhamento mensal e quadrimestral do Indicador C1 (Mais Acesso) e cadastros estruturantes.
                </p>
            </div>

            <button
                type="button"
                wire:click="processNow"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-teal-700 hover:bg-teal-800 disabled:opacity-60 px-6 py-3.5 text-xs font-semibold text-white shadow-md shadow-teal-950/20 transition focus:outline-none focus:ring-2 focus:ring-teal-500/30 cursor-pointer shrink-0"
            >
                <span wire:loading.remove wire:target="processNow" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Processar Dados Agora</span>
                </span>
                <span wire:loading wire:target="processNow" class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Processando tabelas...</span>
                </span>
            </button>
        </div>

        <!-- Barra de Progresso Interativa -->
        @if ($progressPercent > 0 || $isProcessing)
            <div class="border-t border-line pt-5 space-y-3 animate-fade-in">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-ink">Progresso da Consolidação:</span>
                        <span class="text-muted font-mono">{{ $currentStep }}</span>
                    </div>
                    <span class="font-mono font-bold text-teal-800">{{ $progressPercent }}%</span>
                </div>

                <div class="w-full bg-slate-100 rounded-full h-3.5 overflow-hidden border border-slate-200/80 p-0.5">
                    <div
                        class="bg-gradient-to-r from-teal-600 via-emerald-500 to-teal-500 h-full rounded-full transition-all duration-500 ease-out shadow-xs"
                        style="width: {{ $progressPercent }}%"
                    ></div>
                </div>
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
