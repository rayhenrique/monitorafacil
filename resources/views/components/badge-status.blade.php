@props([
    'status' => 'regular',
    'label' => null,
    'size' => 'sm',
])

@php
    $normalized = strtolower(trim((string) $status));
    $badgeConfig = match ($normalized) {
        'otimo', 'ótimo', 'optimal' => [
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot' => 'bg-emerald-500',
            'defaultLabel' => 'Ótimo',
        ],
        'bom', 'good' => [
            'class' => 'bg-teal-50 text-teal-700 border-teal-200',
            'dot' => 'bg-teal-600',
            'defaultLabel' => 'Bom',
        ],
        'suficiente', 'sufficient' => [
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot' => 'bg-amber-500',
            'defaultLabel' => 'Suficiente',
        ],
        default => [
            'class' => 'bg-rose-50 text-rose-700 border-rose-200',
            'dot' => 'bg-rose-500',
            'defaultLabel' => 'Regular',
        ],
    };

    $sizeClasses = match ($size) {
        'md' => 'px-3 py-1 text-xs gap-1.5',
        default => 'px-2.5 py-0.5 text-[11px] gap-1',
    };

    $displayText = $label ?? $badgeConfig['defaultLabel'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border font-semibold {$badgeConfig['class']} {$sizeClasses}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $badgeConfig['dot'] }} shrink-0" aria-hidden="true"></span>
    <span>{{ $displayText }}</span>
</span>
