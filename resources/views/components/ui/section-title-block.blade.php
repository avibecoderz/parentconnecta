@props([
    'title',
    'description' => null,
    'eyebrow' => null,
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'min-w-0']) }}>
    @if ($eyebrow)
        <p class="pc-eyebrow text-slate-400">{{ $eyebrow }}</p>
    @endif

    <h2 @class([
        'pc-section-title',
        'mt-1 text-[1.72rem]' => ! $compact,
        'mt-1 text-lg' => $compact,
    ])>{{ $title }}</h2>

    @if ($description)
        <p class="pc-helper-text mt-1.5">{{ $description }}</p>
    @endif
</div>
