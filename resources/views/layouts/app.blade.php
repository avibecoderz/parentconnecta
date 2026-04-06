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

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
        <style>
            .pc-admin-sidebar {
                display: none;
                padding: 1.5rem 1.25rem;
                color: #f8fafc;
                border-right: 1px solid #1e293b;
                background-color: #020617;
                background-image: linear-gradient(180deg, #0f172a 0%, #020617 100%);
            }

            .pc-admin-sidebar-brand {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem;
                border: 1px solid #1e293b;
                border-radius: 1.4rem;
                background: rgba(15, 23, 42, 0.82);
                box-shadow: 0 24px 48px -32px rgba(2, 6, 23, 0.85);
            }

            .pc-admin-sidebar-link {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.75rem 1rem;
                border-radius: 1.15rem;
                font-size: 0.875rem;
                font-weight: 600;
                transition: background-color 150ms ease, color 150ms ease, box-shadow 150ms ease;
            }

            .pc-admin-sidebar-link:focus-visible,
            .pc-admin-sidebar-secondary-btn:focus-visible,
            .pc-admin-sidebar-primary-btn:focus-visible {
                outline: 2px solid rgba(125, 211, 252, 0.75);
                outline-offset: 2px;
            }

            .pc-admin-sidebar-link-active {
                color: #0f172a;
                background: #ffffff;
                box-shadow: 0 22px 40px -28px rgba(255, 255, 255, 0.45);
            }

            .pc-admin-sidebar-link-idle {
                color: #cbd5e1;
            }

            .pc-admin-sidebar-link-idle:hover {
                color: #ffffff;
                background: rgba(15, 23, 42, 0.9);
            }

            .pc-admin-sidebar-icon {
                display: inline-flex;
                height: 2.5rem;
                width: 2.5rem;
                flex-shrink: 0;
                align-items: center;
                justify-content: center;
                border: 1px solid #1e293b;
                border-radius: 0.95rem;
                background: #0f172a;
            }

            .pc-admin-sidebar-card {
                margin-top: auto;
                padding: 1rem;
                border: 1px solid #1e293b;
                border-radius: 1.4rem;
                background: rgba(15, 23, 42, 0.88);
                box-shadow: 0 24px 48px -34px rgba(2, 6, 23, 0.9);
            }

            .pc-admin-sidebar-muted {
                color: #94a3b8;
            }

            .pc-admin-sidebar-secondary-btn,
            .pc-admin-sidebar-primary-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                padding: 0.625rem 1rem;
                border-radius: 1rem;
                font-size: 0.875rem;
                font-weight: 600;
                transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease;
            }

            .pc-admin-sidebar-secondary-btn {
                color: #f8fafc;
                border: 1px solid #334155;
                background: #1e293b;
            }

            .pc-admin-sidebar-secondary-btn:hover {
                background: #334155;
            }

            .pc-admin-sidebar-primary-btn {
                color: #0f172a;
                background: #ffffff;
            }

            .pc-admin-sidebar-primary-btn:hover {
                background: #e2e8f0;
            }

            @media (min-width: 1024px) {
                .pc-admin-sidebar {
                    display: flex;
                    flex-direction: column;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        @php
            /** @var \App\Models\User|null $layoutUser */
            $layoutUser = auth()->user();
            $isSuperAdminLayout = (bool) $layoutUser?->hasRole('super_admin');
            $superAdminLinks = [
                [
                    'label' => 'Dashboard',
                    'route' => 'super-admin.dashboard',
                    'active' => request()->routeIs('super-admin.dashboard'),
                    'icon' => 'dashboard',
                ],
                [
                    'label' => 'Add School',
                    'route' => 'super-admin.schools.index',
                    'params' => ['create' => 1],
                    'active' => request()->routeIs('super-admin.schools.*') && request()->boolean('create'),
                    'icon' => 'add-school',
                ],
                [
                    'label' => 'View Schools',
                    'route' => 'super-admin.schools.index',
                    'params' => [],
                    'active' => request()->routeIs('super-admin.schools.*') && ! request()->boolean('create'),
                    'icon' => 'schools',
                ],
                [
                    'label' => 'Notifications',
                    'route' => 'super-admin.notifications.index',
                    'params' => [],
                    'active' => request()->routeIs('super-admin.notifications.*'),
                    'icon' => 'notifications',
                ],
                [
                    'label' => 'Payments',
                    'route' => 'super-admin.schools.index',
                    'params' => [],
                    'active' => false,
                    'icon' => 'payments',
                ],
                [
                    'label' => 'School Admins',
                    'route' => 'super-admin.school-admins.index',
                    'params' => [],
                    'active' => request()->routeIs('super-admin.school-admins.*'),
                    'icon' => 'school-admins',
                ],
                [
                    'label' => 'Users',
                    'route' => 'super-admin.users.index',
                    'params' => [],
                    'active' => request()->routeIs('super-admin.users.*'),
                    'icon' => 'users',
                ],
            ];
        @endphp

        <div class="min-h-screen bg-[var(--pc-bg)] text-slate-900">
            @if ($isSuperAdminLayout)
                <div class="lg:grid lg:min-h-screen lg:grid-cols-[18rem_minmax(0,1fr)]">
                    <aside class="pc-admin-sidebar">
                        <a href="{{ route('super-admin.dashboard') }}" wire:navigate class="pc-admin-sidebar-brand">
                            <span class="flex h-12 w-12 items-center justify-center rounded-[1rem] bg-gradient-to-br from-sky-400 via-blue-500 to-indigo-600 shadow-[0_18px_40px_-20px_rgba(59,130,246,0.75)]">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z" />
                                </svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-base font-semibold tracking-tight">ParentConnecta</span>
                                <span class="pc-admin-sidebar-muted mt-1 block text-[11px] font-semibold uppercase tracking-[0.28em]">Super Admin</span>
                            </span>
                        </a>

                        <nav class="mt-8 space-y-2" aria-label="Super admin navigation">
                            @foreach ($superAdminLinks as $link)
                                <a
                                    href="{{ route($link['route'], $link['params'] ?? []) }}"
                                    wire:navigate
                                    @if ($link['active']) aria-current="page" @endif
                                    @class([
                                        'pc-admin-sidebar-link',
                                        'pc-admin-sidebar-link-active' => $link['active'],
                                        'pc-admin-sidebar-link-idle' => ! $link['active'],
                                    ])
                                >
                                    <span class="pc-admin-sidebar-icon">
                                        @if ($link['icon'] === 'dashboard')
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3.5 4.5A1.5 1.5 0 0 1 5 3h10a1.5 1.5 0 0 1 1.5 1.5v11A1.5 1.5 0 0 1 15 17H5a1.5 1.5 0 0 1-1.5-1.5v-11ZM6 5v4h3V5H6Zm5 0v7h3V5h-3Zm-5 6v4h3v-4H6Zm5 3h3v1h-3v-1Z" /></svg>
                                        @elseif ($link['icon'] === 'add-school')
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 3 3 6.5V8h14V6.5L10 3Zm-5.5 6.5a.5.5 0 0 0-.5.5v5a1 1 0 0 0 1 1H6v-4a1 1 0 1 1 2 0v4h4v-4a1 1 0 1 1 2 0v4h1a1 1 0 0 0 1-1v-5a.5.5 0 0 0-.5-.5h-11Z" /><path d="M15.25 2.75a.75.75 0 0 1 .75.75v1h1a.75.75 0 0 1 0 1.5h-1v1a.75.75 0 0 1-1.5 0v-1h-1a.75.75 0 0 1 0-1.5h1v-1a.75.75 0 0 1 .75-.75Z" /></svg>
                                        @elseif ($link['icon'] === 'schools')
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 3 3 6.5V8h14V6.5L10 3Zm-5.5 6.5a.5.5 0 0 0-.5.5v5a1 1 0 0 0 1 1H6v-4a1 1 0 1 1 2 0v4h4v-4a1 1 0 1 1 2 0v4h1a1 1 0 0 0 1-1v-5a.5.5 0 0 0-.5-.5h-11Z" /></svg>
                                        @elseif ($link['icon'] === 'payments')
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 5.75A1.75 1.75 0 0 1 4.75 4h10.5A1.75 1.75 0 0 1 17 5.75v8.5A1.75 1.75 0 0 1 15.25 16H4.75A1.75 1.75 0 0 1 3 14.25v-8.5Zm1.5 1.25v1.5h11V7h-11Zm0 3v4.25a.25.25 0 0 0 .25.25h10.5a.25.25 0 0 0 .25-.25V10h-11Zm2 1.5a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" /></svg>
                                        @elseif ($link['icon'] === 'notifications')
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 2.5a4.5 4.5 0 0 0-4.5 4.5v1.23c0 .4-.12.8-.35 1.12L4 11v1h12v-1l-1.15-1.65a2 2 0 0 1-.35-1.12V7A4.5 4.5 0 0 0 10 2.5ZM7.75 14a2.25 2.25 0 0 0 4.5 0h-4.5Z" /></svg>
                                        @elseif ($link['icon'] === 'school-admins')
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-6 13.5A3.5 3.5 0 0 1 7.5 12h5a3.5 3.5 0 0 1 3.5 3.5V17H4v-1.5Zm10.75-7.25a.75.75 0 0 1 .75.75v1.25h1.25a.75.75 0 0 1 0 1.5H15.5V13a.75.75 0 0 1-1.5 0v-1.25h-1.25a.75.75 0 0 1 0-1.5H14V9a.75.75 0 0 1 .75-.75Z" /></svg>
                                        @else
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.5 6.5a2.5 2.5 0 1 1 5 0 2.5 2.5 0 0 1-5 0Zm6 1a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM3 14.25A3.25 3.25 0 0 1 6.25 11h5.5A3.25 3.25 0 0 1 15 14.25V16H3v-1.75Zm10 1.75h4v-.75A2.75 2.75 0 0 0 14.25 12h-1.13c.6.66.96 1.54.96 2.5V16Z" /></svg>
                                        @endif
                                    </span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>

                        <div class="pc-admin-sidebar-card">
                            <p class="pc-admin-sidebar-muted text-xs font-semibold uppercase tracking-[0.24em]">Signed in as</p>
                            <p class="mt-3 text-sm font-semibold text-white">{{ $layoutUser?->name }}</p>
                            <p class="pc-admin-sidebar-muted mt-1 truncate text-sm">{{ $layoutUser?->email }}</p>
                            <div class="mt-4 flex flex-col gap-2">
                                <a href="{{ route('profile') }}" wire:navigate class="pc-admin-sidebar-secondary-btn">
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="pc-admin-sidebar-primary-btn">
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </aside>

                    <div class="min-w-0">
                        <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur">
                            <div class="pc-shell py-4 sm:py-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">Control Center</p>
                                        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Super Admin Workspace</h1>
                                    </div>

                                    <div class="rounded-[1.3rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                        <span class="font-semibold text-slate-900">{{ $layoutUser?->name }}</span>
                                        <span class="mx-2 text-slate-300">•</span>
                                        <span>{{ now()->format('M j, Y') }}</span>
                                    </div>
                                </div>

                                <div class="mt-4 flex gap-2 overflow-x-auto lg:hidden">
                                    @foreach ($superAdminLinks as $link)
                                        <a
                                            href="{{ route($link['route'], $link['params'] ?? []) }}"
                                            wire:navigate
                                            @if ($link['active']) aria-current="page" @endif
                                            @class([
                                                'whitespace-nowrap rounded-full px-4 py-2.5 text-sm font-semibold transition',
                                                'bg-[var(--pc-primary)] text-white' => $link['active'],
                                                'bg-slate-100 text-slate-600' => ! $link['active'],
                                            ])
                                        >
                                            {{ $link['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </header>

                        <main class="pb-12 pt-5 sm:pt-7">
                            @if (isset($header))
                                <div class="pc-shell mb-5 sm:mb-6">
                                    <header class="pc-page-header">
                                        {{ $header }}
                                    </header>
                                </div>
                            @endif

                            {{ $slot }}
                        </main>
                    </div>
                </div>
            @else
                <livewire:layout.navigation />

                <main class="pb-24 pt-5 sm:pb-12 sm:pt-7">
                    @if (isset($header))
                        <div class="pc-shell mb-5 sm:mb-6">
                            <header class="pc-page-header">
                                {{ $header }}
                            </header>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            @endif
        </div>
        @stack('scripts')
    </body>
</html>
