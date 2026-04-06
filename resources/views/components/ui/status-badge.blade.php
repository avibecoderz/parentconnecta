@props([
    'status',
])

@php
    $normalized = strtolower((string) $status);

    $classes = match ($normalized) {
        'active', 'paid', 'success' => 'bg-emerald-50 text-emerald-700',
        'inactive', 'partial', 'warning' => 'bg-amber-50 text-amber-700',
        'suspended', 'unpaid', 'failed', 'danger' => 'bg-rose-50 text-rose-700',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full px-2.5 py-1 text-[11px] font-semibold capitalize ring-1 ring-black/5 '.$classes]) }}>
    {{ str_replace('_', ' ', $normalized) }}
</span>
