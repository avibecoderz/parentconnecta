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
            $notificationsTableAvailable = \Illuminate\Support\Facades\Schema::hasTable('notifications');
            $recentNotifications = $notificationsTableAvailable && $user
                ? $user->notifications()
                    ->where('type', \App\Notifications\PlatformMessageNotification::class)
                    ->latest()
                    ->take(5)
                    ->get()
                : collect();
            $unreadNotificationCount = $recentNotifications->whereNull('read_at')->count();
            $notificationModalOpen = request()->query('notifications') == '1';
            $baseQuery = collect(request()->query())->except('notifications')->all();
            $closeNotificationUrl = request()->url().($baseQuery !== [] ? '?'.http_build_query($baseQuery) : '');
            $openNotificationUrl = request()->fullUrlWithQuery(['notifications' => 1]);
            $navigation = [
                ['label' => 'Dashboard', 'route' => 'school.admin.dashboard'],
                ['label' => 'Set Current Term', 'route' => 'school.admin.dashboard', 'params' => ['current-term' => 1], 'active' => request()->routeIs('school.admin.dashboard') && request()->query('current-term') == '1'],
                ['label' => 'Teachers', 'route' => 'school.admin.teachers.index'],
                ['label' => 'Parents', 'route' => 'school.admin.parents.index'],
                ['label' => 'Classes', 'route' => 'school.admin.classes.index'],
                ['label' => 'Students', 'route' => 'school.admin.students.index'],
                ['label' => 'Assignments', 'route' => 'school.admin.assignments.index'],
                ['label' => 'Payments', 'route' => 'school.admin.payments.index'],
            ];
        @endphp

        <div class="min-h-screen lg:grid lg:grid-cols-[18rem_minmax(0,1fr)]">
            <aside class="hidden border-r border-slate-200/80 bg-white/92 text-slate-700 backdrop-blur lg:flex lg:flex-col">
                <div class="border-b border-slate-200 px-6 py-6">
                    <a href="{{ route('school.admin.dashboard', ['slug' => $school->slug]) }}" wire:navigate class="inline-flex items-center text-lg font-semibold tracking-tight text-slate-950">
                        ParentConnecta
                    </a>
                    <p class="mt-3 text-sm font-medium text-slate-900">{{ $school->name }}</p>
                    <p class="mt-1 text-sm text-slate-400">/school/{{ $school->slug }}</p>
                </div>

                <nav class="flex-1 space-y-1 px-4 py-6">
                    @foreach ($navigation as $item)
                        <a
                            @php($isActive = $item['active'] ?? request()->routeIs($item['route']))
                            href="{{ route($item['route'], ['slug' => $school->slug, ...($item['params'] ?? [])]) }}"
                            wire:navigate
                            @class([
                                'flex items-center rounded-xl px-4 py-3 text-sm font-medium transition',
                                'bg-[var(--pc-primary)] text-white shadow-[0_18px_30px_-20px_rgba(13,59,102,0.55)]' => $isActive,
                                'text-slate-500 hover:bg-slate-100 hover:text-slate-900' => ! $isActive,
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
                            <p class="pc-eyebrow text-slate-400">School Admin</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ $openNotificationUrl }}" class="relative flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                                <span class="sr-only">Open notifications</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 2.5A4.5 4.5 0 0 0 5.5 7v1.23c0 .4-.12.8-.35 1.12L4 11v1h12v-1l-1.15-1.65a2 2 0 0 1-.35-1.12V7A4.5 4.5 0 0 0 10 2.5ZM7.75 14a2.25 2.25 0 0 0 4.5 0h-4.5Z" />
                                </svg>
                                @if ($unreadNotificationCount > 0)
                                    <span class="absolute -right-0.5 -top-0.5 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-semibold text-white">
                                        {{ min($unreadNotificationCount, 9) }}
                                    </span>
                                @endif
                            </a>

                            <a
                                href="{{ route('school.admin.dashboard', ['slug' => $school->slug]) }}"
                                wire:navigate
                                class="pc-btn-primary px-3 py-2 text-xs"
                            >
                                Dashboard
                            </a>
                        </div>
                    </div>

                    <div class="flex gap-2 overflow-x-auto border-t border-slate-100 px-4 py-3 sm:px-6">
                        @foreach ($navigation as $item)
                            <a
                                @php($isActive = $item['active'] ?? request()->routeIs($item['route']))
                                href="{{ route($item['route'], ['slug' => $school->slug, ...($item['params'] ?? [])]) }}"
                                wire:navigate
                                @class([
                                    'whitespace-nowrap rounded-full px-3 py-2 text-sm font-medium transition',
                                    'bg-[var(--pc-primary)] text-white' => $isActive,
                                    'bg-slate-100 text-slate-600' => ! $isActive,
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
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="pc-page-header">
                                        {{ $header }}
                                    </div>
                                </div>

                                <a href="{{ $openNotificationUrl }}" class="relative hidden h-12 w-12 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900 lg:flex">
                                    <span class="sr-only">Open notifications</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10 2.5A4.5 4.5 0 0 0 5.5 7v1.23c0 .4-.12.8-.35 1.12L4 11v1h12v-1l-1.15-1.65a2 2 0 0 1-.35-1.12V7A4.5 4.5 0 0 0 10 2.5ZM7.75 14a2.25 2.25 0 0 0 4.5 0h-4.5Z" />
                                    </svg>
                                    @if ($unreadNotificationCount > 0)
                                        <span class="absolute -right-0.5 -top-0.5 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-semibold text-white">
                                            {{ min($unreadNotificationCount, 9) }}
                                        </span>
                                    @endif
                                </a>
                            </div>
                        </div>
                    </header>
                @endif

                @if ($notificationModalOpen)
                    <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-6">
                        <a
                            href="{{ $closeNotificationUrl }}"
                            class="fixed inset-0 bg-slate-950/45 backdrop-blur-[2px]"
                            aria-label="Close notifications"
                        ></a>

                        <div class="relative mx-auto w-full max-w-2xl">
                            <div class="pc-modal-panel px-6 py-6 sm:px-7">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="pc-eyebrow text-slate-400">School Admin</p>
                                        <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Recent notifications</h2>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">Latest announcements and system alerts from the super admin workspace.</p>
                                    </div>

                                    <a
                                        href="{{ $closeNotificationUrl }}"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                                    >
                                        <span class="sr-only">Close</span>
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.22 5.22a.75.75 0 0 1 1.06 0L10 8.94l3.72-3.72a.75.75 0 1 1 1.06 1.06L11.06 10l3.72 3.72a.75.75 0 0 1-1.06 1.06L10 11.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06L8.94 10 5.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>

                                <div class="mt-6 flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-slate-600">
                                            {{ $recentNotifications->count() }} recent
                                        </span>
                                        @if ($unreadNotificationCount > 0)
                                            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-rose-700">
                                                {{ $unreadNotificationCount }} unread
                                            </span>
                                        @endif
                                    </div>

                                    @if ($unreadNotificationCount > 0)
                                        <form method="POST" action="{{ route('school.admin.notifications.read-all', ['slug' => $school->slug]) }}">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--pc-primary)] transition hover:text-[var(--pc-primary-deep)]">
                                                Mark all read
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <a
                                        href="{{ route('school.admin.notifications.index', ['slug' => $school->slug]) }}"
                                        wire:navigate
                                        class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--pc-primary)] transition hover:text-[var(--pc-primary-deep)]"
                                    >
                                        View all notifications
                                    </a>
                                </div>

                                @unless ($notificationsTableAvailable)
                                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                        Notifications will appear here after the notifications table is migrated.
                                    </div>
                                @else
                                    <div class="mt-6 max-h-[70vh] space-y-3 overflow-y-auto">
                                        @forelse ($recentNotifications as $notification)
                                            @php($isAlert = data_get($notification->data, 'notification_type') === 'system_alert')
                                            <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-4 py-4">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span @class([
                                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.12em]',
                                                            'bg-rose-100 text-rose-700' => $isAlert,
                                                            'bg-sky-100 text-sky-700' => ! $isAlert,
                                                        ])>
                                                            {{ $isAlert ? 'System Alert' : 'Announcement' }}
                                                        </span>
                                                        @if (blank($notification->read_at))
                                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                                New
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if (blank($notification->read_at))
                                                        <form method="POST" action="{{ route('school.admin.notifications.read', ['slug' => $school->slug, 'notification' => $notification->id]) }}">
                                                            @csrf
                                                            <button type="submit" class="shrink-0 text-xs font-semibold uppercase tracking-[0.12em] text-[var(--pc-primary)] transition hover:text-[var(--pc-primary-deep)]">
                                                                Mark read
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>

                                                <h3 class="mt-4 text-base font-semibold text-slate-900">
                                                    {{ data_get($notification->data, 'subject', 'Platform update') }}
                                                </h3>
                                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                                    {{ data_get($notification->data, 'message') }}
                                                </p>
                                                <p class="mt-3 text-xs text-slate-500">
                                                    {{ data_get($notification->data, 'sender_name', 'Super Admin') }} • {{ $notification->created_at?->format('M j, g:i A') }}
                                                </p>
                                            </article>
                                        @empty
                                            <div class="rounded-[1.5rem] border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">
                                                No recent notifications yet.
                                            </div>
                                        @endforelse
                                    </div>
                                @endunless
                            </div>
                        </div>
                    </div>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
