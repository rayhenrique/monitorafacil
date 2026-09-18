<section id="qualidade" aria-labelledby="quality-title" class="mt-12 scroll-mt-8">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Novo Financiamento APS</p>
            <h2 id="quality-title" class="mt-2 text-2xl font-semibold tracking-tight text-ink">Componente de Qualidade</h2>
            <p class="mt-1 text-sm text-muted">Acompanhamento dos indicadores clínicos no {{ $quarter }}º quadrimestre de {{ $year }}.</p>
        </div>
        <span class="rounded-full bg-teal-50 px-3.5 py-1 text-xs font-semibold text-teal-900 border border-teal-200/60">
            Metodologia Nota Técnica 30/2025
        </span>
    </div>

    <!-- Sub-seção: SAÚDE DA FAMÍLIA (C1 a C7) -->
    <div class="mt-8">
        <div class="mb-4 flex items-center gap-2.5">
            <span class="h-2 w-2 rounded-full bg-teal-600"></span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-teal-900">Saúde da Família · Indicadores C1 a C7</h3>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($familyHealth as $indicator)
                <article class="flex flex-col justify-between rounded-2xl border border-line bg-white p-5 shadow-panel transition hover:border-teal-300/60">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-teal-100/70 px-2 py-1 text-xs font-bold text-teal-900">{{ $indicator['code'] }}</span>
                                <h4 class="text-base font-semibold text-ink">{{ $indicator['name'] }}</h4>
                            </div>
                        </div>
                        <p class="mt-2 text-xs leading-relaxed text-muted">{{ $indicator['description'] }}</p>
                    </div>

                    <div class="mt-6 grid grid-cols-4 rounded-xl border border-line bg-canvas/60 p-2.5 text-center">
                        <div>
                            <p class="text-lg font-semibold tabular-nums text-emerald-700">{{ $indicator['optimal'] }}</p>
                            <p class="text-[10px] font-medium text-muted uppercase">Ótimo</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-lg font-semibold tabular-nums text-teal-700">{{ $indicator['good'] }}</p>
                            <p class="text-[10px] font-medium text-muted uppercase">Bom</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-lg font-semibold tabular-nums text-amber-700">{{ $indicator['sufficient'] }}</p>
                            <p class="text-[10px] font-medium text-muted uppercase">Suficiente</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-lg font-semibold tabular-nums text-rose-700">{{ $indicator['regular'] }}</p>
                            <p class="text-[10px] font-medium text-muted uppercase">Regular</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    <!-- Sub-seção: SAÚDE BUCAL (B1 a B6) -->
    <div class="mt-10">
        <div class="mb-4 flex items-center gap-2.5">
            <span class="h-2 w-2 rounded-full bg-teal-600"></span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-teal-900">Saúde Bucal · Indicadores B1 a B6</h3>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($oralHealth as $indicator)
                <article class="flex flex-col justify-between rounded-2xl border border-line bg-white p-5 shadow-panel transition hover:border-teal-300/60">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-teal-100/70 px-2 py-1 text-xs font-bold text-teal-900">{{ $indicator['code'] }}</span>
                                <h4 class="text-base font-semibold text-ink">{{ $indicator['name'] }}</h4>
                            </div>
                        </div>
                        <p class="mt-2 text-xs leading-relaxed text-muted">{{ $indicator['description'] }}</p>
                    </div>

                    <div class="mt-6 grid grid-cols-4 rounded-xl border border-line bg-canvas/60 p-2.5 text-center">
                        <div>
                            <p class="text-lg font-semibold tabular-nums text-emerald-700">{{ $indicator['optimal'] }}</p>
                            <p class="text-[10px] font-medium text-muted uppercase">Ótimo</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-lg font-semibold tabular-nums text-teal-700">{{ $indicator['good'] }}</p>
                            <p class="text-[10px] font-medium text-muted uppercase">Bom</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-lg font-semibold tabular-nums text-amber-700">{{ $indicator['sufficient'] }}</p>
                            <p class="text-[10px] font-medium text-muted uppercase">Suficiente</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-lg font-semibold tabular-nums text-rose-700">{{ $indicator['regular'] }}</p>
                            <p class="text-[10px] font-medium text-muted uppercase">Regular</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
