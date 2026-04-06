@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<header {{ $attributes->merge(['class' => 'flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">{{ $eyebrow }}</p>
        @endif

        <h1 class="mt-1 text-[2rem] font-semibold tracking-tight text-slate-950 sm:text-[2.35rem]">{{ $title }}</h1>

        @if ($description)
            <p class="mt-3 max-w-xl text-sm leading-7 text-slate-600 sm:text-[15px]">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="grid grid-cols-2 gap-3 sm:min-w-[18rem]">
            {{ $actions }}
        </div>
    @endisset
</header>
