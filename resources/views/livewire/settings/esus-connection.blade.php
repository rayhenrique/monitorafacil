<div class="px-4 py-6 sm:px-8 max-w-7xl mx-auto space-y-6">
    <x-settings-tabs
        title="Conexão com o e-SUS PEC"
        subtitle="Parâmetros de leitura do banco de dados PostgreSQL e diagnóstico de conectividade"
        activeTab="esus-connection"
    />

    <!-- Card de Parâmetros e Teste -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Bloco de Parâmetros Configurados -->
            <div class="rounded-3xl border border-line bg-white p-6 sm:p-8 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-line pb-5 mb-6">
                    <div>
                        <h2 class="text-base font-bold text-ink">Parâmetros do PostgreSQL</h2>
                        <p class="text-xs text-muted">Configurações ativas carregadas das variáveis de ambiente (.env)</p>
                    </div>

                    <button
                        type="button"
                        wire:click="testConnection"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 hover:bg-teal-800 disabled:opacity-50 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-teal-500/30 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="testConnection" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                            </svg>
                            <span>Testar Conexão</span>
                        </span>
                        <span wire:loading wire:target="testConnection" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Verificando...</span>
                        </span>
                    </button>
                </div>

                <!-- Grid de Parâmetros -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="rounded-2xl border border-line bg-slate-50/70 p-4">
                        <span class="text-[11px] font-semibold text-muted block mb-1">Host / Servidor</span>
                        <span class="font-mono text-ink font-semibold">{{ $connectionInfo['host'] }}</span>
                    </div>

                    <div class="rounded-2xl border border-line bg-slate-50/70 p-4">
                        <span class="text-[11px] font-semibold text-muted block mb-1">Porta</span>
                        <span class="font-mono text-ink font-semibold">{{ $connectionInfo['port'] }}</span>
                    </div>

                    <div class="rounded-2xl border border-line bg-slate-50/70 p-4">
                        <span class="text-[11px] font-semibold text-muted block mb-1">Nome do Banco de Dados</span>
                        <span class="font-mono text-ink font-semibold">{{ $connectionInfo['database'] }}</span>
                    </div>

                    <div class="rounded-2xl border border-line bg-slate-50/70 p-4">
                        <span class="text-[11px] font-semibold text-muted block mb-1">Usuário Read-Only</span>
                        <span class="font-mono text-ink font-semibold">{{ $connectionInfo['username'] }}</span>
                    </div>

                    <div class="rounded-2xl border border-line bg-slate-50/70 p-4">
                        <span class="text-[11px] font-semibold text-muted block mb-1">Senha</span>
                        <span class="font-mono text-ink font-semibold">••••••••••••</span>
                    </div>

                    <div class="rounded-2xl border border-line bg-slate-50/70 p-4">
                        <span class="text-[11px] font-semibold text-muted block mb-1">Modo SSL / Driver</span>
                        <span class="font-mono text-ink font-semibold">{{ $connectionInfo['sslmode'] }} ({{ $connectionInfo['driver'] }})</span>
                    </div>
                </div>
            </div>

            <!-- Resultado do Teste de Conectividade -->
            @if ($connectionSuccess !== null)
                <div class="rounded-3xl border p-6 sm:p-8 shadow-sm transition {{ $connectionSuccess ? 'border-emerald-200 bg-emerald-50/40' : 'border-rose-200 bg-rose-50/40' }}">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $connectionSuccess ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            @if ($connectionSuccess)
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @else
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-sm font-bold {{ $connectionSuccess ? 'text-emerald-900' : 'text-rose-900' }}">
                                    {{ $connectionSuccess ? 'Comunicação Estabelecida com Sucesso' : 'Falha na Conexão' }}
                                </h3>
                                @if ($latencyMs !== null)
                                    <span class="rounded-full bg-white px-2.5 py-0.5 text-[11px] font-semibold {{ $connectionSuccess ? 'text-emerald-800 border border-emerald-200' : 'text-rose-800 border border-rose-200' }}">
                                        Latência: {{ $latencyMs }} ms
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs {{ $connectionSuccess ? 'text-emerald-800' : 'text-rose-800' }} leading-relaxed">
                                {{ $testMessage }}
                            </p>

                            @if ($connectionSuccess && count($tablesStatus) > 0)
                                <div class="pt-3 border-t border-emerald-200/60">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900 block mb-2">Diagnóstico de Tabelas PEC</span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach ($tablesStatus as $table => $exists)
                                            <div class="flex items-center justify-between rounded-xl bg-white/80 px-3 py-2 text-xs border border-emerald-100">
                                                <span class="font-mono text-slate-700">{{ $table }}</span>
                                                @if ($exists)
                                                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                        Presente
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 font-semibold text-rose-600">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                        Não encontrada
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Coluna de Ajuda e Instruções -->
        <div class="space-y-5">
            <div class="rounded-3xl border border-line bg-gradient-to-br from-[#0c1f1c] to-[#081714] text-white p-6 shadow-md">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Segurança Read-Only</h3>
                        <p class="text-xs text-teal-300/80">Princípio do menor privilégio</p>
                    </div>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    O Monitora Fácil conecta-se à base de dados do e-SUS PEC exclusivamente através de consultas de leitura (SELECT). O usuário <span class="font-mono text-teal-200">esus_readonly</span> não possui permissão para INSERT, UPDATE ou DROP, garantindo integridade clínica incondicional.
                </p>
            </div>

            <div class="rounded-3xl border border-line bg-white p-6 shadow-sm text-xs space-y-3">
                <h4 class="font-bold text-ink uppercase tracking-wider text-[11px]">Como alterar credenciais</h4>
                <p class="text-muted leading-relaxed">
                    Para atualizar host, usuário ou senha do e-SUS, edite o arquivo <span class="font-mono text-slate-800">.env</span> na raiz da aplicação:
                </p>
                <div class="rounded-xl bg-slate-900 text-slate-200 p-3 font-mono text-[11px] leading-relaxed overflow-x-auto">
                    ESUS_DB_HOST=127.0.0.1<br>
                    ESUS_DB_PORT=5432<br>
                    ESUS_DB_DATABASE=esus<br>
                    ESUS_DB_USERNAME=esus_readonly<br>
                    ESUS_DB_PASSWORD=...
                </div>
            </div>
        </div>
    </div>
</div>
