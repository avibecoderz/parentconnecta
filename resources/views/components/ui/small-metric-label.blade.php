@props(['value'])

<p {{ $attributes->merge(['class' => 'pc-eyebrow']) }}>
    {{ $value }}
</p>
