<section id="cadastros" aria-labelledby="registrations-title" class="mt-10 scroll-mt-8">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Vínculo e território</p>
            <h2 id="registrations-title" class="mt-2 text-2xl font-semibold tracking-tight text-ink">Vínculo e Acompanhamento</h2>
            <p class="mt-1 text-sm text-muted">Atualização nos últimos 24 meses no snapshot do {{ $quarter }}º quadrimestre de {{ $year }}.</p>
        </div>
    </div>

    @php
        $hasData = false;
        $miciUpdated = 0;
        $miciOutdated = 0;
        $miciTotal = 0;
        $miciPct = 0;
        $micdtUpdated = 0;
        $micdtOutdated = 0;
        $micdtTotal = 0;
        $micdtPct = 0;
        $competenciaMes = '';

        if (isset($cvatMetrics) && $cvatMetrics !== null) {
            $hasData = true;
            $competenciaMes = $cvatMetrics->year . ' / M' . str_pad((string) $cvatMetrics->month, 2, '0', STR_PAD_LEFT);
            $miciUpdated = (int) $cvatMetrics->mici_updated;
            $miciOutdated = (int) $cvatMetrics->mici_outdated;
            $miciTotal = (int) $cvatMetrics->mici_total;
            $miciPct = $miciTotal > 0 ? round(($miciUpdated / $miciTotal) * 100, 2) : 0;

            $micdtUpdated = (int) $cvatMetrics->mici_and_micdt_updated;
            $micdtOutdated = (int) $cvatMetrics->mici_and_micdt_outdated;
            $micdtTotal = (int) $cvatMetrics->mici_with_micdt_total;
            $micdtPct = $micdtTotal > 0 ? round(($micdtUpdated / $micdtTotal) * 100, 2) : 0;
        } elseif ($snapshot !== null) {
            $hasData = true;
            $competenciaMes = $year . ' / M' . str_pad($quarter * 4, 2, '0', STR_PAD_LEFT);
            $miciUpdated = (int) $snapshot->mici_updated_count;
            $miciOutdated = (int) $snapshot->mici_outdated_count;
            $miciTotal = $miciUpdated + $miciOutdated;
            $miciPct = $miciTotal > 0 ? round(($miciUpdated / $miciTotal) * 100, 2) : 0;

            $micdtUpdated = (int) $snapshot->micdt_updated_count;
            $micdtOutdated = (int) $snapshot->micdt_outdated_count;
            $micdtTotal = $micdtUpdated + $micdtOutdated;
            $micdtPct = $micdtTotal > 0 ? round(($micdtUpdated / $micdtTotal) * 100, 2) : 0;
        }
    @endphp

    <div class="grid gap-5 lg:grid-cols-3">
            <!-- Card 1: Classificação do Quadrimestre -->
            <article class="flex flex-col justify-between rounded-2xl border border-line bg-white p-6 shadow-panel">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-800">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-ink">Classificação do Quadrimestre</h3>
                                <p class="text-xs text-muted">Desempenho das equipes de eSF</p>
                            </div>
                        </div>
                        <span class="rounded-lg bg-teal-50 px-2 py-0.5 text-xs font-bold text-teal-900 border border-teal-200/60">Q{{ $quarter }} / {{ $year }}</span>
                    </div>

                    <p class="mt-4 text-xs leading-relaxed text-muted">Distribuição oficial das equipes no modelo de cofinanciamento.</p>
                </div>

                @if ($cvatSummary['has_data'])
                    <div class="mt-6 grid grid-cols-2 gap-y-3 rounded-xl border border-line bg-canvas/60 p-3 text-center sm:grid-cols-4 sm:gap-y-0">
                        <div>
                            <p class="text-xl font-bold tabular-nums text-emerald-700">{{ $classifications['optimal'] }}</p>
                            <p class="text-[10px] font-semibold text-muted uppercase">ÓTIMO</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-xl font-bold tabular-nums text-teal-700">{{ $classifications['good'] }}</p>
                            <p class="text-[10px] font-semibold text-muted uppercase">BOM</p>
                        </div>
                        <div class="border-t border-line/80 pt-3 sm:border-l sm:border-t-0 sm:pt-0">
                            <p class="text-xl font-bold tabular-nums text-amber-700">{{ $classifications['sufficient'] }}</p>
                            <p class="text-[10px] font-semibold text-muted uppercase">SUFICIENTE</p>
                        </div>
                        <div class="border-l border-t border-line/80 pt-3 sm:border-t-0 sm:pt-0">
                            <p class="text-xl font-bold tabular-nums text-rose-700">{{ $classifications['regular'] }}</p>
                            <p class="text-[10px] font-semibold text-muted uppercase">REGULAR</p>
                        </div>
                    </div>
                @else
                    <div class="mt-6 rounded-xl border border-dashed border-line bg-canvas/50 px-4 py-5 text-center">
                        <p class="text-xs font-semibold text-ink">Sem consolidação disponível neste período</p>
                    </div>
                @endif

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('territorial-bonding.nominal') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-900 transition inline-flex items-center gap-1">
                        <span>Acessar Módulo Vínculo e Acompanhamento</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </article>

            <!-- Card 2: Total MICI Atualizados -->
            <article class="flex flex-col justify-between rounded-2xl border border-line bg-white p-6 shadow-panel">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-800">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-ink">Total MICI atualizados</h3>
                                <p class="text-xs text-muted">Cadastro Individual</p>
                            </div>
                        </div>
                        @if ($hasData)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 border border-emerald-200">
                                {{ number_format($miciPct, 2, ',', '.') }}%
                            </span>
                        @endif
                    </div>

                    @if ($hasData)
                        <p class="mt-4 text-3xl font-bold tracking-tight tabular-nums text-ink">
                            {{ number_format($miciUpdated, 0, ',', '.') }}
                        </p>
                    @endif

                    <p class="mt-2 text-xs leading-relaxed text-muted">
                        MICI — Cadastro Individual. Considera-se atualizado o cadastro incluído ou modificado nos últimos 24 meses.
                    </p>
                </div>

                @if ($hasData)
                    <div class="mt-6 grid grid-cols-3 gap-2 border-t border-line pt-3 text-center">
                        <div>
                            <p class="text-[10px] font-medium text-muted uppercase">Mês</p>
                            <p class="mt-0.5 text-xs font-semibold text-ink">{{ $competenciaMes }}</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-[10px] font-medium text-muted uppercase">Total Geral</p>
                            <p class="mt-0.5 text-xs font-semibold text-ink">{{ number_format($miciTotal, 0, ',', '.') }}</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-[10px] font-medium text-muted uppercase">Desatualizados</p>
                            <p class="mt-0.5 text-xs font-semibold text-amber-700">{{ number_format($miciOutdated, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @else
                    <div class="mt-6 rounded-xl border border-dashed border-line bg-canvas/50 px-4 py-5 text-center">
                        <p class="text-xs font-semibold text-ink">Sem consolidação disponível neste período</p>
                    </div>
                @endif
            </article>

            <!-- Card 3: Total MICI + MICDT Atualizados -->
            <article class="flex flex-col justify-between rounded-2xl border border-line bg-white p-6 shadow-panel">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-800">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1.091m-16.5-3.273L12 3m0 0l4.5 1.636M5.25 14.25h1.5m-1.5 3h1.5m10.5-3h1.5m-1.5 3h1.5" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-ink">Total MICI + MICDT atualizados</h3>
                                <p class="text-xs text-muted">fichas/domicílios</p>
                            </div>
                        </div>
                        @if ($hasData)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 border border-emerald-200">
                                {{ number_format($micdtPct, 2, ',', '.') }}%
                            </span>
                        @endif
                    </div>

                    @if ($hasData)
                        <p class="mt-4 text-3xl font-bold tracking-tight tabular-nums text-ink">
                            {{ number_format($micdtUpdated, 0, ',', '.') }}
                        </p>
                    @endif

                    <p class="mt-2 text-xs leading-relaxed text-muted">
                        MICDT — Cadastro Domiciliar e Territorial (fichas/domicílios). A pessoa com cadastro completo tem MICI e MICDT.
                    </p>
                </div>

                @if ($hasData)
                    <div class="mt-6 grid grid-cols-3 gap-2 border-t border-line pt-3 text-center">
                        <div>
                            <p class="text-[10px] font-medium text-muted uppercase">Mês</p>
                            <p class="mt-0.5 text-xs font-semibold text-ink">{{ $competenciaMes }}</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-[10px] font-medium text-muted uppercase">Total Geral</p>
                            <p class="mt-0.5 text-xs font-semibold text-ink">{{ number_format($micdtTotal, 0, ',', '.') }}</p>
                        </div>
                        <div class="border-l border-line/80">
                            <p class="text-[10px] font-medium text-muted uppercase">Desatualizados</p>
                            <p class="mt-0.5 text-xs font-semibold text-amber-700">{{ number_format($micdtOutdated, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @else
                    <div class="mt-6 rounded-xl border border-dashed border-line bg-canvas/50 px-4 py-5 text-center">
                        <p class="text-xs font-semibold text-ink">Sem consolidação disponível neste período</p>
                    </div>
                @endif
            </article>
    </div>
</section>
