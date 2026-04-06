<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        $manifest = [
            'name' => (string) config('pwa.name', config('app.name', 'ParentConnecta')),
            'short_name' => (string) config('pwa.short_name', 'ParentConnecta'),
            'description' => (string) config('pwa.description', ''),
            'start_url' => (string) config('pwa.start_url', '/'),
            'scope' => '/',
            'display' => (string) config('pwa.display', 'standalone'),
            'background_color' => (string) config('pwa.background_color', '#f8fafc'),
            'theme_color' => (string) config('pwa.theme_color', '#0d3b66'),
            'lang' => str_replace('_', '-', app()->getLocale()),
            'icons' => [
                [
                    'src' => asset('pwa/icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('pwa/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('pwa/icon-maskable-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
                [
                    'src' => asset('pwa/icon-maskable-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ];

        return response()->json(
            $manifest,
            200,
            [
                'Content-Type' => 'application/manifest+json',
                'Cache-Control' => 'public, max-age=3600',
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    public function serviceWorker(): Response
    {
        $cacheVersion = (string) (config('pwa.cache_version') ?: $this->defaultCacheVersion());

        return response()
            ->view('pwa.service-worker', [
                'cacheVersion' => $cacheVersion,
                'protectedPrefixes' => array_values((array) config('pwa.protected_prefixes', [])),
                'offlineFallbackRoutes' => array_values((array) config('pwa.offline_fallback_routes', ['/offline'])),
                'offlineUrl' => '/offline',
                'precacheUrls' => [
                    '/offline',
                    '/favicon.ico',
                    '/robots.txt',
                    '/pwa/icon-192.png',
                    '/pwa/icon-512.png',
                    '/pwa/icon-maskable-192.png',
                    '/pwa/icon-maskable-512.png',
                ],
            ])
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Service-Worker-Allowed', '/');
    }

    protected function defaultCacheVersion(): string
    {
        $buildManifest = public_path('build/manifest.json');

        if (is_file($buildManifest)) {
            return 'build-'.sha1_file($buildManifest);
        }

        return 'app-'.sha1((string) config('app.version', config('app.name', 'parentconnect')));
    }
}
