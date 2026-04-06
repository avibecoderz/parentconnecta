@props([
    'href' => null,
])

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->except('href')->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-[1.05rem] bg-[var(--pc-primary)] px-5 py-3.5 text-sm font-semibold text-white shadow-[0_22px_45px_-28px_rgba(13,59,102,0.65)] transition hover:bg-[var(--pc-primary-deep)] focus:outline-none focus:ring-2 focus:ring-[var(--pc-primary)]/20 disabled:cursor-not-allowed disabled:opacity-60']) }}
    >
        {{ $slot }}
    </a>
@else
    <x-primary-button {{ $attributes }}>
        {{ $slot }}
    </x-primary-button>
@endif
