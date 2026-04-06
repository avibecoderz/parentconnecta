<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header eyebrow="Teacher" title="Assigned Classes" :description="'Review only the class records currently assigned to you in '.$school->name.'.'">
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.teacher.dashboard', ['slug' => $school->slug])" wire:navigate>
                    Back
                </x-ui.secondary-button>
                <x-ui.primary-button :href="route('school.teacher.students.index', ['slug' => $school->slug])" wire:navigate>
                    View Students
                </x-ui.primary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <x-school-admin.stat-card
                    :label="$metric['label']"
                    :value="$metric['value']"
                    :hint="$metric['hint']"
                />
            @endforeach
        </section>

        <section class="pc-card mt-6 overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Class directory</h2>
                    <p class="text-sm text-slate-500">Everything listed here is limited to the classes already assigned to your teacher profile.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.search-input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search class name, section, or code..."
                        class="sm:w-80"
                    />

                    <select
                        wire:model.live="statusFilter"
                        class="rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-900 focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]"
                    >
                        <option value="all">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                            <th class="px-6 py-4">Class</th>
                            <th class="px-6 py-4">Code</th>
                            <th class="px-6 py-4">Students</th>
                            <th class="px-6 py-4">Teachers</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($classes as $schoolClass)
                            <tr class="align-top">
                                <td class="px-6 py-5">
                                    <p class="text-sm font-semibold text-slate-900">{{ $schoolClass->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $schoolClass->section ?: 'No section' }}</p>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">{{ $schoolClass->code ?: 'Not set' }}</td>
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    <p>{{ number_format($schoolClass->students_count) }} total</p>
                                    <p class="mt-1">{{ number_format($schoolClass->active_students_count) }} active</p>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">{{ number_format($schoolClass->teachers_count) }}</td>
                                <td class="px-6 py-5">
                                    <x-ui.status-badge :status="$schoolClass->status" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No assigned classes matched your current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 lg:hidden">
                @forelse ($classes as $schoolClass)
                    <article class="rounded-2xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">{{ $schoolClass->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $schoolClass->section ?: 'No section' }}</p>
                            </div>

                            <x-ui.status-badge :status="$schoolClass->status" />
                        </div>

                        <dl class="mt-4 space-y-2 text-sm text-slate-500">
                            <div class="flex justify-between gap-3">
                                <dt>Code</dt>
                                <dd class="text-right text-slate-700">{{ $schoolClass->code ?: 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Students</dt>
                                <dd class="text-right text-slate-700">{{ number_format($schoolClass->students_count) }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Active students</dt>
                                <dd class="text-right text-slate-700">{{ number_format($schoolClass->active_students_count) }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Teachers</dt>
                                <dd class="text-right text-slate-700">{{ number_format($schoolClass->teachers_count) }}</dd>
                            </div>
                        </dl>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                        No assigned classes matched your current filters.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $classes->links() }}
            </div>
        </section>
    </div>
</div>
