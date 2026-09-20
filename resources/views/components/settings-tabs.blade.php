@props([
    'title' => 'Configurações',
    'subtitle' => 'Gerencie os parâmetros operacionais, credenciais e integrações da plataforma',
    'activeTab' => 'users',
])

<div class="mb-6 space-y-4">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-muted mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-teal-700 transition">Visão Geral</a>
                <span>/</span>
                <span class="text-teal-800 font-medium">Configurações</span>
                <span>/</span>
                <span class="text-ink font-semibold">{{ $title }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $title }}</h1>
            <p class="text-sm text-muted mt-0.5">{{ $subtitle }}</p>
        </div>
    </div>

    <!-- Navegação em Abas (Tabs) -->
    <div class="border-b border-line bg-white rounded-2xl p-1.5 shadow-sm border border-slate-200/80">
        <nav class="flex items-center gap-1 overflow-x-auto pb-1 text-xs font-medium" aria-label="Abas de Configurações">
            <a href="{{ route('settings.users') }}" class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition {{ $activeTab === 'users' ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <span>Usuários</span>
            </a>

            <a href="{{ route('settings.municipality') }}" class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition {{ $activeTab === 'municipality' ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.75a1.5 1.5 0 011.5-1.5h1.5a1.5 1.5 0 011.5 1.5V21" />
                </svg>
                <span>Município</span>
            </a>

            <a href="{{ route('settings.audit-logs') }}" class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition {{ $activeTab === 'audit-logs' ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Log de Auditoria</span>
            </a>

            <a href="{{ route('settings.esus-connection') }}" class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition {{ $activeTab === 'esus-connection' ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                </svg>
                <span>Conexão com e-SUS</span>
            </a>

            <a href="{{ route('settings.data-processing') }}" class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition {{ $activeTab === 'data-processing' ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>Processar Dados</span>
            </a>

            <a href="{{ route('settings.cnes-import') }}" class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 whitespace-nowrap transition {{ $activeTab === 'cnes-import' ? 'bg-teal-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span>Importar CNES / XML</span>
            </a>
        </nav>
    </div>
</div>
