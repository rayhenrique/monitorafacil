@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="min-w-0 flex-1">
        @if ($eyebrow)
            <p class="text-xs font-bold uppercase tracking-wider text-teal-700 mb-1">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-2xl font-bold tracking-tight text-[#16302c] sm:text-3xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-[#58716b] leading-relaxed">{{ $subtitle }}</p>
        @endif
    </div>

    @if (isset($actions) || $slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2.5 sm:justify-end shrink-0">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>
