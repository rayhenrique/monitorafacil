<!doctype html>
<html lang="pt-BR" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trim($settings['municipio_nome'] ?? '') ?: 'Município' }} · Monitora Fácil</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full bg-canvas font-sans text-ink antialiased">
    @php
        $isTerritorialNominal = request()->routeIs('territorial-bonding.nominal');
        $isTerritorialTeams = request()->routeIs('territorial-bonding.overview') && request()->query('aba', 'teams') === 'teams';
        $isTerritorialGuide = request()->routeIs('territorial-bonding.overview') && request()->query('aba') === 'guide';
    @endphp
    <div x-data="{
        mobileMenuOpen: false,
        sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed);
        }
    }" class="min-h-screen flex flex-col lg:flex-row">
        <!-- Barra superior para Mobile (< lg) -->
        <header class="sticky top-0 z-30 flex items-center justify-between gap-2 border-b border-[#1b3832] bg-[#0c1f1c] px-3 py-3 text-white sm:px-4 lg:hidden">
            <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">
                <button type="button" @click="mobileMenuOpen = true" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-300 hover:bg-[#132d27] hover:text-white focus:outline-none focus:ring-2 focus:ring-teal-400" aria-label="Abrir menu de navegação">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2.5" aria-label="Ir para o painel">
                    @if (filled($settings['logo_path'] ?? null))
                        <img src="{{ asset('storage/' . ltrim($settings['logo_path'], '/')) }}" alt="Logotipo de {{ trim($settings['municipio_nome'] ?? '') ?: 'município' }}" class="h-8 w-8 rounded-lg object-contain">
                    @else
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-teal-500 to-emerald-700 text-xs font-bold text-white shadow-sm" aria-hidden="true">MF</span>
                    @endif
                    <span class="min-w-0">
                        <span class="block truncate text-xs font-semibold text-white">{{ trim($settings['municipio_nome'] ?? '') ?: 'Município não configurado' }}</span>
                        <span class="block text-[10px] text-teal-300/80">Monitora Fácil</span>
                    </span>
                </a>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <span class="hidden text-xs text-slate-300 sm:inline">{{ auth()->user()->name }}</span>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sair do sistema" class="rounded-lg border border-[#1b3832] bg-[#132d27] p-2 text-slate-200 hover:border-rose-500/50 hover:bg-rose-500/10 hover:text-rose-300 focus:outline-none focus:ring-2 focus:ring-teal-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Drawer Lateral para Mobile (< lg) -->
        <div x-show="mobileMenuOpen" x-cloak class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition-opacity ease-linear duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                 @click="mobileMenuOpen = false"></div>

            <div class="fixed inset-0 flex">
                <div x-show="mobileMenuOpen"
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative flex w-full max-w-xs flex-1 flex-col bg-[#0c1f1c] text-slate-200 shadow-2xl border-r border-[#1a3832]">

                    <!-- Botão fechar -->
                    <div class="absolute right-2 top-3">
                        <button type="button" @click="mobileMenuOpen = false" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 hover:bg-[#132d27] hover:text-white focus:outline-none focus:ring-2 focus:ring-teal-400">
                            <span class="sr-only">Fechar menu</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Conteúdo do menu Mobile -->
                    <div class="flex h-full flex-col justify-between overflow-y-auto p-5">
                        <div class="space-y-6">
                            <!-- Cabeçalho / Município -->
                            <div class="flex items-center gap-3 pr-8">
                                @if (filled($settings['logo_path'] ?? null))
                                    <img src="{{ asset('storage/' . ltrim($settings['logo_path'], '/')) }}" alt="Logotipo de {{ trim($settings['municipio_nome'] ?? '') ?: 'município' }}" class="h-10 w-10 rounded-xl object-contain shadow">
                                @else
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-700 font-bold text-white shadow-md shadow-teal-950/40" aria-hidden="true">MF</span>
                                @endif
                                <div class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-white">{{ trim($settings['municipio_nome'] ?? '') ?: 'Município não configurado' }}</span>
                                    <span class="block text-xs text-teal-300/80">Monitora Fácil · Gestão APS</span>
                                </div>
                            </div>

                            <!-- Links de Navegação Mobile -->
                            <div>
                                <p class="px-2 text-[10px] font-bold uppercase tracking-widest text-teal-400/70">Navegação Principal</p>
                                <nav class="mt-2 space-y-1.5" aria-label="Navegação da aplicação">
                                    <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-teal-500/15 text-teal-300 border-l-2 border-teal-400 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white' }}">
                                        <svg class="h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                        </svg>
                                        <span>Visão Geral</span>
                                    </a>

                                    <!-- Vínculo e Acompanhamento Mobile -->
                                    <div x-data="{ open: {{ request()->routeIs('territorial-bonding.*') ? 'true' : 'false' }} }" class="space-y-1">
                                        <div class="flex items-center justify-between rounded-xl transition {{ request()->routeIs('territorial-bonding.*') ? 'bg-teal-500/15 text-teal-300 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                                            <a href="{{ route('territorial-bonding.nominal') }}" @click="mobileMenuOpen = false" class="flex-1 flex items-center gap-3 px-3 py-2.5 text-sm">
                                                <svg class="h-5 w-5 {{ request()->routeIs('territorial-bonding.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                                </svg>
                                                <span>Vínculo e Acompanhamento</span>
                                            </a>
                                            <button type="button" @click="open = !open" class="p-2.5 text-slate-400 hover:text-white transition" aria-label="Alternar menu vínculo e acompanhamento">
                                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180 text-teal-400': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div x-show="open" class="ml-4 pl-3 border-l border-[#1a3832] space-y-1 mt-1" style="{{ request()->routeIs('territorial-bonding.*') ? '' : 'display: none;' }}">
                                            <a href="{{ route('territorial-bonding.nominal') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ $isTerritorialNominal ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}" @if ($isTerritorialNominal) aria-current="page" @endif>
                                                <span>Relação Nominal</span>
                                                <span class="rounded bg-sky-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-sky-300">PEC</span>
                                            </a>
                                            <a href="{{ route('territorial-bonding.overview', ['aba' => 'teams']) }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ $isTerritorialTeams ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}" @if ($isTerritorialTeams) aria-current="page" @endif>
                                                <span>Equipes (Mensal)</span>
                                                <span class="text-[10px] text-teal-400 font-mono">Mensal</span>
                                            </a>
                                            <a href="{{ route('territorial-bonding.overview', ['aba' => 'guide']) }}" @click="mobileMenuOpen = false" class="block rounded-lg px-2.5 py-2 text-xs font-medium transition {{ $isTerritorialGuide ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}" @if ($isTerritorialGuide) aria-current="page" @endif>Caderno Metodológico</a>
                                        </div>
                                    </div>

                                    <!-- Saúde da Família Mobile -->
                                    <div x-data="{ open: {{ request()->routeIs('family-health.*') ? 'true' : 'false' }} }" class="space-y-1">
                                        <div class="flex items-center justify-between rounded-xl transition {{ request()->routeIs('family-health.*') ? 'bg-teal-500/15 text-teal-300 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                                            <a href="{{ route('family-health.overview') }}" @click="mobileMenuOpen = false" class="flex-1 flex items-center gap-3 px-3 py-2.5 text-sm">
                                                <svg class="h-5 w-5 {{ request()->routeIs('family-health.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                                </svg>
                                                <span>Saúde da Família</span>
                                            </a>
                                            <button type="button" @click="open = !open" class="p-2.5 text-slate-400 hover:text-white transition" aria-label="Alternar menu saúde da família">
                                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180 text-teal-400': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div x-show="open" class="ml-4 pl-3 border-l border-[#1a3832] space-y-1 mt-1" style="{{ request()->routeIs('family-health.*') ? '' : 'display: none;' }}">
                                            <a href="{{ route('family-health.overview') }}" @click="mobileMenuOpen = false" class="block rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('family-health.overview') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">Painel Municipal</a>
                                            <a href="{{ route('family-health.indicator', 'c1') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c1' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>C1 · Acesso APS</span>
                                                <span class="text-[10px] text-teal-400 font-mono">Prop.</span>
                                            </a>
                                            <a href="{{ route('family-health.indicator', 'c2') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c2' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>C2 · Desenv. Infantil</span>
                                                <span class="text-[10px] text-teal-400 font-mono">5 BP</span>
                                            </a>
                                            <a href="{{ route('family-health.indicator', 'c3') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c3' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>C3 · Pré-natal e Puerp.</span>
                                                <span class="text-[10px] text-teal-400 font-mono">11 BP</span>
                                            </a>
                                            <a href="{{ route('family-health.indicator', 'c4') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c4' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>C4 · Diabetes</span>
                                                <span class="text-[10px] text-teal-400 font-mono">6 BP</span>
                                            </a>
                                            <a href="{{ route('family-health.indicator', 'c5') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c5' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>C5 · Hipertensão</span>
                                                <span class="text-[10px] text-teal-400 font-mono">4 BP</span>
                                            </a>
                                            <a href="{{ route('family-health.indicator', 'c6') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c6' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>C6 · Pessoa Idosa</span>
                                                <span class="text-[10px] text-teal-400 font-mono">4 BP</span>
                                            </a>
                                            <a href="{{ route('family-health.indicator', 'c7') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c7' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>C7 · Câncer Mulher</span>
                                                <span class="text-[10px] text-teal-400 font-mono">Pond.</span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Saúde Bucal Mobile -->
                                    <div x-data="{ open: {{ request()->routeIs('oral-health.*') ? 'true' : 'false' }} }" class="space-y-1">
                                        <div class="flex items-center justify-between rounded-xl transition {{ request()->routeIs('oral-health.*') ? 'bg-teal-500/15 text-teal-300 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                                            <a href="{{ route('oral-health.overview') }}" @click="mobileMenuOpen = false" class="flex-1 flex items-center gap-3 px-3 py-2.5 text-sm">
                                                <svg class="h-5 w-5 {{ request()->routeIs('oral-health.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 4c-2.5 0-4 2-4 5.5 0 3.5 1.5 6 3 9.5 1 2.3 2 4.5 3 4.5s1-1.5 2-4c.5-1.3 1-2.5 1-2.5s.5 1.2 1 2.5c1 2.5 1 4 2 4s2-2.2 3-4.5c1.5-3.5 3-6 3-9.5C21 6 19.5 4 17 4c-2 0-3.5 1.5-5 1.5S9 4 7 4z" />
                                                </svg>
                                                <span>Saúde Bucal</span>
                                            </a>
                                            <button type="button" @click="open = !open" class="p-2.5 text-slate-400 hover:text-white transition" aria-label="Alternar menu saúde bucal">
                                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180 text-teal-400': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div x-show="open" class="ml-4 pl-3 border-l border-[#1a3832] space-y-1 mt-1" style="{{ request()->routeIs('oral-health.*') ? '' : 'display: none;' }}">
                                            <a href="{{ route('oral-health.overview') }}" @click="mobileMenuOpen = false" class="block rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('oral-health.overview') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">Painel Geral</a>
                                            <a href="{{ route('oral-health.nominal') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('oral-health.nominal') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>Busca Geral Nominal</span>
                                                <span class="rounded bg-teal-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-teal-300 font-mono">PEC</span>
                                            </a>
                                            <a href="{{ route('oral-health.monthly') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('oral-health.monthly') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>Dashboard Mensal</span>
                                                <span class="rounded bg-sky-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-sky-300 font-mono">Mensal</span>
                                            </a>
                                            <a href="{{ route('oral-health.indicator', 'b1') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b1' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>B1 · Primeira Consulta</span>
                                                <span class="text-[10px] text-teal-400 font-mono">1x/ano</span>
                                            </a>
                                            <a href="{{ route('oral-health.indicator', 'b2') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b2' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>B2 · Trat. Concluído</span>
                                                <span class="text-[10px] text-teal-400 font-mono">12m</span>
                                            </a>
                                            <a href="{{ route('oral-health.indicator', 'b3') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b3' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>B3 · Taxa Exodontia</span>
                                                <span class="text-[10px] text-teal-400 font-mono">Menor</span>
                                            </a>
                                            <a href="{{ route('oral-health.indicator', 'b4') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b4' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>B4 · Escovação Superv.</span>
                                                <span class="text-[10px] text-teal-400 font-mono">6-12a</span>
                                            </a>
                                            <a href="{{ route('oral-health.indicator', 'b5') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b5' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>B5 · Proced. Preventivos</span>
                                                <span class="text-[10px] text-teal-400 font-mono">Prev.</span>
                                            </a>
                                            <a href="{{ route('oral-health.indicator', 'b6') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b6' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>B6 · Restauração ART</span>
                                                <span class="text-[10px] text-teal-400 font-mono">TRA</span>
                                            </a>
                                        </div>
                                    </div>

                                    <div x-data="{ open: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }" class="space-y-1">
                                        <div class="flex items-center justify-between rounded-xl transition {{ request()->routeIs('settings.*') ? 'bg-teal-500/15 text-teal-300 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                                            <a href="{{ route('settings.users') }}" @click="mobileMenuOpen = false" class="flex-1 flex items-center gap-3 px-3 py-2.5 text-sm">
                                                <svg class="h-5 w-5 {{ request()->routeIs('settings.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.241.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span>Configurações</span>
                                            </a>
                                            <button type="button" @click="open = !open" class="p-2.5 text-slate-400 hover:text-white transition" aria-label="Alternar menu configurações">
                                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180 text-teal-400': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div x-show="open" class="ml-4 pl-3 border-l border-[#1a3832] space-y-1 mt-1" style="{{ request()->routeIs('settings.*') ? '' : 'display: none;' }}">
                                            <a href="{{ route('settings.users') }}" @click="mobileMenuOpen = false" class="block rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('settings.users') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">Usuários</a>
                                            <a href="{{ route('settings.municipality') }}" @click="mobileMenuOpen = false" class="block rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('settings.municipality') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">Município</a>
                                            <a href="{{ route('settings.audit-logs') }}" @click="mobileMenuOpen = false" class="block rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('settings.audit-logs') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">Log de Auditoria</a>
                                            <a href="{{ route('settings.esus-connection') }}" @click="mobileMenuOpen = false" class="block rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('settings.esus-connection') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">Conexão e-SUS</a>
                                            <a href="{{ route('settings.data-processing') }}" @click="mobileMenuOpen = false" class="block rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('settings.data-processing') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">Processar Dados</a>
                                            <a href="{{ route('settings.cnes-import') }}" @click="mobileMenuOpen = false" class="block rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('settings.cnes-import') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">Importar CNES / XML</a>
                                        </div>
                                    </div>

                                    <!-- Ajuda com Sub-itens Mobile -->
                                    <div x-data="{ open: {{ request()->routeIs('help.*') ? 'true' : 'false' }} }" class="space-y-1">
                                        <div class="flex items-center justify-between rounded-xl transition {{ request()->routeIs('help.*') ? 'bg-teal-500/15 text-teal-300 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                                            <a href="{{ route('help.guide') }}" @click="mobileMenuOpen = false" class="flex-1 flex items-center gap-3 px-3 py-2.5 text-sm">
                                                <svg class="h-5 w-5 {{ request()->routeIs('help.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                                </svg>
                                                <span>Ajuda</span>
                                            </a>
                                            <button type="button" @click="open = !open" class="p-2.5 text-slate-400 hover:text-white transition" aria-label="Alternar menu ajuda">
                                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180 text-teal-400': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div x-show="open" class="ml-4 pl-3 border-l border-[#1a3832] space-y-1 mt-1" style="{{ request()->routeIs('help.*') ? '' : 'display: none;' }}">
                                            <a href="{{ route('help.guide') }}" @click="mobileMenuOpen = false" class="block rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('help.guide') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">Guia de Preenchimento</a>
                                            <a href="{{ route('help.whats-new') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-medium transition {{ request()->routeIs('help.whats-new') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                                <span>Novidades da Versão</span>
                                                <span class="rounded bg-teal-800 text-teal-200 px-1.5 py-0.5 text-[10px] font-bold">{{ \App\Services\VersionService::CURRENT_VERSION }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </nav>
                            </div>
                        </div>

                        <!-- Rodapé do menu Mobile com Usuário -->
                        <div class="mt-8 border-t border-[#1a3832] pt-4">
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-[#081714] p-3 border border-[#1a3832]/60">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-800 text-xs font-bold text-teal-200">
                                        {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-medium text-white">{{ auth()->user()->name }}</p>
                                        <p class="truncate text-[11px] text-slate-400">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>
                                <form method="post" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" title="Sair" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-500/20 hover:text-rose-300 transition">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Fixo no Desktop (>= lg) -->
        <aside :class="sidebarCollapsed ? 'lg:w-20' : 'lg:w-72'"
               class="hidden lg:flex lg:shrink-0 lg:sticky lg:top-0 lg:h-screen lg:flex-col lg:justify-between bg-[#0c1f1c] text-slate-200 border-r border-[#1a3832] z-30 transition-all duration-300 ease-in-out">
            <div class="flex flex-col gap-5 overflow-y-auto" :class="sidebarCollapsed ? 'p-3' : 'p-5'">
                <!-- Cabeçalho do Município / Logo + Botão de Recolher/Expandir -->
                <div class="flex items-center gap-2" :class="sidebarCollapsed ? 'flex-col justify-center' : 'justify-between'">
                    <a href="{{ route('dashboard') }}" class="group flex items-center gap-3.5 rounded-2xl p-1.5 transition hover:bg-[#132d27]/70 focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal-400 min-w-0" aria-label="Ir para o painel" :title="sidebarCollapsed ? '{{ trim($settings['municipio_nome'] ?? '') ?: 'Monitora Fácil' }}' : ''">
                        @if (filled($settings['logo_path'] ?? null))
                            <img src="{{ asset('storage/' . ltrim($settings['logo_path'], '/')) }}" alt="Logotipo de {{ trim($settings['municipio_nome'] ?? '') ?: 'município' }}" class="h-10 w-10 shrink-0 rounded-xl object-contain shadow-md">
                        @else
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-700 font-bold text-white shadow-lg shadow-teal-950/50 ring-1 ring-white/10" aria-hidden="true">MF</span>
                        @endif
                        <div class="min-w-0" x-show="!sidebarCollapsed" x-transition>
                            <span class="block truncate text-sm font-semibold text-white group-hover:text-teal-200 transition">{{ trim($settings['municipio_nome'] ?? '') ?: 'Município não configurado' }}</span>
                            <span class="block text-xs text-teal-300/80">Monitora Fácil · Gestão APS</span>
                        </div>
                    </a>

                    <!-- Toggle Sidebar Button (Recolher/Expandir) -->
                    <button type="button"
                            @click="toggleSidebar()"
                            id="sidebar-toggle-btn"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-[#1b3a33] bg-[#102722] text-slate-300 hover:border-teal-500/50 hover:bg-[#163830] hover:text-white transition focus:outline-none focus:ring-2 focus:ring-teal-400 shadow-sm"
                            :title="sidebarCollapsed ? 'Expandir menu lateral' : 'Recolher menu lateral'"
                            :aria-label="sidebarCollapsed ? 'Expandir menu lateral' : 'Recolher menu lateral'">
                        <svg class="h-4 w-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <rect width="18" height="18" x="3" y="3" rx="2" stroke="currentColor"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v18" />
                            <path stroke-linecap="round" stroke-linejoin="round" :d="sidebarCollapsed ? 'M13 9l3 3-3 3' : 'M15 9l-3 3 3 3'" />
                        </svg>
                    </button>
                </div>

                <!-- Indicador de Status do Sistema (oculto quando recolhido) -->
                <div x-show="!sidebarCollapsed" x-transition class="rounded-xl border border-[#1b3a33] bg-[#081714]/80 p-3 shadow-inner">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-medium text-emerald-300">Base e-SUS PEC</span>
                        </div>
                        <span class="rounded bg-[#122e28] px-2 py-0.5 text-[10px] font-semibold text-teal-300">Ativo</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400 border-t border-[#132d27] pt-2">
                        <span>Fuso</span>
                        <span class="font-mono text-slate-300">{{ config('esus.schedule_timezone', 'America/Maceio') }}</span>
                    </div>
                </div>

                <!-- Seção de Navegação -->
                <div>
                    <p x-show="!sidebarCollapsed" x-transition class="px-3 text-[11px] font-bold uppercase tracking-widest text-teal-400/70">Navegação Principal</p>
                    <nav class="mt-2.5 space-y-1.5" aria-label="Navegação da aplicação">
                        <!-- Visão Geral -->
                        <a href="{{ route('dashboard') }}"
                           :class="sidebarCollapsed ? 'justify-center !px-2' : 'px-3.5'"
                           :title="sidebarCollapsed ? 'Visão Geral' : ''"
                           class="group flex items-center gap-3 rounded-xl py-2.5 text-sm transition {{ request()->routeIs('dashboard') ? 'bg-teal-500/15 text-teal-300 border-l-4 border-teal-400 shadow-sm shadow-teal-950/30 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                            <svg class="h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-300' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="truncate">Visão Geral</span>
                        </a>

                        <!-- Vínculo e Acompanhamento (Componente II - CVAT) -->
                        <div x-data="{ open: {{ request()->routeIs('territorial-bonding.*') ? 'true' : 'false' }} }" class="space-y-1">
                            <div class="flex items-center justify-between rounded-xl transition {{ request()->routeIs('territorial-bonding.*') ? 'bg-teal-500/15 text-teal-300 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                                <a href="{{ route('territorial-bonding.nominal') }}"
                                   :class="sidebarCollapsed ? 'justify-center !px-2' : 'px-3.5'"
                                   :title="sidebarCollapsed ? 'Vínculo e Acompanhamento' : ''"
                                   class="flex-1 flex items-center gap-3 py-2.5 text-sm">
                                    <svg class="h-5 w-5 {{ request()->routeIs('territorial-bonding.*') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-300' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    <span x-show="!sidebarCollapsed" class="truncate">Vínculo e Acompanhamento</span>
                                </a>
                                <button type="button" x-show="!sidebarCollapsed" @click="open = !open" class="p-2.5 text-slate-400 hover:text-white transition" aria-label="Alternar menu vínculo e acompanhamento">
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180 text-teal-400': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </div>
                            <div x-show="open && !sidebarCollapsed" class="ml-4 pl-3 border-l border-[#1a3832] space-y-1 mt-1" style="{{ request()->routeIs('territorial-bonding.*') ? '' : 'display: none;' }}">
                                <a href="{{ route('territorial-bonding.nominal') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ $isTerritorialNominal ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}" @if ($isTerritorialNominal) aria-current="page" @endif>
                                    <span>Relação Nominal</span>
                                    <span class="rounded bg-sky-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-sky-300">PEC</span>
                                </a>
                                <a href="{{ route('territorial-bonding.overview', ['aba' => 'teams']) }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ $isTerritorialTeams ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}" @if ($isTerritorialTeams) aria-current="page" @endif>
                                    <span>Equipes (Mensal)</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">Mensal</span>
                                </a>
                                <a href="{{ route('territorial-bonding.overview', ['aba' => 'guide']) }}" class="block rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ $isTerritorialGuide ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}" @if ($isTerritorialGuide) aria-current="page" @endif>
                                    Caderno Metodológico
                                </a>
                            </div>
                        </div>

                        <!-- Saúde da Família (C1 ao C7) -->
                        <div x-data="{ open: {{ request()->routeIs('family-health.*') ? 'true' : 'false' }} }" class="space-y-1">
                            <div class="flex items-center justify-between rounded-xl transition {{ request()->routeIs('family-health.*') ? 'bg-teal-500/15 text-teal-300 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                                <a href="{{ route('family-health.overview') }}"
                                   :class="sidebarCollapsed ? 'justify-center !px-2' : 'px-3.5'"
                                   :title="sidebarCollapsed ? 'Saúde da Família' : ''"
                                   class="flex-1 flex items-center gap-3 py-2.5 text-sm">
                                    <svg class="h-5 w-5 {{ request()->routeIs('family-health.*') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-300' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                    </svg>
                                    <span x-show="!sidebarCollapsed" class="truncate">Saúde da Família</span>
                                </a>
                                <button type="button" x-show="!sidebarCollapsed" @click="open = !open" class="p-2.5 text-slate-400 hover:text-white transition" aria-label="Alternar menu saúde da família">
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180 text-teal-400': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </div>
                            <div x-show="open && !sidebarCollapsed" class="ml-4 pl-3 border-l border-[#1a3832] space-y-1 mt-1" style="{{ request()->routeIs('family-health.*') ? '' : 'display: none;' }}">
                                <a href="{{ route('family-health.overview') }}" class="block rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('family-health.overview') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    Painel Municipal
                                </a>
                                <a href="{{ route('family-health.indicator', 'c1') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c1' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>C1 · Acesso APS</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">Prop.</span>
                                </a>
                                <a href="{{ route('family-health.indicator', 'c2') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c2' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>C2 · Desenv. Infantil</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">5 BP</span>
                                </a>
                                <a href="{{ route('family-health.indicator', 'c3') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c3' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>C3 · Pré-natal e Puerp.</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">11 BP</span>
                                </a>
                                <a href="{{ route('family-health.indicator', 'c4') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c4' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>C4 · Diabetes</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">6 BP</span>
                                </a>
                                <a href="{{ route('family-health.indicator', 'c5') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c5' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>C5 · Hipertensão</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">4 BP</span>
                                </a>
                                <a href="{{ route('family-health.indicator', 'c6') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c6' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>C6 · Pessoa Idosa</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">4 BP</span>
                                </a>
                                <a href="{{ route('family-health.indicator', 'c7') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('family-health.indicator') && request()->route('indicator') === 'c7' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>C7 · Câncer Mulher</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">Pond.</span>
                                </a>
                            </div>
                        </div>

                        <!-- Saúde Bucal (B1 ao B6) -->
                        <div x-data="{ open: {{ request()->routeIs('oral-health.*') ? 'true' : 'false' }} }" class="space-y-1">
                            <div class="flex items-center justify-between rounded-xl transition {{ request()->routeIs('oral-health.*') ? 'bg-teal-500/15 text-teal-300 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                                <a href="{{ route('oral-health.overview') }}"
                                   :class="sidebarCollapsed ? 'justify-center !px-2' : 'px-3.5'"
                                   :title="sidebarCollapsed ? 'Saúde Bucal' : ''"
                                   class="flex-1 flex items-center gap-3 py-2.5 text-sm">
                                    <svg class="h-5 w-5 {{ request()->routeIs('oral-health.*') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-300' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 4c-2.5 0-4 2-4 5.5 0 3.5 1.5 6 3 9.5 1 2.3 2 4.5 3 4.5s1-1.5 2-4c.5-1.3 1-2.5 1-2.5s.5 1.2 1 2.5c1 2.5 1 4 2 4s2-2.2 3-4.5c1.5-3.5 3-6 3-9.5C21 6 19.5 4 17 4c-2 0-3.5 1.5-5 1.5S9 4 7 4z" />
                                    </svg>
                                    <span x-show="!sidebarCollapsed" class="truncate">Saúde Bucal</span>
                                </a>
                                <button type="button" x-show="!sidebarCollapsed" @click="open = !open" class="p-2.5 text-slate-400 hover:text-white transition" aria-label="Alternar menu saúde bucal">
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180 text-teal-400': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </div>
                            <div x-show="open && !sidebarCollapsed" class="ml-4 pl-3 border-l border-[#1a3832] space-y-1 mt-1" style="{{ request()->routeIs('oral-health.*') ? '' : 'display: none;' }}">
                                <a href="{{ route('oral-health.overview') }}" class="block rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('oral-health.overview') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    Painel Geral
                                </a>
                                <a href="{{ route('oral-health.nominal') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('oral-health.nominal') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>Busca Geral Nominal</span>
                                    <span class="rounded bg-teal-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-teal-300 font-mono">PEC</span>
                                </a>
                                <a href="{{ route('oral-health.monthly') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('oral-health.monthly') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>Dashboard Mensal</span>
                                    <span class="rounded bg-sky-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-sky-300 font-mono">Mensal</span>
                                </a>
                                <a href="{{ route('oral-health.indicator', 'b1') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b1' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>B1 · Primeira Consulta</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">1x/ano</span>
                                </a>
                                <a href="{{ route('oral-health.indicator', 'b2') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b2' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>B2 · Trat. Concluído</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">12m</span>
                                </a>
                                <a href="{{ route('oral-health.indicator', 'b3') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b3' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>B3 · Taxa Exodontia</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">Menor</span>
                                </a>
                                <a href="{{ route('oral-health.indicator', 'b4') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b4' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>B4 · Escovação Superv.</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">6-12a</span>
                                </a>
                                <a href="{{ route('oral-health.indicator', 'b5') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b5' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>B5 · Proced. Preventivos</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">Prev.</span>
                                </a>
                                <a href="{{ route('oral-health.indicator', 'b6') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('oral-health.indicator') && request()->route('indicator') === 'b6' ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>B6 · Restauração ART</span>
                                    <span class="text-[10px] font-mono text-teal-400/80">TRA</span>
                                </a>
                            </div>
                        </div>

                        <!-- Configurações com Sub-itens -->
                        <div x-data="{ open: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }" class="space-y-1">
                            <div class="flex items-center justify-between rounded-xl transition {{ request()->routeIs('settings.*') ? 'bg-teal-500/15 text-teal-300 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                                <a href="{{ route('settings.users') }}"
                                   :class="sidebarCollapsed ? 'justify-center !px-2' : 'px-3.5'"
                                   :title="sidebarCollapsed ? 'Configurações' : ''"
                                   class="flex-1 flex items-center gap-3 py-2.5 text-sm">
                                    <svg class="h-5 w-5 {{ request()->routeIs('settings.*') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-300' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.241.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span x-show="!sidebarCollapsed" class="truncate">Configurações</span>
                                </a>
                                <button type="button" x-show="!sidebarCollapsed" @click="open = !open" class="p-2.5 text-slate-400 hover:text-white transition" aria-label="Alternar menu configurações">
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180 text-teal-400': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </div>
                            <div x-show="open && !sidebarCollapsed" class="ml-4 pl-3 border-l border-[#1a3832] space-y-1 mt-1" style="{{ request()->routeIs('settings.*') ? '' : 'display: none;' }}">
                                <a href="{{ route('settings.users') }}" class="block rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('settings.users') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    Usuários
                                </a>
                                <a href="{{ route('settings.municipality') }}" class="block rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('settings.municipality') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    Município
                                </a>
                                <a href="{{ route('settings.audit-logs') }}" class="block rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('settings.audit-logs') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    Log de Auditoria
                                </a>
                                <a href="{{ route('settings.esus-connection') }}" class="block rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('settings.esus-connection') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    Conexão e-SUS
                                </a>
                                <a href="{{ route('settings.data-processing') }}" class="block rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('settings.data-processing') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    Processar Dados
                                </a>
                                <a href="{{ route('settings.cnes-import') }}" class="block rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('settings.cnes-import') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    Importar CNES / XML
                                </a>
                            </div>
                        </div>

                        <!-- Ajuda com Sub-itens -->
                        <div x-data="{ open: {{ request()->routeIs('help.*') ? 'true' : 'false' }} }" class="space-y-1">
                            <div class="flex items-center justify-between rounded-xl transition {{ request()->routeIs('help.*') ? 'bg-teal-500/15 text-teal-300 font-semibold' : 'text-slate-300 hover:bg-[#132d27] hover:text-white font-medium' }}">
                                <a href="{{ route('help.guide') }}"
                                   :class="sidebarCollapsed ? 'justify-center !px-2' : 'px-3.5'"
                                   :title="sidebarCollapsed ? 'Ajuda' : ''"
                                   class="flex-1 flex items-center gap-3 py-2.5 text-sm">
                                    <svg class="h-5 w-5 {{ request()->routeIs('help.*') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-300' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                    </svg>
                                    <span x-show="!sidebarCollapsed" class="truncate">Ajuda</span>
                                </a>
                                <button type="button" x-show="!sidebarCollapsed" @click="open = !open" class="p-2.5 text-slate-400 hover:text-white transition" aria-label="Alternar menu ajuda">
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180 text-teal-400': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </div>
                            <div x-show="open && !sidebarCollapsed" class="ml-4 pl-3 border-l border-[#1a3832] space-y-1 mt-1" style="{{ request()->routeIs('help.*') ? '' : 'display: none;' }}">
                                <a href="{{ route('help.guide') }}" class="block rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('help.guide') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    Guia de Preenchimento
                                </a>
                                <a href="{{ route('help.whats-new') }}" class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('help.whats-new') ? 'bg-teal-500/20 text-teal-300 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#132d27]' }}">
                                    <span>Novidades da Versão</span>
                                    <span class="rounded bg-teal-800 text-teal-200 px-1.5 py-0.5 text-[10px] font-bold">{{ \App\Services\VersionService::CURRENT_VERSION }}</span>
                                </a>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>

            <!-- Rodapé da Sidebar: Usuário Autenticado, Logout e Versão -->
            <div class="border-t border-[#1a3832] bg-[#081714]" :class="sidebarCollapsed ? 'p-2.5' : 'p-4'">
                <div class="flex items-center gap-2" :class="sidebarCollapsed ? 'flex-col justify-center' : 'justify-between'">
                    <div class="flex items-center gap-3 min-w-0" :class="sidebarCollapsed ? 'justify-center' : ''">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-800/90 text-sm font-bold text-teal-200 ring-1 ring-teal-500/30 shadow-inner" :title="auth()->user()->name">
                            {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                        </span>
                        <div class="min-w-0" x-show="!sidebarCollapsed" x-transition>
                            <p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-[11px] text-slate-400">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Sair do sistema" class="group flex h-9 w-9 items-center justify-center rounded-xl border border-[#1b3a33] bg-[#102722] text-slate-300 transition hover:border-rose-500/50 hover:bg-rose-500/10 hover:text-rose-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal-400">
                            <span class="sr-only">Sair</span>
                            <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
                <div class="mt-3 pt-2.5 border-t border-[#132d27] flex items-center justify-between text-[11px] text-slate-400" x-show="!sidebarCollapsed" x-transition>
                    <span class="text-slate-400">Monitora Fácil</span>
                    <a href="{{ route('help.whats-new') }}" class="inline-flex items-center gap-1 rounded bg-teal-900/80 hover:bg-teal-800 text-teal-300 px-1.5 py-0.5 font-mono text-[10px] font-bold transition border border-teal-700/50" title="Ver notas da versão e histórico">
                        <span>{{ \App\Services\VersionService::CURRENT_VERSION }}</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Área Principal de Conteúdo -->
        <div class="flex min-h-screen min-w-0 flex-1 flex-col overflow-x-clip bg-canvas">
            <!-- Barra Superior Discreta de Contexto no Desktop -->
            <div class="hidden lg:flex items-center justify-between border-b border-line bg-white/75 px-6 py-3 backdrop-blur-sm shadow-[0_1px_3px_0_rgba(0,0,0,0.02)]">
                <div class="flex items-center gap-3 text-xs text-muted">
                    <button type="button"
                            @click="toggleSidebar()"
                            id="topbar-sidebar-toggle-btn"
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-teal-500/50 hover:bg-slate-50 hover:text-teal-700 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-400"
                            :title="sidebarCollapsed ? 'Expandir menu lateral' : 'Recolher menu lateral'"
                            :aria-label="sidebarCollapsed ? 'Expandir menu lateral' : 'Recolher menu lateral'">
                        <svg class="h-4 w-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <rect width="18" height="18" x="3" y="3" rx="2" stroke="currentColor"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v18" />
                            <path stroke-linecap="round" stroke-linejoin="round" :d="sidebarCollapsed ? 'M13 9l3 3-3 3' : 'M15 9l-3 3 3 3'" />
                        </svg>
                    </button>
                    <span class="font-medium text-teal-800">Monitora Fácil</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-ink font-medium">Gestão da Atenção Primária à Saúde</span>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-2.5 py-1 font-medium text-teal-900 border border-teal-200/60">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
                        Monitoramento Ativo
                    </span>
                    <span class="text-muted tabular-nums">{{ now()->format('d/m/Y') }}</span>
                </div>
            </div>

            <main id="conteudo" class="min-w-0 flex-1">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>

            <footer class="flex flex-col items-center justify-between gap-1.5 border-t border-line bg-white/50 px-4 py-4 text-center text-xs text-muted sm:flex-row sm:px-8 sm:text-left">
                <span>Dados consolidados para apoio à gestão municipal da APS.</span>
                <span class="flex items-center gap-1">
                    <span>Monitora Fácil · {{ trim($settings['municipio_nome'] ?? '') ?: 'Gestão Municipal' }} ·</span>
                    <a href="{{ route('help.whats-new') }}" class="font-mono hover:text-teal-700 transition font-medium" title="Novidades da versão">
                        {{ \App\Services\VersionService::CURRENT_VERSION }}
                    </a>
                </span>
            </footer>
        </div>
    </div>
    @livewire('common.whats-new-modal')
    @livewireScripts
</body>
</html>
