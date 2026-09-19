@props([
    'percent' => 0,
    'step' => 'Preparando o processamento...',
    'running' => false,
    'status' => null,
])

@php
    $percent = max(0, min(100, (int) $percent));
    $isError = $status === 'error';
    $statusLabel = $running ? 'Em andamento' : ($isError ? 'Concluído com pendências' : 'Concluído');
@endphp

<div class="border-t border-line pt-5 space-y-3" aria-live="polite" aria-atomic="true">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between text-xs">
        <div class="min-w-0 flex items-start gap-2">
            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $isError ? 'bg-rose-600' : 'bg-teal-600' }} {{ $running ? 'motion-safe:animate-pulse' : '' }}"></span>
            <div class="min-w-0">
                <span class="font-bold text-ink">Progresso da consolidação</span>
                <p class="mt-0.5 break-words font-mono text-[11px] leading-relaxed text-muted">{{ $step }}</p>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-2 sm:justify-end">
            <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $isError ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-teal-200 bg-teal-50 text-teal-800' }}">
                {{ $statusLabel }}
            </span>
            <span class="min-w-11 text-right font-mono font-bold {{ $isError ? 'text-rose-800' : 'text-teal-800' }}">{{ $percent }}%</span>
        </div>
    </div>

    <div
        class="h-3.5 w-full overflow-hidden rounded-full border border-slate-200/80 bg-slate-100 p-0.5"
        role="progressbar"
        aria-label="Progresso do processamento dos dados do e-SUS PEC"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="{{ $percent }}"
        aria-valuetext="{{ $percent }} por cento. {{ $step }}"
        aria-busy="{{ $running ? 'true' : 'false' }}"
    >
        <div
            class="relative h-full overflow-hidden rounded-full transition-[width] duration-500 ease-out {{ $isError ? 'bg-rose-600' : 'bg-gradient-to-r from-teal-700 via-emerald-500 to-teal-500' }}"
            style="width: {{ $percent }}%"
        >
            @if ($running)
                <span class="absolute inset-0 bg-white/20 motion-safe:animate-pulse"></span>
            @endif
        </div>
    </div>

    @if ($running)
        <p class="text-[11px] leading-relaxed text-muted">Mantenha esta página aberta. A etapa e o percentual são atualizados conforme cada consolidação termina.</p>
    @endif
</div>
