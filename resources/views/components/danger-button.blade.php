<button {{ $attributes->merge(['type' => 'submit', 'class' => 'pc-btn-danger']) }}>
    {{ $slot }}
</button>
