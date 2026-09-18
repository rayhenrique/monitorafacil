<section id="equipes" aria-labelledby="teams-title" class="mt-8 scroll-mt-8">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Estrutura assistencial</p>
            <h2 id="teams-title" class="mt-2 text-2xl font-semibold tracking-tight text-ink">Equipes homologadas</h2>
            <p class="mt-1 text-sm text-muted">INEs homologados na competência de {{ $quarter }}º quadrimestre de {{ $year }}.</p>
        </div>
        @if ($complete)
            <p class="rounded-full bg-teal-50 px-3.5 py-1 text-xs font-semibold text-teal-900 border border-teal-200/60">{{ number_format(array_sum($totals), 0, ',', '.') }} equipes no total</p>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        @php
            $teamTypes = [
                [
                    'key' => 'esf',
                    'label' => 'Saúde da Família',
                    'abbr' => 'eSF',
                    'description' => 'Equipes de Saúde da Família e de Atenção Primária com CNES e INE válidos no quadrimestre.',
                    'icon' => 'home-users'
                ],
                [
                    'key' => 'esaude_bucal',
                    'label' => 'Saúde Bucal',
                    'abbr' => 'eSB',
                    'description' => 'Equipes de Saúde Bucal com CNES e INE válidos no quadrimestre.',
                    'icon' => 'tooth'
                ],
                [
                    'key' => 'emulti',
                    'label' => 'e-Multi',
                    'abbr' => 'eMulti',
                    'description' => 'Equipes Multiprofissionais com INE válido no quadrimestre.',
                    'icon' => 'users-multi'
                ]
            ];
        @endphp

        @foreach ($teamTypes as $teamType)
            <article class="flex flex-col justify-between rounded-2xl border border-line bg-white p-6 shadow-panel transition hover:border-teal-300/60">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            @if ($teamType['key'] === 'esf')
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-800">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                    </svg>
                                </span>
                            @elseif ($teamType['key'] === 'esaude_bucal')
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-800">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8.5 2 7 4.5 7 7c0 2 .5 4 1 6.5s1.5 5.5 2 7.5c.3 1.2 1.5 1 2-.5.5-1.5 1-3 1.5-4 .5 1 1 2.5 1.5 4 .5 1.5 1.7 1.7 2 .5.5-2 1.5-5 2-7.5s1-4.5 1-6.5c0-2.5-1.5-5-5-5h-4z" />
                                    </svg>
                                </span>
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-800">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                    </svg>
                                </span>
                            @endif
                            <div>
                                <h3 class="text-base font-semibold text-ink">{{ $teamType['label'] }}</h3>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-teal-800">{{ $teamType['abbr'] }}</p>
                            </div>
                        </div>
                        <span class="h-2.5 w-2.5 rounded-full {{ $totals[$teamType['key']] === null ? 'bg-slate-300' : 'bg-emerald-500' }}" aria-hidden="true"></span>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-muted">{{ $teamType['description'] }}</p>
                </div>

                <div class="mt-6 border-t border-line pt-4">
                    <p class="text-3xl font-bold tracking-tight tabular-nums text-ink">
                        {{ $totals[$teamType['key']] === null ? '—' : number_format($totals[$teamType['key']], 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-muted">
                        {{ $totals[$teamType['key']] === null ? 'Sem consolidação neste período' : 'Equipes no snapshot local' }}
                    </p>
                </div>
            </article>
        @endforeach
    </div>
</section>
