<div>
    <section class="mt-8 flex flex-wrap items-end justify-between gap-5 rounded-2xl border border-line bg-white p-5 shadow-panel sm:p-6" aria-label="Período de análise">
        <div>
            <p class="eyebrow">Período em análise</p>
            <p class="mt-2 text-lg font-semibold text-ink">{{ $quarter }}º quadrimestre de {{ $year }}</p>
            <p class="mt-1 text-sm text-muted">Selecione o período para atualizar todos os indicadores.</p>
        </div>
        <div class="grid w-full grid-cols-1 gap-3 sm:w-auto sm:grid-cols-2">
            <label class="block min-w-0 text-sm font-medium text-ink">Ano
                <select wire:model.change="year" class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-200 sm:min-w-28">
                    @foreach ($years as $availableYear)
                        <option value="{{ $availableYear }}">{{ $availableYear }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block min-w-0 text-sm font-medium text-ink">Quadrimestre
                <select wire:model.change="quarter" class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-200 sm:min-w-40">
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
