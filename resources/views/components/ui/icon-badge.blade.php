@props([
    'tone' => 'neutral',
    'size' => 'md',
])

@php
    $toneClasses = [
        'neutral' => 'bg-slate-100 text-slate-600',
        'primary' => 'bg-[var(--pc-primary)] text-white',
        'success' => 'bg-emerald-50 text-emerald-600',
        'danger' => 'bg-rose-50 text-rose-600',
        'warning' => 'bg-amber-50 text-amber-600',
    ];

    $sizeClasses = [
        'sm' => 'h-9 w-9 rounded-xl',
        'md' => 'h-11 w-11 rounded-2xl',
        'lg' => 'h-12 w-12 rounded-2xl',
    ];
@endphp

<span {{ $attributes->class([
    'pc-icon-badge',
    $toneClasses[$tone] ?? $toneClasses['neutral'],
    $sizeClasses[$size] ?? $sizeClasses['md'],
]) }}>
    {{ $slot }}
</span>
