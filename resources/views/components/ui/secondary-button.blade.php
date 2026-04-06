@props([
    'href' => null,
])

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->except('href')->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-[1.05rem] bg-slate-100 px-5 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-[var(--pc-primary)]/15 disabled:cursor-not-allowed disabled:opacity-60']) }}
    >
        {{ $slot }}
    </a>
@else
    <x-secondary-button {{ $attributes }}>
        {{ $slot }}
    </x-secondary-button>
@endif
