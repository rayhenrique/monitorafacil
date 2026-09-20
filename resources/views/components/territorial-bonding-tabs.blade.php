@props([
    'title' => 'Vínculo e Acompanhamento Territorial',
    'subtitle' => 'Componente II · Metodologia Oficial Siaps / Portaria GM/MS nº 3.493/2024',
    'activeTab' => 'nominal', // 'nominal', 'cadastro', 'acompanhamento', 'teams', 'guide'
    'teamCount' => null,
])

@php
    $tabs = [
        'nominal' => [
            'label' => 'Relação Nominal',
            'icon' => 'list',
            'badge' => 'Busca Ativa',
        ],
        'teams' => [
            'label' => 'Equipes (Mensal)',
            'icon' => 'users',
            'badge' => $teamCount !== null ? $teamCount.' eSF' : 'Mensal',
        ],
        'guide' => [
            'label' => 'Caderno Metodológico',
            'icon' => 'book',
            'badge' => 'NT 30/2025',
        ],
    ];
@endphp

<div class="mb-6 space-y-4">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-muted mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-teal-700 transition">Visão Geral</a>
                <span>/</span>
                <span class="text-teal-800 font-medium">Componente II</span>
                <span>/</span>
                <span class="text-ink font-semibold">{{ $title }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $title }}</h1>
            <p class="text-sm text-muted mt-0.5">{{ $subtitle }}</p>
        </div>

        <!-- Badges de Conformidade -->
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-xl border border-teal-700/20 bg-teal-50 px-3.5 py-2 text-xs font-semibold text-teal-900 shadow-xs">
                <span class="h-2 w-2 rounded-full bg-teal-600 animate-pulse"></span>
                <span>Componente II · Vínculo e Território</span>
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800">
                <svg class="h-3.5 w-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Base local processada</span>
            </span>
        </div>
    </div>

    <!-- Navegação em Abas Horizontais -->
    <div class="border-b border-line bg-white rounded-2xl p-1.5 shadow-sm border border-slate-200/80">
        <nav class="flex items-center gap-1 overflow-x-auto pb-1 text-xs font-medium" aria-label="Abas do Módulo Vínculo e Acompanhamento">
            @foreach ($tabs as $key => $tab)
                @php
                    $isActive = ($activeTab === $key);
                    $isNominalRoute = request()->routeIs('territorial-bonding.nominal');
                @endphp
                @if ($key === 'nominal')
                    <a
                        href="{{ route('territorial-bonding.nominal') }}"
                        @if ($isActive) aria-current="page" @endif
                        class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition cursor-pointer {{ $isActive ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 17.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span>{{ $tab['label'] }}</span>
                        <span class="ml-1 rounded-md px-1.5 py-0.5 text-[10px] font-mono {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tab['badge'] }}
                        </span>
                    </a>
                @elseif ($isNominalRoute)
                    <a
                        href="{{ route('territorial-bonding.overview', ['aba' => $key]) }}"
                        @if ($isActive) aria-current="page" @endif
                        class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition cursor-pointer {{ $isActive ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}"
                    >
                        @if ($tab['icon'] === 'id-card')
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                            </svg>
                        @elseif ($tab['icon'] === 'home-user')
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        @elseif ($tab['icon'] === 'users')
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        @else
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        @endif
                        <span>{{ $tab['label'] }}</span>
                        <span class="ml-1 rounded-md px-1.5 py-0.5 text-[10px] font-mono {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tab['badge'] }}
                        </span>
                    </a>
                @else
                    <button
                        type="button"
                        wire:click="$set('activeTab', '{{ $key }}')"
                        class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition cursor-pointer {{ $isActive ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}"
                    >
                        @if ($tab['icon'] === 'id-card')
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                            </svg>
                        @elseif ($tab['icon'] === 'home-user')
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        @elseif ($tab['icon'] === 'users')
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        @else
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        @endif
                        <span>{{ $tab['label'] }}</span>
                        <span class="ml-1 rounded-md px-1.5 py-0.5 text-[10px] font-mono {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tab['badge'] }}
                        </span>
                    </button>
                @endif
            @endforeach
        </nav>
    </div>
</div>
