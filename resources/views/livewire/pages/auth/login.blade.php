<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $defaultRoute = auth()->user()?->hasRole('super_admin')
            ? route('super-admin.dashboard', absolute: false)
            : route('dashboard', absolute: false);

        $this->redirectIntended(default: $defaultRoute, navigate: true);
    }
}; ?>

<div>
    <div class="rounded-[1.5rem] bg-gradient-to-br from-[#eef4ff] via-white to-[#f7fbff] p-5 ring-1 ring-slate-100">
        <p class="pc-auth-kicker">Welcome Back</p>
        <h1 class="pc-auth-title">Log in to your workspace</h1>
        <p class="pc-auth-copy">
            Access dashboards, school operations, and parent communication tools from one secure place.
        </p>
    </div>

    <x-auth-session-status class="mt-5 rounded-[1rem] border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <div class="pc-auth-divider"></div>

    <form wire:submit="login" class="space-y-5">
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="email" :value="__('Email Address')" />
                <span class="text-xs font-medium text-slate-400">Use your school account</span>
            </div>

            <div class="relative mt-2">
                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M2.94 5.5A2.5 2.5 0 0 1 5.3 4h9.4a2.5 2.5 0 0 1 2.36 1.5L10 9.92 2.94 5.5ZM2.5 7.18V13.5A2.5 2.5 0 0 0 5 16h10a2.5 2.5 0 0 0 2.5-2.5V7.18l-7.1 4.43a.75.75 0 0 1-.8 0L2.5 7.18Z" />
                    </svg>
                </span>
                <x-text-input wire:model="form.email" id="email" class="block w-full ps-12" type="email" name="email" required autofocus autocomplete="username" placeholder="name@school.com" />
            </div>

            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-[#0f47b8] transition hover:text-[#0a3a97]" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <div class="relative mt-2">
                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.5 8V6.5a4.5 4.5 0 1 1 9 0V8h.5A1.5 1.5 0 0 1 16.5 9.5v6A1.5 1.5 0 0 1 15 17H5A1.5 1.5 0 0 1 3.5 15.5v-6A1.5 1.5 0 0 1 5 8h.5Zm7.5 0V6.5a3 3 0 1 0-6 0V8h6Z" clip-rule="evenodd" />
                    </svg>
                </span>
                <x-text-input wire:model="form.password" id="password" class="block w-full ps-12" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" />
            </div>

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4 rounded-[1rem] bg-slate-50 px-4 py-3">
            <label for="remember" class="inline-flex items-center gap-3">
                <input wire:model="form.remember" id="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-[#0f47b8] focus:ring-blue-200" name="remember">
                <span class="text-sm font-medium text-slate-600">{{ __('Remember me') }}</span>
            </label>

            <span class="text-xs uppercase tracking-[0.24em] text-slate-400">Protected</span>
        </div>

        <x-primary-button class="w-full justify-center rounded-[1.2rem] py-4 text-base font-semibold shadow-[0_24px_48px_-28px_rgba(13,59,102,0.62)]">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 3a1 1 0 0 1 1 1v1.04a5.5 5.5 0 0 1 0 10.92V17a1 1 0 1 1-2 0v-1.04a5.5 5.5 0 0 1 0-10.92V4a1 1 0 0 1 1-1Zm0 4a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z" clip-rule="evenodd" />
            </svg>
            {{ __('Log in') }}
        </x-primary-button>
    </form>
</div>
