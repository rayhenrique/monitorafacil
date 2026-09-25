@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'p-5 sm:p-6',
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-[#dce6e2] bg-white shadow-xs transition']) }}>
    @if ($title || isset($header) || isset($actions))
        <div class="flex flex-col gap-2 border-b border-[#dce6e2] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                @if (isset($header))
                    {{ $header }}
                @else
                    <h3 class="text-base font-bold text-[#16302c]">{{ $title }}</h3>
                    @if ($subtitle)
                        <p class="mt-0.5 text-xs text-[#58716b]">{{ $subtitle }}</p>
                    @endif
                @endif
            </div>
            @if (isset($actions))
                <div class="flex items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="border-t border-[#dce6e2] bg-[#f5f7f6]/50 px-5 py-3 rounded-b-2xl sm:px-6">
            {{ $footer }}
        </div>
    @endif
</div>
