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
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[var(--pc-bg)] font-sans text-slate-900 antialiased">
        <div class="pc-auth-shell">
            <div class="pc-auth-orb left-[-5rem] top-[-4rem] h-40 w-40 bg-blue-200/60"></div>
            <div class="pc-auth-orb bottom-[-5rem] right-[-3rem] h-48 w-48 bg-cyan-100/70"></div>
            <div class="pc-auth-orb right-[12%] top-[18%] hidden h-32 w-32 bg-slate-200/70 sm:block"></div>

            <div class="relative w-full max-w-md">
                <div class="mb-6 flex items-center justify-between px-1">
                    <a href="/" wire:navigate class="inline-flex items-center gap-3">
                        <span class="pc-icon-badge h-12 w-12 rounded-[1.1rem] bg-[var(--pc-primary)] text-white shadow-[0_18px_42px_-26px_rgba(13,59,102,0.65)]">
                            <x-application-logo class="h-6 w-6 fill-current" />
                        </span>
                        <span class="text-left">
                            <span class="block text-lg font-semibold tracking-tight text-slate-950">ParentConnecta</span>
                            <span class="pc-eyebrow text-slate-400">Secure Access</span>
                        </span>
                    </a>

                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-[1rem] bg-white/80 text-slate-500 shadow-[0_18px_40px_-30px_rgba(15,23,42,0.18)] ring-1 ring-slate-200/70 backdrop-blur">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10 2a4.5 4.5 0 0 0-4.5 4.5v1.84c0 .58-.23 1.14-.64 1.55L4 10.8V12h12v-1.2l-.86-.9a2.2 2.2 0 0 1-.64-1.56V6.5A4.5 4.5 0 0 0 10 2Zm0 16a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 10 18Z" />
                        </svg>
                    </span>
                </div>

                <div class="pc-auth-card">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
