<section id="qualidade" aria-labelledby="quality-title" class="mt-12 scroll-mt-8">
    @php
        $renderIcon = function($code) {
            return match($code) {
                'C1' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333M4.5 21V10.333" />',
                'C2' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm5.25 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75z" />',
                'C3' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />',
                'C4' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25c-3.75 5.25-6 8.5-6 12a6 6 0 0012 0c0-3.5-2.25-6.75-6-12z" />',
                'C5' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12h3.75l2.25-6 3.75 12 2.25-6h3.75M21 12h.75" />',
                'C6' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />',
                'C7' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />',
                'B1' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />',
                'B2' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8.5 2 7 4.5 7 7c0 2 .5 4 1 6.5s1.5 5.5 2 7.5c.3 1.2 1.5 1 2-.5.5-1.5 1-3 1.5-4 .5 1 1 2.5 1.5 4 .5 1.5 1.7 1.7 2 .5.5-2 1.5-5 2-7.5s1-4.5 1-6.5c0-2.5-1.5-5-5-5h-4z" /><path stroke-linecap="round" stroke-linejoin="round" d="M10 10l1.5 1.5L14 8" />',
                'B3' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8.5 2 7 4.5 7 7c0 2 .5 4 1 6.5s1.5 5.5 2 7.5c.3 1.2 1.5 1 2-.5.5-1.5 1-3 1.5-4 .5 1 1 2.5 1.5 4 .5 1.5 1.7 1.7 2 .5.5-2 1.5-5 2-7.5s1-4.5 1-6.5c0-2.5-1.5-5-5-5h-4z" /><path stroke-linecap="round" stroke-linejoin="round" d="M10 10h4" />',
                'B4' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 21l8.5-8.5M11.5 12.5l5.5-5.5a2.5 2.5 0 00-3.5-3.5l-5.5 5.5M13 5l2 2M15 7l2 2" />',
                'B5' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />',
                'B6' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />',
                'M1' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />',
                'M2' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />',
                default => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-6-6h12" />',
            };
        };
    @endphp

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
                        <div class="flex items-start gap-3.5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-800 border border-teal-100/80">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    {!! $renderIcon($indicator['code']) !!}
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-teal-100/70 px-2 py-0.5 text-xs font-bold text-teal-900">{{ $indicator['code'] }}</span>
                                    <h4 class="text-sm font-semibold text-ink sm:text-base leading-snug">{{ $indicator['name'] }}</h4>
                                </div>
                                <p class="mt-1.5 text-xs leading-relaxed text-muted">{{ $indicator['description'] }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($indicator['has_data'])
                        <div class="mt-6 grid grid-cols-2 gap-y-3 rounded-xl border border-line bg-canvas/60 p-2.5 text-center sm:grid-cols-4 sm:gap-y-0">
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
                        <p class="mt-2 text-[10px] font-medium text-muted">
                            {{ $indicator['evaluated_teams'] }} {{ $indicator['evaluated_teams'] === 1 ? 'equipe avaliada' : 'equipes avaliadas' }} com consolidação válida
                        </p>
                    @else
                        <div class="mt-6 rounded-xl border border-dashed border-line bg-canvas/50 px-3 py-4 text-center">
                            <p class="text-xs font-semibold text-ink">
                                {{ in_array($indicator['code'], ['C1', 'C2', 'C3'], true) ? 'Sem consolidado válido neste período' : 'Indicador ainda não processado' }}
                            </p>
                            @if (in_array($indicator['code'], ['C1', 'C2', 'C3'], true))
                                <p class="mt-1 text-[10px] leading-relaxed text-muted">Execute o processamento correspondente para atualizar este cartão.</p>
                            @endif
                        </div>
                    @endif
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
                        <div class="flex items-start gap-3.5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-800 border border-teal-100/80">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    {!! $renderIcon($indicator['code']) !!}
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-teal-100/70 px-2 py-0.5 text-xs font-bold text-teal-900">{{ $indicator['code'] }}</span>
                                    <h4 class="text-sm font-semibold text-ink sm:text-base leading-snug">{{ $indicator['name'] }}</h4>
                                </div>
                                <p class="mt-1.5 text-xs leading-relaxed text-muted">{{ $indicator['description'] }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($indicator['has_data'])
                        <div class="mt-6 grid grid-cols-2 gap-y-3 rounded-xl border border-line bg-canvas/60 p-2.5 text-center sm:grid-cols-4 sm:gap-y-0">
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
                    @else
                        <div class="mt-6 rounded-xl border border-dashed border-line bg-canvas/50 px-3 py-4 text-center">
                            <p class="text-xs font-semibold text-ink">Sem consolidação disponível neste período</p>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>

    <!-- Sub-seção: E-MULTI (M1 e M2) -->
    <div class="mt-10">
        <div class="mb-4 flex items-center gap-2.5">
            <span class="h-2 w-2 rounded-full bg-teal-600"></span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-teal-900">e-Multi · Indicadores M1 e M2</h3>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($eMulti as $indicator)
                <article class="flex flex-col justify-between rounded-2xl border border-line bg-white p-5 shadow-panel transition hover:border-teal-300/60">
                    <div>
                        <div class="flex items-start gap-3.5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-800 border border-teal-100/80">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    {!! $renderIcon($indicator['code']) !!}
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-teal-100/70 px-2 py-0.5 text-xs font-bold text-teal-900">{{ $indicator['code'] }}</span>
                                    <h4 class="text-sm font-semibold text-ink sm:text-base leading-snug">{{ $indicator['name'] }}</h4>
                                </div>
                                <p class="mt-1.5 text-xs leading-relaxed text-muted">{{ $indicator['description'] }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($indicator['has_data'])
                        <div class="mt-6 grid grid-cols-2 gap-y-3 rounded-xl border border-line bg-canvas/60 p-2.5 text-center sm:grid-cols-4 sm:gap-y-0">
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
                    @else
                        <div class="mt-6 rounded-xl border border-dashed border-line bg-canvas/50 px-3 py-4 text-center">
                            <p class="text-xs font-semibold text-ink">Sem consolidação disponível neste período</p>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
