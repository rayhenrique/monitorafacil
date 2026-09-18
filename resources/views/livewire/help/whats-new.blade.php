<div class="px-4 py-6 sm:px-8 max-w-7xl mx-auto space-y-6">
    <x-help-tabs
        title="Novidades da Versão"
        subtitle="Histórico oficial de atualizações, novas funcionalidades, melhorias e correções da plataforma"
        activeTab="whats-new"
    />

    <!-- Banner Principal de Versão -->
    <div class="rounded-3xl border border-line bg-gradient-to-br from-[#0c1f1c] via-[#0f2d26] to-[#081714] text-white p-6 sm:p-8 shadow-md">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-2 max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-500/25 px-3 py-1 text-[11px] font-semibold text-teal-300 border border-teal-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-400"></span>
                        Versão Ativa no Servidor: {{ $latestVersion }}
                    </span>
                    <span class="text-xs text-slate-400">Padrão Semântico SemVer · Lançamentos Contínuos</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                    Histórico de Atualizações do Monitora Fácil
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Acompanhe a evolução do sistema, novos módulos, conformidades técnicas com as portarias do Ministério da Saúde e aprimoramentos de usabilidade e segurança.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                <div class="rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10 px-5 py-3 text-center">
                    <p class="text-[11px] text-teal-300 uppercase font-semibold">Total de Lançamentos</p>
                    <p class="text-2xl font-black text-white mt-0.5">{{ $totalReleases }} versões</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros Rápidos -->
    <div class="flex items-center justify-between gap-4 flex-wrap border-b border-line pb-3">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <button
                type="button"
                wire:click="setFilter('all')"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $filter === 'all' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
            >
                <span>Todas as Versões</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ $filter === 'all' ? 'bg-teal-800 text-teal-200' : 'bg-slate-100 text-slate-600' }}">{{ $totalReleases }}</span>
            </button>

            <button
                type="button"
                wire:click="setFilter('novo')"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $filter === 'novo' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
            >
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>Novas Funcionalidades</span>
            </button>

            <button
                type="button"
                wire:click="setFilter('melhoria')"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $filter === 'melhoria' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
            >
                <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                <span>Melhorias</span>
            </button>

            <button
                type="button"
                wire:click="setFilter('correcao')"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $filter === 'correcao' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
            >
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                <span>Correções</span>
            </button>
        </div>

        <span class="text-xs text-muted">Documento gerador: <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono text-[11px]">versoes.md</code></span>
    </div>

    <!-- Linha do Tempo de Versões (Timeline) -->
    <div class="relative border-l-2 border-teal-200 ml-4 pl-6 sm:pl-8 space-y-8">
        @foreach ($releases as $index => $release)
            <div class="relative group">
                <!-- Marcador da Timeline -->
                <div class="absolute -left-[31px] sm:-left-[39px] top-1 flex h-6 w-6 items-center justify-center rounded-full bg-white ring-4 {{ $index === 0 ? 'ring-teal-500 bg-teal-600' : 'ring-slate-300 bg-slate-400' }}">
                    <div class="h-2 w-2 rounded-full {{ $index === 0 ? 'bg-white' : 'bg-white' }}"></div>
                </div>

                <!-- Card da Versão -->
                <div class="rounded-3xl border border-line bg-white p-6 sm:p-7 shadow-sm transition hover:shadow-md space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-line pb-4">
                        <div class="flex items-center gap-3">
                            <span class="rounded-xl px-3 py-1 text-sm font-black font-mono tracking-tight {{ $index === 0 ? 'bg-teal-700 text-white shadow-xs' : 'bg-slate-100 text-slate-800' }}">
                                {{ $release['version'] }}
                            </span>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $index === 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                {{ $release['badge'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-muted">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <span>Lançado em {{ $release['date'] }}</span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-ink">
                            {{ $release['title'] }}
                        </h3>
                        <p class="text-xs sm:text-sm text-muted mt-1 leading-relaxed">
                            {{ $release['summary'] }}
                        </p>
                    </div>

                    <!-- Lista de Modificações / Highlights -->
                    <div class="space-y-2 pt-1">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Principais Itens da Versão</p>
                        <div class="grid grid-cols-1 gap-2">
                            @foreach ($release['highlights'] as $highlight)
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 bg-slate-50/70 p-2.5 rounded-xl border border-slate-150/60">
                                    @if ($highlight['type'] === 'novo')
                                        <span class="rounded bg-emerald-100 text-emerald-800 px-1.5 py-0.5 text-[10px] font-bold shrink-0">Novo</span>
                                    @elseif ($highlight['type'] === 'melhoria')
                                        <span class="rounded bg-sky-100 text-sky-800 px-1.5 py-0.5 text-[10px] font-bold shrink-0">Melhoria</span>
                                    @elseif ($highlight['type'] === 'correcao')
                                        <span class="rounded bg-amber-100 text-amber-800 px-1.5 py-0.5 text-[10px] font-bold shrink-0">Correção</span>
                                    @elseif ($highlight['type'] === 'seguranca')
                                        <span class="rounded bg-purple-100 text-purple-800 px-1.5 py-0.5 text-[10px] font-bold shrink-0">Segurança</span>
                                    @else
                                        <span class="rounded bg-slate-100 text-slate-800 px-1.5 py-0.5 text-[10px] font-bold shrink-0">Geral</span>
                                    @endif
                                    <span class="leading-relaxed">{{ $highlight['text'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Card de Rodapé sobre Política de Versionamento -->
    <div class="rounded-3xl border border-line bg-white p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-800 border border-teal-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-ink">Política de Versionamento Semântico (SemVer)</h4>
                <p class="text-xs text-muted leading-relaxed">
                    O Monitora Fácil adota a convenção <code>MAJOR.MINOR.PATCH</code>. Atualizações que incluem novos módulos ou alterações nas portarias do Ministério da Saúde incrementam a versão secundária (ex: de v1.3.0 para v1.4.0), assegurando total rastreabilidade operacional e garantindo que os gestores municipais estejam sempre respaldados.
                </p>
            </div>
        </div>
    </div>
</div>
