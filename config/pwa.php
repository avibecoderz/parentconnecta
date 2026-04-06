<?php

return [
    'name' => env('PWA_NAME', config('app.name', 'ParentConnecta')),
    'short_name' => env('PWA_SHORT_NAME', 'ParentConnecta'),
    'description' => env('PWA_DESCRIPTION', 'ParentConnecta school management platform.'),
    'start_url' => env('PWA_START_URL', '/'),
    'display' => env('PWA_DISPLAY', 'standalone'),
    'background_color' => env('PWA_BACKGROUND_COLOR', '#f8fafc'),
    'theme_color' => env('PWA_THEME_COLOR', '#0d3b66'),
    'cache_version' => env('PWA_CACHE_VERSION'),

    // Prefixes that should never be cached by the service worker.
    'protected_prefixes' => [
        '/dashboard',
        '/profile',
        '/school',
        '/admin',
        '/teacher',
        '/parent',
        '/livewire',
        '/broadcasting',
        '/sanctum',
        '/api',
        '/logout',
    ],

    // Public pages allowed to fall back to offline view when network fails.
    'offline_fallback_routes' => [
        '/',
        '/offline',
        '/login',
        '/forgot-password',
        '/register',
        '/reset-password',
        '/verify-email',
    ],
];
