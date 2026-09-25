@props([
    'href' => null,
    'type' => 'button',
    'size' => 'md',
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'lg' => 'px-5 py-2.5 text-base',
        default => 'px-4 py-2 text-sm',
    };
    $baseClasses = "inline-flex items-center justify-center gap-2 rounded-lg bg-teal-700 hover:bg-teal-800 active:bg-teal-900 text-white font-semibold shadow-xs focus:outline-hidden focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:opacity-50 transition cursor-pointer {$sizeClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </button>
@endif
