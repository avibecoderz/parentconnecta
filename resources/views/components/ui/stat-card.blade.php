@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'neutral',
    'iconTone' => null,
    'hintTone' => null,
])

@php
    $resolvedIconTone = $iconTone ?? match ($tone) {
        'success' => 'success',
        'danger' => 'danger',
        'primary' => 'primary',
        default => 'neutral',
    };

    $resolvedHintTone = $hintTone ?? $tone;

    $hintClasses = match ($resolvedHintTone) {
        'success' => 'text-emerald-600',
        'danger' => 'text-rose-600',
        'primary' => 'text-[var(--pc-primary)]',
        default => 'text-slate-500',
    };
@endphp

<article {{ $attributes->merge(['class' => 'pc-card flex h-full flex-col justify-between px-5 py-5 sm:px-6 sm:py-6']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <x-ui.small-metric-label :value="$label" />
            <p class="pc-metric-value mt-5 break-words leading-[1.05]">{{ $value }}</p>
        </div>

        @isset($icon)
            <x-ui.icon-badge :tone="$resolvedIconTone">
                {{ $icon }}
            </x-ui.icon-badge>
        @endisset
    </div>

    @if ($hint)
        <p class="pc-helper-text mt-4 leading-7 {{ $hintClasses }}">{{ $hint }}</p>
    @endif
</article>
