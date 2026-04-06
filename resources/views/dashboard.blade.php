<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $availableWorkspaceLinks = [];

        if ($user?->hasRole('super_admin')) {
            $availableWorkspaceLinks[] = [
                'label' => 'Open Super Admin Dashboard',
                'href' => route('super-admin.dashboard'),
            ];
        }

        if ($user?->school?->slug && $user?->hasRole('school_admin')) {
            $availableWorkspaceLinks[] = [
                'label' => 'Open School Admin Workspace',
                'href' => route('school.admin.dashboard', ['slug' => $user->school->slug]),
            ];
        }

        if ($user?->school?->slug && $user?->hasRole('teacher')) {
            $availableWorkspaceLinks[] = [
                'label' => 'Open Teacher Workspace',
                'href' => route('school.teacher.dashboard', ['slug' => $user->school->slug]),
            ];
        }

        if ($user?->school?->slug && $user?->hasRole('parent')) {
            $availableWorkspaceLinks[] = [
                'label' => 'Open Parent Workspace',
                'href' => route('school.parent.dashboard', ['slug' => $user->school->slug]),
            ];
        }
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div>
                        <p class="font-semibold text-slate-900">{{ __("You're logged in!") }}</p>
                        <p class="mt-2 text-sm text-slate-600">
                            @if ($availableWorkspaceLinks !== [])
                                Your account is active, but you landed on the generic dashboard instead of a role workspace.
                            @else
                                Your account does not currently have a dashboard workspace assigned.
                            @endif
                        </p>
                    </div>

                    @if ($availableWorkspaceLinks !== [])
                        <div class="flex flex-wrap gap-3">
                            @foreach ($availableWorkspaceLinks as $link)
                                <a
                                    href="{{ $link['href'] }}"
                                    class="inline-flex items-center rounded-xl bg-[var(--pc-primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--pc-primary-deep)]"
                                >
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @elseif ($user?->school?->slug)
                        <p class="text-sm text-amber-700">
                            This account is linked to <span class="font-semibold">{{ $user->school->name }}</span>, but no recognized workspace role was found.
                        </p>
                    @else
                        <p class="text-sm text-slate-500">
                            Ask an administrator to assign the correct role and school access for this account.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
