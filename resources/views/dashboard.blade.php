@extends('layouts.app')

@section('content')
    <section aria-labelledby="dashboard-title" class="mx-auto max-w-7xl px-6 py-8 sm:px-10 lg:py-10">
        <div class="max-w-3xl">
            <p class="eyebrow">Visão Municipal</p>
            <h1 id="dashboard-title" class="mt-2.5 text-3xl font-bold tracking-tight text-ink sm:text-4xl">Resumo do Monitoramento da APS</h1>
            <p class="mt-2.5 max-w-2xl text-base leading-relaxed text-muted">Consolidado quadrimestral das equipes homologadas, vínculo/acompanhamento e indicadores clínicos da Atenção Primária.</p>
        </div>
        @livewire('dashboard.quarter-selector')
    </section>
@endsection
