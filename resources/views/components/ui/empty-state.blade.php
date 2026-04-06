@props([
    'title' => 'Nothing here yet',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-[1.2rem] border border-dashed border-slate-200 bg-slate-50 px-5 py-10 text-center']) }}>
    @isset($icon)
        <div class="mb-4 flex justify-center">
            {{ $icon }}
        </div>
    @endisset

    <p class="text-sm font-semibold text-slate-700">{{ $title }}</p>

    @if ($description)
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $description }}</p>
    @endif
</div>
