@props([
    'title',
    'subtitle' => null,
    'meta' => null,
])

<article {{ $attributes->merge(['class' => 'flex items-start justify-between gap-4 rounded-[1.2rem] px-1 py-1']) }}>
    <div class="flex min-w-0 items-start gap-3">
        @isset($leading)
            {{ $leading }}
        @endisset

        <div class="min-w-0">
            <h3 class="text-[15px] font-semibold leading-5 text-slate-950">{{ $title }}</h3>

            @if ($subtitle)
                <p class="mt-1 text-xs text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if ($meta || isset($trailing))
        <div class="min-w-0 max-w-[40%] text-right">
            @if ($meta)
                <p class="break-words text-sm font-medium leading-5 text-slate-700">{{ $meta }}</p>
            @endif

            @isset($trailing)
                <div class="mt-2 flex justify-end">
                    {{ $trailing }}
                </div>
            @endisset
        </div>
    @endif
</article>
