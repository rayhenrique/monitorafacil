<div class="px-4 py-6 sm:px-8 max-w-7xl mx-auto space-y-6">
    <x-settings-tabs
        title="Processamento de Dados"
        subtitle="Consolidação manual do snapshot analítico e sincronização de dados do e-SUS PEC"
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
            <div class="min-w-0 flex-1 whitespace-pre-wrap font-mono">{{ $processMessage }}</div>
        </div>
    @endif

    <!-- Card de Ação Principal -->
    <div class="rounded-3xl border border-line bg-white p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div class="max-w-2xl space-y-1">
                <h2 class="text-base font-bold text-ink">Disparar Consolidação Manual</h2>
                <p class="text-xs text-muted leading-relaxed">
                    Executa a rotina <span class="font-mono text-slate-800 font-medium">esus:sync-snapshot</span>, lendo as equipes homologadas do arquivo XML, cruzando com a base de dados do e-SUS PEC e persistindo os quantitativos municipais de equipes e cadastros (MICI e MICDT).
                </p>
            </div>

            <button
                type="button"
                wire:click="processNow"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-teal-700 hover:bg-teal-800 disabled:opacity-50 px-6 py-3.5 text-xs font-semibold text-white shadow-md shadow-teal-950/20 transition focus:outline-none focus:ring-2 focus:ring-teal-500/30 cursor-pointer shrink-0"
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
                    <span>Consolidando dados...</span>
                </span>
            </button>
        </div>
    </div>

    <!-- Resumo do Último Snapshot Gravado -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Status Geral do Último Snapshot -->
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted">Último Snapshot</h3>

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
            <p class="text-[11px] text-muted">Contabilizadas no cruzamento do XML com a tabela de equipes ativas do e-SUS.</p>
        </div>

        <!-- Cadastros MICI / MICDT -->
        <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted">Cadastros Individuais</h3>

            @if ($latestRegistration)
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-line pb-2">
                        <span class="text-muted">MICI Atualizados:</span>
                        <span class="font-mono font-bold text-emerald-700">{{ number_format($latestRegistration->mici_updated_count, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-line pb-2">
                        <span class="text-muted">MICI Desatualizados:</span>
                        <span class="font-mono font-bold text-amber-700">{{ number_format($latestRegistration->mici_outdated_count, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted">MICDT Atualizados:</span>
                        <span class="font-mono font-bold text-emerald-700">{{ number_format($latestRegistration->micdt_updated_count, 0, ',', '.') }}</span>
                    </div>
                </div>
            @else
                <p class="text-xs text-muted py-6 text-center">Aguardando primeira consolidação.</p>
            @endif
        </div>
    </div>

    <!-- Card Informativo sobre Automação -->
    <div class="rounded-3xl border border-line bg-slate-50/70 p-6 flex items-start gap-4 text-xs text-muted">
        <svg class="h-5 w-5 text-teal-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <strong class="text-ink">Execução Agendada Automática:</strong> O Monitora Fácil possui agendamento no Cron para executar esta mesma rotina diariamente às <span class="font-mono text-slate-800">23:00</span> (fuso {{ config('esus.schedule_timezone', 'America/Maceio') }}). A execução manual acima é útil para atualizar os dados imediatamente após novos atendimentos ou importação de novo XML do CNES.
        </div>
    </div>
</div>
