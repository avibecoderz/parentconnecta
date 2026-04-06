@props([
    'paginator',
])

@php
    $pageNumbers = range(1, $paginator->lastPage());
    $windowStart = max(1, $paginator->currentPage() - 1);
    $windowEnd = min($paginator->lastPage(), $windowStart + 2);
    $windowStart = max(1, $windowEnd - 2);
    $visiblePages = array_filter($pageNumbers, fn (int $page) => $page >= $windowStart && $page <= $windowEnd);
@endphp

@if ($paginator->lastPage() > 1)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
        @if ($paginator->previousPageUrl())
            <a
                href="{{ $paginator->previousPageUrl() }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200"
            >
                <span class="sr-only">Previous page</span>
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M11.78 4.22a.75.75 0 0 1 0 1.06L7.06 10l4.72 4.72a.75.75 0 0 1-1.06 1.06l-5.25-5.25a.75.75 0 0 1 0-1.06l5.25-5.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                </svg>
            </a>
        @else
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 opacity-50" aria-disabled="true">
                <span class="sr-only">Previous page</span>
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M11.78 4.22a.75.75 0 0 1 0 1.06L7.06 10l4.72 4.72a.75.75 0 0 1-1.06 1.06l-5.25-5.25a.75.75 0 0 1 0-1.06l5.25-5.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                </svg>
            </span>
        @endif

        @foreach ($visiblePages as $page)
            <a
                href="{{ $paginator->url($page) }}"
                @class([
                    'inline-flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-sm font-semibold transition',
                    'bg-[var(--pc-primary)] text-white shadow-[0_18px_30px_-20px_rgba(13,59,102,0.65)]' => $paginator->currentPage() === $page,
                    'bg-slate-100 text-slate-700 hover:bg-slate-200' => $paginator->currentPage() !== $page,
                ])
            >
                {{ $page }}
            </a>
        @endforeach

        @if ($paginator->nextPageUrl())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200"
            >
                <span class="sr-only">Next page</span>
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </a>
        @else
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 opacity-50" aria-disabled="true">
                <span class="sr-only">Next page</span>
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </span>
        @endif
    </div>
@endif
