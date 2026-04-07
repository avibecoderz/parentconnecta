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
                <div class="mb-6 flex items-center justify-center px-1">
                    <a href="/" wire:navigate class="inline-flex items-center">
                        <span class="text-center">
                            <span class="block text-lg font-semibold tracking-tight text-slate-950">ParentConnecta</span>
                            <span class="pc-eyebrow text-slate-400">Secure Access</span>
                        </span>
                    </a>
                </div>

                <div class="pc-auth-card">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
