<div>
    @if ($show)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop com blur -->
            <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <!-- Card do Modal -->
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200">
                    <!-- Topo com gradiente executivo esmeralda -->
                    <div class="bg-gradient-to-br from-[#0c1f1c] via-[#0f2d26] to-[#081714] p-6 sm:p-8 text-white relative">
                        <!-- Botão fechar -->
                        <button
                            type="button"
                            wire:click="acknowledge"
                            class="absolute top-5 right-5 text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/10 transition"
                            aria-label="Fechar"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <div class="flex items-center gap-2 mb-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-500/25 px-3 py-1 text-xs font-semibold text-teal-300 border border-teal-500/30">
                                <span class="h-1.5 w-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                                Atualização do Sistema · {{ $release['version'] ?? \App\Services\VersionService::CURRENT_VERSION }}
                            </span>
                            <span class="text-xs text-slate-400">Lançada em {{ $release['date'] ?? '' }}</span>
                        </div>

                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-white" id="modal-title">
                            🎉 {{ $release['title'] ?? 'Novidades da Versão' }}
                        </h3>

                        <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                            {{ $release['summary'] ?? '' }}
                        </p>
                    </div>

                    <!-- Conteúdo: Lista de Destaques -->
                    <div class="p-6 sm:p-8 space-y-4 max-h-[50vh] overflow-y-auto">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Principais Destaques Desta Atualização
                        </h4>

                        <div class="space-y-3">
                            @foreach ($release['highlights'] ?? [] as $item)
                                <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-150/80">
                                    @if ($item['type'] === 'novo')
                                        <span class="rounded-lg bg-emerald-100 text-emerald-800 px-2 py-0.5 text-[11px] font-bold shrink-0">Novo</span>
                                    @elseif ($item['type'] === 'melhoria')
                                        <span class="rounded-lg bg-sky-100 text-sky-800 px-2 py-0.5 text-[11px] font-bold shrink-0">Melhoria</span>
                                    @elseif ($item['type'] === 'correcao')
                                        <span class="rounded-lg bg-amber-100 text-amber-800 px-2 py-0.5 text-[11px] font-bold shrink-0">Correção</span>
                                    @elseif ($item['type'] === 'seguranca')
                                        <span class="rounded-lg bg-purple-100 text-purple-800 px-2 py-0.5 text-[11px] font-bold shrink-0">Segurança</span>
                                    @else
                                        <span class="rounded-lg bg-slate-100 text-slate-800 px-2 py-0.5 text-[11px] font-bold shrink-0">Item</span>
                                    @endif

                                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                                        {{ $item['text'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Rodapé do Modal com Ações -->
                    <div class="bg-slate-50 px-6 py-4 sm:px-8 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-200">
                        <button
                            type="button"
                            wire:click="viewFullHistory"
                            class="inline-flex items-center justify-center gap-2 text-xs font-semibold text-slate-600 hover:text-teal-800 transition py-2"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Ver Histórico Completo de Versões</span>
                        </button>

                        <button
                            type="button"
                            wire:click="acknowledge"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-teal-700 hover:bg-teal-600 px-6 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-md shadow-teal-950/20 transition cursor-pointer"
                        >
                            <span>Entendido, Continuar</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
