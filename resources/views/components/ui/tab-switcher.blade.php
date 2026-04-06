@props([
    'tabs' => [],
    'active' => null,
    'action' => null,
])

<div {{ $attributes->merge(['class' => 'inline-flex rounded-[0.95rem] bg-slate-100 p-1']) }}>
    @foreach ($tabs as $value => $label)
        <button
            type="button"
            @if ($action) wire:click="{{ $action }}({{ \Illuminate\Support\Js::from((string) $value) }})" @endif
            @class([
                'rounded-[0.8rem] px-4 py-2 text-xs font-semibold transition',
                'bg-white text-slate-900 shadow-sm' => (string) $active === (string) $value,
                'text-slate-500 hover:text-slate-700' => (string) $active !== (string) $value,
            ])
        >
            {{ $label }}
        </button>
    @endforeach
</div>
