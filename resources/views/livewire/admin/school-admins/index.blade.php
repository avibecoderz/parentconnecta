<div class="px-4 pb-10 pt-2 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-8">
        <section class="pc-page-header">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">Super Admin</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">School Admins</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">Monitor the operators responsible for each school workspace and verify their tenant assignments quickly.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-[1.25rem] border border-slate-200 bg-white px-5 py-4 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Total school admins</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ number_format($metrics['total']) }}</p>
                    </div>
                    <div class="rounded-[1.25rem] border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm">
                        <p class="text-sm font-medium text-emerald-700">Active school admins</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-emerald-800">{{ number_format($metrics['active']) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="pc-card overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-slate-950">Directory</h2>
                    <p class="mt-1 text-sm text-slate-500">Search by school admin name, email, or school.</p>
                </div>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search school admins..."
                    class="w-full rounded-[1rem] border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:ring-sky-500 lg:w-80"
                >
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                            <th class="px-6 py-4">Admin</th>
                            <th class="px-6 py-4">School</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Workspace</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($schoolAdmins as $admin)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-semibold text-slate-950">{{ $admin->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $admin->email }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-medium text-slate-900">{{ $admin->school?->name ?? 'No school assigned' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $admin->school?->slug ? '/school/'.$admin->school->slug : 'Missing workspace slug' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize',
                                        'bg-emerald-100 text-emerald-700' => $admin->status === 'active',
                                        'bg-slate-100 text-slate-600' => $admin->status !== 'active',
                                    ])>
                                        {{ $admin->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    {{ $admin->school?->status ? ucfirst($admin->school->status) : 'Unavailable' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">No school admins matched your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 lg:hidden">
                @forelse ($schoolAdmins as $admin)
                    <article class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-950">{{ $admin->name }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $admin->email }}</p>
                            </div>
                            <span @class([
                                'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize',
                                'bg-emerald-100 text-emerald-700' => $admin->status === 'active',
                                'bg-slate-100 text-slate-600' => $admin->status !== 'active',
                            ])>{{ $admin->status }}</span>
                        </div>

                        <div class="mt-4 rounded-[1rem] bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <p class="font-medium text-slate-900">{{ $admin->school?->name ?? 'No school assigned' }}</p>
                            <p class="mt-1">{{ $admin->school?->slug ? '/school/'.$admin->school->slug : 'Missing workspace slug' }}</p>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.35rem] border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">No school admins matched your filters.</div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $schoolAdmins->links() }}
            </div>
        </section>
    </div>
</div>
