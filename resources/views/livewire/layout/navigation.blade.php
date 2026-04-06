<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
};
?>

@php
    $user = auth()->user();
    $isSuperAdmin = (bool) $user?->hasRole('super_admin');
    $dashboardRoute = $isSuperAdmin ? route('super-admin.dashboard') : route('dashboard');

    $primaryLinks = $isSuperAdmin
        ? [
            ['label' => 'Dashboard', 'href' => route('super-admin.dashboard'), 'active' => request()->routeIs('super-admin.dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Schools', 'href' => route('super-admin.schools.index'), 'active' => request()->routeIs('super-admin.schools.*'), 'icon' => 'school'],
            ['label' => 'Analytics', 'href' => route('super-admin.dashboard'), 'active' => false, 'icon' => 'analytics'],
            ['label' => 'Settings', 'href' => route('profile'), 'active' => request()->routeIs('profile'), 'icon' => 'settings'],
        ]
        : [
            ['label' => 'Home', 'href' => $dashboardRoute, 'active' => request()->routeIs('dashboard'), 'icon' => 'home'],
            ['label' => 'Profile', 'href' => route('profile'), 'active' => request()->routeIs('profile'), 'icon' => 'profile'],
        ];

    $desktopLinks = $isSuperAdmin ? [] : $primaryLinks;
@endphp

<nav x-data="{ menuOpen: false }" @class(['backdrop-blur', 'bg-transparent' => $isSuperAdmin, 'border-b border-slate-200/80 bg-[#f4f6f8]/95' => ! $isSuperAdmin])>
    <div class="pc-shell">
        <div class="flex items-center justify-between gap-4 py-4 sm:py-5">
            <a href="{{ $dashboardRoute }}" wire:navigate class="flex min-w-0 items-center gap-3">
                <div @class([
                    'flex shrink-0 items-center justify-center',
                    'h-12 w-12 rounded-[1.15rem] bg-[#dce7ff] text-[#0f47b8]' => $isSuperAdmin,
                    'h-11 w-11 rounded-2xl bg-[var(--pc-primary)] text-white shadow-[0_14px_32px_-18px_rgba(13,59,102,0.85)]' => ! $isSuperAdmin,
                ])>
                    @if ($isSuperAdmin)
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z" /></svg>
                    @else
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2 4 6v6c0 5.25 3.44 10.01 8 11.5 4.56-1.49 8-6.25 8-11.5V6l-8-4Z" /></svg>
                    @endif
                </div>

                <div class="min-w-0">
                    <p class="truncate text-[1.05rem] font-semibold tracking-tight text-[#0f47b8]">ParentConnecta</p>
                    @if (! $isSuperAdmin)
                        <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-slate-400">Platform Workspace</p>
                    @endif
                </div>
            </a>

            <div class="hidden items-center gap-3 sm:flex">
                @if (! $isSuperAdmin && count($desktopLinks) > 0)
                    <div class="flex items-center gap-1 rounded-2xl bg-white/80 p-1 shadow-[0_18px_50px_-40px_rgba(15,23,42,0.35)] ring-1 ring-slate-200/80">
                        @foreach ($desktopLinks as $link)
                            <a href="{{ $link['href'] }}" wire:navigate @class([
                                'inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-semibold transition',
                                'bg-[var(--pc-primary)] text-white shadow-[0_14px_28px_-20px_rgba(13,59,102,0.75)]' => $link['active'],
                                'text-slate-500 hover:bg-slate-50 hover:text-slate-800' => ! $link['active'],
                            ])>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="relative">
                    <button type="button" @click="menuOpen = ! menuOpen" @class([
                        'inline-flex items-center gap-3 transition hover:-translate-y-0.5',
                        'rounded-[1.15rem] bg-white px-2 py-2 shadow-[0_16px_44px_-32px_rgba(15,23,42,0.32)] ring-1 ring-slate-200/90' => $isSuperAdmin,
                        'rounded-2xl bg-white px-3 py-2 shadow-[0_18px_50px_-40px_rgba(15,23,42,0.35)] ring-1 ring-slate-200/80' => ! $isSuperAdmin,
                    ])>
                        @if ($isSuperAdmin)
                            <span class="relative block h-11 w-11 overflow-hidden rounded-[1rem] bg-gradient-to-br from-[#ffe2d6] via-[#f7f4ef] to-[#dbe6ef] ring-1 ring-slate-300/80">
                                <span class="absolute left-1/2 top-[20%] h-[36%] w-[42%] -translate-x-1/2 rounded-full bg-[#f4c4a2]"></span>
                                <span class="absolute left-[25%] top-[12%] h-[52%] w-[26%] rounded-r-full bg-[#2f2018]"></span>
                                <span class="absolute right-[25%] top-[12%] h-[52%] w-[26%] rounded-l-full bg-[#2f2018]"></span>
                                <span class="absolute left-[36%] top-[35%] h-[7%] w-[8%] rounded-full bg-[#1f2937]"></span>
                                <span class="absolute right-[36%] top-[35%] h-[7%] w-[8%] rounded-full bg-[#1f2937]"></span>
                                <span class="absolute left-1/2 top-[48%] h-[6%] w-[14%] -translate-x-1/2 rounded-full bg-[#d97745]"></span>
                                <span class="absolute left-1/2 bottom-0 h-[40%] w-[90%] -translate-x-1/2 rounded-t-[1rem] bg-[#2f8a85]"></span>
                            </span>
                        @else
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-sm font-semibold text-slate-700">
                                {{ str($user?->name ?? 'PC')->explode(' ')->filter()->take(2)->map(fn (string $part) => str($part)->substr(0, 1)->upper())->join('') ?: 'PC' }}
                            </span>
                            <span class="hidden text-left lg:block">
                                <span class="block max-w-[11rem] truncate text-sm font-semibold text-slate-900">{{ $user?->name }}</span>
                                <span class="block max-w-[11rem] truncate text-xs text-slate-500">{{ $user?->email }}</span>
                            </span>
                        @endif
                    </button>

                    <div x-cloak x-show="menuOpen" @click.outside="menuOpen = false" x-transition.opacity class="absolute right-0 z-50 mt-3 w-56 rounded-2xl bg-white p-2 shadow-[0_28px_80px_-48px_rgba(15,23,42,0.5)] ring-1 ring-slate-200/80">
                        <a href="{{ route('profile') }}" wire:navigate class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Profile</a>
                        <button wire:click="logout" class="flex w-full items-center rounded-xl px-3 py-2.5 text-left text-sm font-medium text-rose-600 hover:bg-rose-50">Log Out</button>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:hidden">
                @if ($isSuperAdmin)
                    <button type="button" @click="menuOpen = ! menuOpen" class="relative block h-12 w-12 overflow-hidden rounded-[1rem] bg-gradient-to-br from-[#ffe2d6] via-[#f7f4ef] to-[#dbe6ef] ring-1 ring-slate-300/80 shadow-[0_16px_44px_-32px_rgba(15,23,42,0.32)]">
                        <span class="absolute left-1/2 top-[20%] h-[36%] w-[42%] -translate-x-1/2 rounded-full bg-[#f4c4a2]"></span>
                        <span class="absolute left-[25%] top-[12%] h-[52%] w-[26%] rounded-r-full bg-[#2f2018]"></span>
                        <span class="absolute right-[25%] top-[12%] h-[52%] w-[26%] rounded-l-full bg-[#2f2018]"></span>
                        <span class="absolute left-[36%] top-[35%] h-[7%] w-[8%] rounded-full bg-[#1f2937]"></span>
                        <span class="absolute right-[36%] top-[35%] h-[7%] w-[8%] rounded-full bg-[#1f2937]"></span>
                        <span class="absolute left-1/2 top-[48%] h-[6%] w-[14%] -translate-x-1/2 rounded-full bg-[#d97745]"></span>
                        <span class="absolute left-1/2 bottom-0 h-[40%] w-[90%] -translate-x-1/2 rounded-t-[1rem] bg-[#2f8a85]"></span>
                        <span class="sr-only">Open profile menu</span>
                    </button>
                @else
                    <button type="button" @click="menuOpen = ! menuOpen" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-slate-700 shadow-[0_18px_50px_-40px_rgba(15,23,42,0.35)] ring-1 ring-slate-200/80">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 5.75A.75.75 0 0 1 3.75 5h12.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 5.75Zm0 4.25a.75.75 0 0 1 .75-.75h12.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 10Zm0 4.25a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 0 1.5h-8.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" /></svg>
                    </button>
                @endif
            </div>
        </div>

        <div x-cloak x-show="menuOpen" @click.outside="menuOpen = false" class="pb-4 sm:hidden">
            <div class="rounded-[1.5rem] bg-white p-3 shadow-[0_22px_60px_-44px_rgba(15,23,42,0.4)] ring-1 ring-slate-200/80">
                <div class="border-b border-slate-100 px-2 pb-3">
                    <p class="text-sm font-semibold text-slate-900">{{ $user?->name }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $user?->email }}</p>
                </div>

                <div class="mt-3 space-y-1">
                    @foreach ($primaryLinks as $link)
                        <a href="{{ $link['href'] }}" wire:navigate @class([
                            'flex items-center rounded-xl px-3 py-3 text-sm font-medium transition',
                            'bg-slate-100 text-slate-900' => $link['active'],
                            'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $link['active'],
                        ])>{{ $link['label'] }}</a>
                    @endforeach

                    <a href="{{ route('profile') }}" wire:navigate class="flex items-center rounded-xl px-3 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">Profile</a>
                    <button wire:click="logout" class="flex w-full items-center rounded-xl px-3 py-3 text-left text-sm font-medium text-rose-600 hover:bg-rose-50">Log Out</button>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-0 z-40 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 sm:hidden">
        <div class="mx-auto max-w-md rounded-[1.9rem] border border-slate-200/80 bg-white/96 px-3 py-2 shadow-[0_-14px_40px_-28px_rgba(15,23,42,0.35)] backdrop-blur">
            <div class="mx-auto grid max-w-md gap-2" style="grid-template-columns: repeat({{ count($primaryLinks) }}, minmax(0, 1fr));">
                @foreach ($primaryLinks as $link)
                    <a href="{{ $link['href'] }}" wire:navigate @class([
                        'flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2.5 text-[11px] font-medium transition',
                        'bg-[#dce7ff] text-[#0f47b8]' => $link['active'],
                        'text-slate-500' => ! $link['active'],
                    ])>
                        @if ($link['icon'] === 'dashboard' || $link['icon'] === 'home')
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3.5 4.5A1.5 1.5 0 0 1 5 3h10a1.5 1.5 0 0 1 1.5 1.5v11A1.5 1.5 0 0 1 15 17H5a1.5 1.5 0 0 1-1.5-1.5v-11ZM6 5v4h3V5H6Zm5 0v7h3V5h-3Zm-5 6v4h3v-4H6Zm5 3h3v1h-3v-1Z" /></svg>
                        @elseif ($link['icon'] === 'school')
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 3 3 6.5V8h14V6.5L10 3Zm-5.5 6.5a.5.5 0 0 0-.5.5v5a1 1 0 0 0 1 1H6v-4a1 1 0 1 1 2 0v4h4v-4a1 1 0 1 1 2 0v4h1a1 1 0 0 0 1-1v-5a.5.5 0 0 0-.5-.5h-11Z" /></svg>
                        @elseif ($link['icon'] === 'analytics')
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4 3.5A1.5 1.5 0 0 0 2.5 5v10A1.5 1.5 0 0 0 4 16.5h12a1.5 1.5 0 0 0 1.5-1.5V5A1.5 1.5 0 0 0 16 3.5H4Zm1.5 9.5a1 1 0 0 1 1-1h.25a1 1 0 0 1 1 1v1.5h-2.25V13Zm3.75-3a1 1 0 0 1 1-1h.25a1 1 0 0 1 1 1v4.5H9.25V10Zm3.75-2.5a1 1 0 0 1 1-1h.25a1 1 0 0 1 1 1v7h-2.25v-7Z" /></svg>
                        @elseif ($link['icon'] === 'settings')
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.49 2.17a1 1 0 0 0-1.98 0l-.16 1.2a6.9 6.9 0 0 0-1.34.56l-.97-.72a1 1 0 0 0-1.4.18l-.58.8a1 1 0 0 0 .18 1.4l.96.72a6.96 6.96 0 0 0-.24 1.44l-1.19.16a1 1 0 0 0 0 1.98l1.2.16c.07.5.18.98.35 1.43l-.96.72a1 1 0 0 0-.18 1.4l.58.8a1 1 0 0 0 1.4.18l.97-.72c.42.24.87.42 1.34.56l.16 1.2a1 1 0 0 0 1.98 0l.16-1.2c.47-.14.92-.32 1.34-.56l.97.72a1 1 0 0 0 1.4-.18l.58-.8a1 1 0 0 0-.18-1.4l-.96-.72c.17-.45.28-.93.35-1.43l1.2-.16a1 1 0 0 0 0-1.98l-1.19-.16a6.96 6.96 0 0 0-.24-1.44l.96-.72a1 1 0 0 0 .18-1.4l-.58-.8a1 1 0 0 0-1.4-.18l-.97.72a6.9 6.9 0 0 0-1.34-.56l-.16-1.2ZM10.5 12.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z" clip-rule="evenodd" /></svg>
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm0 1.5c-3.6 0-6.5 1.87-6.5 4.17 0 .73.6 1.33 1.33 1.33h10.34c.73 0 1.33-.6 1.33-1.33 0-2.3-2.9-4.17-6.5-4.17Z" /></svg>
                        @endif
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>
