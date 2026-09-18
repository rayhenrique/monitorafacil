<div class="px-4 py-6 sm:px-8 max-w-7xl mx-auto space-y-6">
    <x-settings-tabs
        title="Log de Auditoria e Sincronizações"
        subtitle="Histórico detalhado das execuções de consolidação e rotinas do e-SUS PEC"
        activeTab="audit-logs"
    />

    <!-- Filtros e Resumo -->
    <div class="rounded-3xl border border-line bg-white shadow-sm overflow-hidden">
        <div class="p-5 border-b border-line flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-muted">Filtrar por status:</span>
                <div class="inline-flex rounded-xl bg-slate-200/70 p-1 text-xs">
                    <button
                        type="button"
                        wire:click="$set('statusFilter', 'all')"
                        class="rounded-lg px-3 py-1 font-medium transition {{ $statusFilter === 'all' ? 'bg-white text-ink shadow-xs font-semibold' : 'text-slate-600 hover:text-ink' }}"
                    >
                        Todos
                    </button>
                    <button
                        type="button"
                        wire:click="$set('statusFilter', 'success')"
                        class="rounded-lg px-3 py-1 font-medium transition {{ $statusFilter === 'success' ? 'bg-white text-emerald-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-ink' }}"
                    >
                        Sucesso
                    </button>
                    <button
                        type="button"
                        wire:click="$set('statusFilter', 'failed')"
                        class="rounded-lg px-3 py-1 font-medium transition {{ $statusFilter === 'failed' ? 'bg-white text-rose-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-ink' }}"
                    >
                        Falhas
                    </button>
                    <button
                        type="button"
                        wire:click="$set('statusFilter', 'running')"
                        class="rounded-lg px-3 py-1 font-medium transition {{ $statusFilter === 'running' ? 'bg-white text-amber-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-ink' }}"
                    >
                        Em Execução
                    </button>
                </div>
            </div>

            <div class="text-xs text-muted">
                Total de registros: <span class="font-semibold text-ink">{{ $logs->total() }}</span>
            </div>
        </div>

        <!-- Tabela de Logs -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line text-left text-xs text-ink">
                <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-muted">
                    <tr>
                        <th scope="col" class="px-6 py-3.5">ID</th>
                        <th scope="col" class="px-6 py-3.5">Status</th>
                        <th scope="col" class="px-6 py-3.5">Início</th>
                        <th scope="col" class="px-6 py-3.5">Término</th>
                        <th scope="col" class="px-6 py-3.5">Duração</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line bg-white">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="whitespace-nowrap px-6 py-4 font-mono text-muted">
                                #{{ $log->id }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($log->status->value === 'success')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 border border-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Sucesso
                                    </span>
                                @elseif ($log->status->value === 'failed')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700 border border-rose-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                        Falha
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 border border-amber-200">
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-500"></span>
                                        </span>
                                        Em Execução
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-ink tabular-nums">
                                {{ $log->started_at ? $log->started_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-muted tabular-nums">
                                {{ $log->finished_at ? $log->finished_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-muted tabular-nums">
                                @if ($log->started_at && $log->finished_at)
                                    {{ $log->started_at->diff($log->finished_at)->format('%H:%I:%S') }}
                                @elseif ($log->started_at)
                                    <span class="text-amber-600">processando...</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <button
                                    type="button"
                                    wire:click="viewDetails({{ $log->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition"
                                >
                                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Detalhes</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-muted">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="font-medium text-slate-600">Nenhum registro de auditoria encontrado</p>
                                    <p class="text-xs text-muted">As sincronizações executadas aparecerão aqui automaticamente.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-line px-6 py-3.5 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Detalhes do Log -->
    @if ($showDetailModal && $selectedLog)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-fade-in">
            <div class="relative w-full max-w-2xl rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-200" @click.outside="$wire.closeDetailModal()">
                <div class="flex items-center justify-between border-b border-line pb-4 mb-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-50 text-teal-700 font-mono text-xs font-bold border border-teal-200">
                            #{{ $selectedLog->id }}
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-ink">Registro de Execução</h3>
                            <p class="text-xs text-muted">Iniciado em {{ $selectedLog->started_at ? $selectedLog->started_at->format('d/m/Y \à\s H:i:s') : '-' }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="rounded-lg p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="rounded-2xl border border-line bg-slate-50/70 p-3.5">
                            <span class="text-[11px] text-muted block">Status</span>
                            <span class="text-xs font-semibold text-ink mt-0.5 block">
                                {{ ucfirst($selectedLog->status->value) }}
                            </span>
                        </div>
                        <div class="rounded-2xl border border-line bg-slate-50/70 p-3.5">
                            <span class="text-[11px] text-muted block">Término</span>
                            <span class="text-xs font-semibold text-ink mt-0.5 block tabular-nums">
                                {{ $selectedLog->finished_at ? $selectedLog->finished_at->format('d/m/Y H:i:s') : 'Em andamento' }}
                            </span>
                        </div>
                        <div class="rounded-2xl border border-line bg-slate-50/70 p-3.5 col-span-2 sm:col-span-1">
                            <span class="text-[11px] text-muted block">Tempo Total</span>
                            <span class="text-xs font-semibold text-ink mt-0.5 block tabular-nums">
                                @if ($selectedLog->started_at && $selectedLog->finished_at)
                                    {{ $selectedLog->started_at->diff($selectedLog->finished_at)->format('%H:%I:%S') }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                    </div>

                    @if ($selectedLog->error_message)
                        <div>
                            <span class="text-xs font-bold text-rose-700 block mb-1.5">Mensagem de Erro / Falha Registrada</span>
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 font-mono text-xs text-rose-950 overflow-x-auto whitespace-pre-wrap max-h-60 leading-relaxed">
                                {{ $selectedLog->error_message }}
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs text-emerald-900 flex items-center gap-3">
                            <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>A rotina foi executada sem relatar erros ou interrupções.</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end pt-5 border-t border-line mt-6">
                    <button
                        type="button"
                        wire:click="closeDetailModal"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
