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
    <div x-data="{ mobileMenuOpen: false }" class="min-h-screen flex flex-col lg:flex-row">
        <!-- Barra superior para Mobile (< lg) -->
        <header class="sticky top-0 z-30 flex items-center justify-between border-b border-[#1b3832] bg-[#0c1f1c] px-4 py-3 text-white lg:hidden">
            <div class="flex items-center gap-3">
                <button type="button" @click="mobileMenuOpen = true" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-300 hover:bg-[#132d27] hover:text-white focus:outline-none focus:ring-2 focus:ring-teal-400" aria-label="Abrir menu de navegação">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5" aria-label="Ir para o painel">
                    @if (filled($settings['logo_path'] ?? null))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['logo_path']) }}" alt="Logotipo de {{ trim($settings['municipio_nome'] ?? '') ?: 'município' }}" class="h-8 w-8 rounded-lg object-contain">
                    @else
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-teal-500 to-emerald-700 text-xs font-bold text-white shadow-sm" aria-hidden="true">MF</span>
                    @endif
                    <span class="min-w-0">
                        <span class="block truncate text-xs font-semibold text-white">{{ trim($settings['municipio_nome'] ?? '') ?: 'Município não configurado' }}</span>
                        <span class="block text-[10px] text-teal-300/80">Monitora Fácil</span>
                    </span>
                </a>
            </div>

            <div class="flex items-center gap-2">
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
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['logo_path']) }}" alt="Logotipo de {{ trim($settings['municipio_nome'] ?? '') ?: 'município' }}" class="h-10 w-10 rounded-xl object-contain shadow">
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
                                <nav class="mt-2 space-y-1">
                                    <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 rounded-xl bg-teal-500/15 px-3 py-2.5 text-sm font-medium text-teal-300 border-l-2 border-teal-400">
                                        <svg class="h-5 w-5 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                        </svg>
                                        <span>Visão Geral</span>
                                    </a>
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
        <aside class="hidden lg:flex lg:w-72 lg:shrink-0 lg:sticky lg:top-0 lg:h-screen lg:flex-col lg:justify-between bg-[#0c1f1c] text-slate-200 border-r border-[#1a3832] z-30">
            <div class="flex flex-col gap-6 p-5 overflow-y-auto">
                <!-- Cabeçalho do Município / Logo -->
                <a href="{{ route('dashboard') }}" class="group flex items-center gap-3.5 rounded-2xl p-2 transition hover:bg-[#132d27]/70 focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal-400" aria-label="Ir para o painel">
                    @if (filled($settings['logo_path'] ?? null))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['logo_path']) }}" alt="Logotipo de {{ trim($settings['municipio_nome'] ?? '') ?: 'município' }}" class="h-11 w-11 rounded-xl object-contain shadow-md">
                    @else
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-700 font-bold text-white shadow-lg shadow-teal-950/50 ring-1 ring-white/10" aria-hidden="true">MF</span>
                    @endif
                    <div class="min-w-0">
                        <span class="block truncate text-sm font-semibold text-white group-hover:text-teal-200 transition">{{ trim($settings['municipio_nome'] ?? '') ?: 'Município não configurado' }}</span>
                        <span class="block text-xs text-teal-300/80">Monitora Fácil · Gestão APS</span>
                    </div>
                </a>

                <!-- Indicador de Status do Sistema -->
                <div class="rounded-xl border border-[#1b3a33] bg-[#081714]/80 p-3 shadow-inner">
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
                    <p class="px-3 text-[11px] font-bold uppercase tracking-widest text-teal-400/70">Navegação Principal</p>
                    <nav class="mt-2.5 space-y-1.5" aria-label="Navegação da aplicação">
                        <!-- Visão Geral -->
                        <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 rounded-xl bg-teal-500/15 px-3.5 py-2.5 text-sm font-semibold text-teal-300 border-l-4 border-teal-400 shadow-sm shadow-teal-950/30 transition hover:bg-teal-500/20">
                            <svg class="h-5 w-5 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span>Visão Geral</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Rodapé da Sidebar: Usuário Autenticado e Logout -->
            <div class="border-t border-[#1a3832] p-4 bg-[#081714]">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-800/90 text-sm font-bold text-teal-200 ring-1 ring-teal-500/30 shadow-inner">
                            {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                        </span>
                        <div class="min-w-0">
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
            </div>
        </aside>

        <!-- Área Principal de Conteúdo -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-canvas">
            <!-- Barra Superior Discreta de Contexto no Desktop -->
            <div class="hidden lg:flex items-center justify-between border-b border-line bg-white/75 px-8 py-3.5 backdrop-blur-sm shadow-[0_1px_3px_0_rgba(0,0,0,0.02)]">
                <div class="flex items-center gap-2.5 text-xs text-muted">
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

            <main id="conteudo" class="flex-1">
                @yield('content')
            </main>

            <footer class="border-t border-line bg-white/50 px-6 py-4 text-center text-xs text-muted sm:px-8 flex flex-wrap items-center justify-between gap-2">
                <span>Dados consolidados para apoio à gestão municipal da APS.</span>
                <span>Monitora Fácil · {{ trim($settings['municipio_nome'] ?? '') ?: 'Gestão Municipal' }}</span>
            </footer>
        </div>
    </div>
    @livewireScripts
</body>
</html>
