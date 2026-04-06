<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' | '.config('app.name', 'ParentConnecta') : config('app.name', 'ParentConnecta') }}</title>
        <meta name="theme-color" content="{{ config('pwa.theme_color', '#0d3b66') }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="{{ config('pwa.short_name', config('app.name', 'ParentConnecta')) }}">
        <link rel="manifest" href="{{ route('pwa.manifest') }}">
        <link rel="apple-touch-icon" href="{{ asset('pwa/icon-192.png') }}">
        <link rel="mask-icon" href="{{ asset('pwa/safari-pinned-tab.svg') }}" color="{{ config('pwa.theme_color', '#0d3b66') }}">
        <meta name="msapplication-config" content="{{ asset('pwa/browserconfig.xml') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[var(--pc-bg)] font-sans text-slate-900 antialiased">
        @php
            $school = $currentSchool ?? request()->attributes->get('currentSchool');
            $user = auth()->user();
            $navigation = [
                ['label' => 'Dashboard', 'route' => 'school.parent.dashboard', 'pattern' => 'school.parent.dashboard'],
                ['label' => 'My Pupils', 'route' => 'school.parent.pupils.index', 'pattern' => 'school.parent.pupils.*'],
                ['label' => 'Payments', 'route' => 'school.parent.payments.index', 'pattern' => 'school.parent.payments.*'],
            ];
        @endphp

        <div class="min-h-screen lg:grid lg:grid-cols-[18rem_minmax(0,1fr)]">
            <aside class="hidden border-r border-slate-200/80 bg-white/92 text-slate-700 backdrop-blur lg:flex lg:flex-col">
                <div class="border-b border-slate-200 px-6 py-6">
                    <a href="{{ route('school.parent.dashboard', ['slug' => $school->slug]) }}" wire:navigate class="inline-flex items-center text-lg font-semibold tracking-tight text-slate-950">
                        ParentConnecta
                    </a>
                    <p class="mt-3 text-sm font-medium text-slate-900">{{ $school->name }}</p>
                    <p class="mt-1 text-sm text-slate-400">/school/{{ $school->slug }}</p>
                    <p class="mt-4 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-[var(--pc-primary)]">
                        Parent workspace
                    </p>
                </div>

                <nav class="flex-1 space-y-1 px-4 py-6">
                    @foreach ($navigation as $item)
                        <a
                            href="{{ route($item['route'], ['slug' => $school->slug]) }}"
                            wire:navigate
                            @class([
                                'flex items-center rounded-xl px-4 py-3 text-sm font-medium transition',
                                'bg-[var(--pc-primary)] text-white shadow-[0_18px_30px_-20px_rgba(13,59,102,0.55)]' => request()->routeIs($item['pattern']),
                                'text-slate-500 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs($item['pattern']),
                            ])
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="border-t border-slate-200 px-6 py-5">
                    <p class="text-sm font-medium text-slate-900">{{ $user?->name }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $user?->email }}</p>

                    <div class="mt-4 flex flex-col gap-2">
                        <a
                            href="{{ route('profile') }}"
                            wire:navigate
                            class="pc-btn-secondary"
                        >
                            Profile
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="pc-btn-primary w-full"
                            >
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="border-b border-slate-200/80 bg-white/95 backdrop-blur lg:hidden">
                    <div class="flex items-center justify-between px-4 py-4 sm:px-6">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $school->name }}</p>
                            <p class="pc-eyebrow text-slate-400">Parent</p>
                        </div>

                        <a
                            href="{{ route('school.parent.dashboard', ['slug' => $school->slug]) }}"
                            wire:navigate
                            class="pc-btn-primary px-3 py-2 text-xs"
                        >
                            Dashboard
                        </a>
                    </div>

                    <div class="flex gap-2 overflow-x-auto border-t border-slate-100 px-4 py-3 sm:px-6">
                        @foreach ($navigation as $item)
                            <a
                                href="{{ route($item['route'], ['slug' => $school->slug]) }}"
                                wire:navigate
                                @class([
                                    'whitespace-nowrap rounded-full px-3 py-2 text-sm font-medium transition',
                                    'bg-[var(--pc-primary)] text-white' => request()->routeIs($item['pattern']),
                                    'bg-slate-100 text-slate-600' => ! request()->routeIs($item['pattern']),
                                ])
                            >
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </header>

                @if (isset($header))
                    <header class="py-5 sm:py-6">
                        <div class="pc-shell">
                            <div class="pc-page-header">
                                {{ $header }}
                            </div>
                        </div>
                    </header>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
