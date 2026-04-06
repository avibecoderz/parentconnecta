@props([
    'placeholder' => 'Search...',
    'wrapperClass' => '',
])

@php
    $inputAttributes = $attributes->except('class');
@endphp

<label @class(['relative block', $wrapperClass])>
    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
        @isset($icon)
            {{ $icon }}
        @else
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.47 9.77l3.63 3.63a1 1 0 0 0 1.4-1.4l-3.62-3.63A5.5 5.5 0 0 0 8.5 3Zm-3.5 5.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z" clip-rule="evenodd" />
            </svg>
        @endisset
    </span>

    <input
        {{ $inputAttributes->merge([
            'type' => 'search',
            'placeholder' => $placeholder,
            'class' => 'pc-input border-0 pl-11 pr-4',
        ]) }}
    >
</label>
