@props(['value'])

<label {{ $attributes->merge(['class' => 'pc-label']) }}>
    {{ $value ?? $slot }}
</label>
