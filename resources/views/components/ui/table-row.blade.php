@props([
    'columns' => 2,
])

<div {{ $attributes->merge(['class' => 'grid gap-3 px-4 py-4']) }} style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));">
    {{ $slot }}
</div>
