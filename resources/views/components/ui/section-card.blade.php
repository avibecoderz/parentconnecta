@props([
    'padding' => 'md',
])

@php
    $paddingClasses = [
        'sm' => 'px-5 py-5',
        'md' => 'px-5 py-6 sm:px-6',
        'lg' => 'px-6 py-6 sm:px-7',
    ];
@endphp

<section {{ $attributes->class([
    'pc-card',
    $paddingClasses[$padding] ?? $paddingClasses['md'],
]) }}>
    {{ $slot }}
</section>
