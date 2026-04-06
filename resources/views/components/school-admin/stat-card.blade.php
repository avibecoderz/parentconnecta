@props([
    'label',
    'value',
    'hint' => null,
])

<x-ui.stat-card
    :label="$label"
    :value="$value"
    :hint="$hint"
    {{ $attributes }}
/>
