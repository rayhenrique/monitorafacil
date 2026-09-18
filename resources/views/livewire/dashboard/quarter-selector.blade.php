<div>
    <section class="mt-8 flex flex-wrap items-end justify-between gap-5 rounded-2xl border border-line bg-white p-5 shadow-panel sm:p-6" aria-label="Período de análise">
        <div>
            <p class="eyebrow">Período em análise</p>
            <p class="mt-2 text-lg font-semibold text-ink">{{ $quarter }}º quadrimestre de {{ $year }}</p>
            <p class="mt-1 text-sm text-muted">Selecione o período para atualizar todos os indicadores.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <label class="block text-sm font-medium text-ink">Ano
                <select wire:model.change="year" class="mt-1 block min-w-28 rounded-lg border border-line bg-white px-3 py-2.5 text-sm focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-200">
                    @foreach ($years as $availableYear)
                        <option value="{{ $availableYear }}">{{ $availableYear }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm font-medium text-ink">Quadrimestre
                <select wire:model.change="quarter" class="mt-1 block min-w-40 rounded-lg border border-line bg-white px-3 py-2.5 text-sm focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-200">
                    @foreach ([1, 2, 3] as $availableQuarter)
                        <option value="{{ $availableQuarter }}">{{ $availableQuarter }}º quadrimestre</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    @livewire('dashboard.teams-overview', ['year' => $year, 'quarter' => $quarter], key('teams-'.$year.'-'.$quarter))
    @livewire('dashboard.registrations-overview', ['year' => $year, 'quarter' => $quarter], key('registrations-'.$year.'-'.$quarter))
    @livewire('dashboard.quality-overview', ['year' => $year, 'quarter' => $quarter], key('quality-'.$year.'-'.$quarter))
    @livewire('dashboard.financial-simulator', ['year' => $year, 'quarter' => $quarter], key('financial-'.$year.'-'.$quarter))
</div>
