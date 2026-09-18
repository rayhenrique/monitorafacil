@props([
    'title' => 'Ajuda e Suporte',
    'subtitle' => 'Guias operacionais, documentações ministeriais e boas práticas da Atenção Primária',
    'activeTab' => 'guide',
])

<div class="mb-6 space-y-4">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-muted mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-teal-700 transition">Visão Geral</a>
                <span>/</span>
                <span class="text-teal-800 font-medium">Ajuda</span>
                <span>/</span>
                <span class="text-ink font-semibold">{{ $title }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $title }}</h1>
            <p class="text-sm text-muted mt-0.5">{{ $subtitle }}</p>
        </div>

        <!-- Link Oficial para o Portal do Ministério da Saúde -->
        <div class="flex items-center gap-2">
            <a
                href="https://sisaps.saude.gov.br/sistemas/esusaps/docs/guias-preenchimento/"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 rounded-xl border border-teal-700/20 bg-teal-50 hover:bg-teal-100/80 px-3.5 py-2 text-xs font-semibold text-teal-900 shadow-xs transition"
                title="Acessar documentação oficial no portal do Ministério da Saúde"
            >
                <svg class="h-4 w-4 text-teal-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                <span>Portal Oficial SISAPS · MS</span>
            </a>
        </div>
    </div>

    <!-- Navegação em Abas (Tabs) do Módulo Ajuda -->
    <div class="border-b border-line bg-white rounded-2xl p-1.5 shadow-sm border border-slate-200/80">
        <nav class="flex items-center gap-1 overflow-x-auto text-xs font-medium scrollbar-none" aria-label="Abas do Módulo Ajuda">
            <a href="{{ route('help.guide') }}" class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition {{ $activeTab === 'guide' ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <span>Guia de Preenchimento | Ministério da Saúde</span>
            </a>
        </nav>
    </div>
</div>
