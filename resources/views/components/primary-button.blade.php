<button {{ $attributes->merge(['type' => 'submit', 'class' => 'pc-btn-primary']) }}>
    {{ $slot }}
</button>
