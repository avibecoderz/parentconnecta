<div class="px-4 pb-10 pt-2 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-8">
        <section class="pc-page-header">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">Super Admin</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Users</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">A clean overview of platform accounts, their roles, and the school workspace each user belongs to.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-[1.25rem] border border-slate-200 bg-white px-5 py-4 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Total users</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ number_format($metrics['total']) }}</p>
                    </div>
                    <div class="rounded-[1.25rem] border border-blue-200 bg-blue-50 px-5 py-4 shadow-sm">
                        <p class="text-sm font-medium text-blue-700">Active users</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-blue-800">{{ number_format($metrics['active']) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="pc-card overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-slate-950">User directory</h2>
                    <p class="mt-1 text-sm text-slate-500">Search by user name, email, role, or school.</p>
                </div>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search users..."
                    class="w-full rounded-[1rem] border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:ring-sky-500 lg:w-80"
                >
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">School</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-semibold text-slate-950">{{ $user->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $user->email }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($user->roles as $role)
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ str($role->name)->headline() }}</span>
                                        @empty
                                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">No role</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    @if ($user->school)
                                        <p class="font-medium text-slate-900">{{ $user->school->name }}</p>
                                        <p class="mt-1">/school/{{ $user->school->slug }}</p>
                                    @else
                                        <span>No school assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize',
                                        'bg-emerald-100 text-emerald-700' => $user->status === 'active',
                                        'bg-slate-100 text-slate-600' => $user->status !== 'active',
                                    ])>
                                        {{ $user->status ?? 'unknown' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">No users matched your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 lg:hidden">
                @forelse ($users as $user)
                    <article class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-950">{{ $user->name }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $user->email }}</p>
                            </div>
                            <span @class([
                                'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize',
                                'bg-emerald-100 text-emerald-700' => $user->status === 'active',
                                'bg-slate-100 text-slate-600' => $user->status !== 'active',
                            ])>{{ $user->status ?? 'unknown' }}</span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse ($user->roles as $role)
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ str($role->name)->headline() }}</span>
                            @empty
                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">No role</span>
                            @endforelse
                        </div>

                        <div class="mt-4 rounded-[1rem] bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            @if ($user->school)
                                <p class="font-medium text-slate-900">{{ $user->school->name }}</p>
                                <p class="mt-1">/school/{{ $user->school->slug }}</p>
                            @else
                                <p>No school assigned</p>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.35rem] border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">No users matched your filters.</div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $users->links() }}
            </div>
        </section>
    </div>
</div>
