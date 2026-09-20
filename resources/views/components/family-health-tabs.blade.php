@props([
    'title' => 'Saúde da Família',
    'subtitle' => 'Monitoramento dos Indicadores de Qualidade do Cuidado (C1 ao C7)',
    'activeIndicator' => 'overview', // 'overview', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7'
])

@php
    $tabs = [
        'overview' => ['label' => 'Visão Geral C1 a C7', 'route' => 'family-health.overview', 'icon' => 'grid'],
        'c1' => ['label' => 'C1 · Mais Acesso', 'route' => 'family-health.indicator', 'param' => 'c1'],
        'c2' => ['label' => 'C2 · Crianças', 'route' => 'family-health.indicator', 'param' => 'c2'],
        'c3' => ['label' => 'C3 · Gestação e Puerpério', 'route' => 'family-health.indicator', 'param' => 'c3'],
        'c4' => ['label' => 'C4 · Diabetes', 'route' => 'family-health.indicator', 'param' => 'c4'],
        'c5' => ['label' => 'C5 · Hipertensão', 'route' => 'family-health.indicator', 'param' => 'c5'],
        'c6' => ['label' => 'C6 · Pessoa Idosa', 'route' => 'family-health.indicator', 'param' => 'c6'],
        'c7' => ['label' => 'C7 · Prevenção Câncer', 'route' => 'family-health.indicator', 'param' => 'c7'],
    ];
@endphp

<div class="mb-6 space-y-4">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-muted mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-teal-700 transition">Visão Geral</a>
                <span>/</span>
                <span class="text-teal-800 font-medium">Saúde da Família</span>
                <span>/</span>
                <span class="text-ink font-semibold">{{ $title }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $title }}</h1>
            <p class="text-sm text-muted mt-0.5">{{ $subtitle }}</p>
        </div>

        <!-- Indicador de Conformidade Portaria GM/MS nº 3.493/2024 -->
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-xl border border-teal-700/20 bg-teal-50 px-3.5 py-2 text-xs font-semibold text-teal-900 shadow-xs">
                <span class="h-2 w-2 rounded-full bg-teal-600 animate-pulse"></span>
                <span>Portaria GM/MS nº 3.493/2024 · Componente de Qualidade</span>
            </span>
        </div>
    </div>

    <!-- Navegação em Abas Horizontais -->
    <div class="border-b border-line bg-white rounded-2xl p-1.5 shadow-sm border border-slate-200/80">
        <nav class="flex items-center gap-1 overflow-x-auto pb-1 text-xs font-medium" aria-label="Abas do Módulo Saúde da Família">
            @foreach ($tabs as $key => $tab)
                @php
                    $isActive = ($activeIndicator === $key);
                    $url = isset($tab['param']) ? route($tab['route'], ['indicator' => $tab['param']]) : route($tab['route']);
                @endphp
                <a
                    href="{{ $url }}"
                    class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition {{ $isActive ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}"
                >
                    @if ($key === 'overview')
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    @else
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-mono font-bold {{ $isActive ? 'bg-teal-800 text-teal-200' : 'bg-slate-100 text-slate-700' }}">{{ strtoupper($key) }}</span>
                    @endif
                    <span>{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>
</div>
