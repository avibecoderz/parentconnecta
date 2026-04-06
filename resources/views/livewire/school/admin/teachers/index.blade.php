<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header
            eyebrow="School Admin"
            title="Teachers"
            :description="'Create and manage teacher accounts for '.$school->name.' while keeping every record inside the current school.'"
        >
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.admin.dashboard', ['slug' => $school->slug])" wire:navigate>
                    Back
                </x-ui.secondary-button>
                <x-ui.primary-button :href="route('school.admin.teachers.index', ['slug' => $school->slug, 'create' => 1])" wire:navigate>
                    Add Teacher
                </x-ui.primary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

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
                    <h2 class="text-lg font-semibold text-slate-900">Teacher directory</h2>
                    <p class="text-sm text-slate-500">Only teachers belonging to the current school are listed here.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.search-input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search teachers..."
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
                            <th class="px-6 py-4">Teacher</th>
                            <th class="px-6 py-4">Assignments</th>
                            <th class="px-6 py-4">Assigned classes</th>
                            <th class="px-6 py-4">Results</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($teachers as $teacher)
                            <tr class="align-top">
                                <td class="px-6 py-5">
                                    <p class="text-sm font-semibold text-slate-900">{{ $teacher->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $teacher->email }}</p>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">{{ number_format($teacher->teaching_classes_count) }} classes</td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($teacher->teachingClasses as $schoolClass)
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                                {{ $schoolClass->name }}{{ $schoolClass->section ? ' / '.$schoolClass->section : '' }}
                                            </span>
                                        @empty
                                            <span class="text-sm text-slate-400">No classes assigned</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">{{ number_format($teacher->results_count) }} records</td>
                                <td class="px-6 py-5">
                                    <x-ui.status-badge :status="$teacher->status" />
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="editTeacher({{ $teacher->id }})"
                                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="toggleTeacherStatus({{ $teacher->id }})"
                                            class="rounded-lg border border-amber-200 px-3 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-50"
                                        >
                                            {{ $teacher->status === 'active' ? 'Suspend' : 'Activate' }}
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $teacher->id }})"
                                            class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8">
                                    <x-ui.empty-state
                                        title="No teachers matched your filters"
                                        description="Try a different search term or status filter."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 lg:hidden">
                @forelse ($teachers as $teacher)
                    <article class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-[0_16px_40px_-32px_rgba(15,23,42,0.28)]">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">{{ $teacher->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $teacher->email }}</p>
                            </div>

                            <x-ui.status-badge :status="$teacher->status" />
                        </div>

                        <dl class="mt-4 space-y-2 text-sm text-slate-500">
                            <div class="flex justify-between gap-3">
                                <dt>Classes</dt>
                                <dd class="text-right text-slate-700">{{ number_format($teacher->teaching_classes_count) }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Results</dt>
                                <dd class="text-right text-slate-700">{{ number_format($teacher->results_count) }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse ($teacher->teachingClasses as $schoolClass)
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                    {{ $schoolClass->name }}{{ $schoolClass->section ? ' / '.$schoolClass->section : '' }}
                                </span>
                            @empty
                                <span class="text-sm text-slate-400">No classes assigned</span>
                            @endforelse
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button
                                type="button"
                                wire:click="editTeacher({{ $teacher->id }})"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                wire:click="toggleTeacherStatus({{ $teacher->id }})"
                                class="rounded-lg border border-amber-200 px-3 py-2 text-sm font-medium text-amber-700"
                            >
                                {{ $teacher->status === 'active' ? 'Suspend' : 'Activate' }}
                            </button>
                            <button
                                type="button"
                                wire:click="confirmDelete({{ $teacher->id }})"
                                class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700"
                            >
                                Delete
                            </button>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state
                        title="No teachers matched your filters"
                        description="Try a different search term or status filter."
                    />
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $teachers->links() }}
            </div>
        </section>
    </div>

    @if ($showTeacherModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-8">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">
                            {{ $editingTeacherId ? 'Edit teacher' : 'Add teacher' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">The teacher will be created inside {{ $school->name }} and assigned the teacher role automatically.</p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeTeacherModal"
                        class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveTeacher" class="space-y-6 px-6 py-6">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Full name</label>
                        <input type="text" wire:model.defer="name" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                        @error('name') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Email address</label>
                        <input type="email" wire:model.defer="email" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                        @error('email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">
                                {{ $editingTeacherId ? 'New password (optional)' : 'Password' }}
                            </label>
                            <input type="password" wire:model.defer="password" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('password') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Confirm password</label>
                            <input type="password" wire:model.defer="password_confirmation" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Status</label>
                        <select wire:model.defer="status" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <label class="text-sm font-medium text-slate-700">Assigned classes</label>
                            <span class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400">Optional</span>
                        </div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @forelse ($availableClasses as $availableClass)
                                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 hover:bg-white">
                                    <input
                                        type="checkbox"
                                        value="{{ $availableClass->id }}"
                                        wire:model.defer="assignedClassIds"
                                        class="mt-1 rounded border-slate-300 text-slate-900 focus:ring-sky-500"
                                    >
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-slate-900">
                                            {{ $availableClass->name }}{{ $availableClass->section ? ' / '.$availableClass->section : '' }}
                                        </span>
                                        <span class="mt-1 block text-sm text-slate-500">
                                            {{ $availableClass->code ?: 'No class code yet' }} - {{ $availableClass->status }}
                                        </span>
                                    </span>
                                </label>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-200 p-5 text-sm text-slate-500 sm:col-span-2">
                                    No classes are available yet. Create school classes first, then return to assign teachers.
                                </div>
                            @endforelse
                        </div>
                        @error('assignedClassIds') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        @error('assignedClassIds.*') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        Teacher emails are globally unique because all roles share the main users table. Assigned classes are synced through the `teacher_class` pivot and stay limited to this school only.
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            wire:click="closeTeacherModal"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            {{ $editingTeacherId ? 'Save changes' : 'Create teacher' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDeleteModal && $deletingTeacher)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <h2 class="text-xl font-semibold text-slate-900">Delete teacher</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    This will permanently remove
                    <span class="font-semibold text-slate-900">{{ $deletingTeacher->name }}</span>
                    from {{ $school->name }}.
                </p>

                @if ($deletingTeacher->teaching_classes_count > 0 || $deletingTeacher->results_count > 0)
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
                        @if ($deletingTeacher->teaching_classes_count > 0)
                            <p>{{ number_format($deletingTeacher->teaching_classes_count) }} class assignments will be removed.</p>
                        @endif
                        @if ($deletingTeacher->results_count > 0)
                            <p class="mt-1">{{ number_format($deletingTeacher->results_count) }} result records will remain but lose the teacher reference.</p>
                        @endif
                    </div>
                @endif

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        wire:click="closeDeleteModal"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="deleteTeacher"
                        class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700"
                    >
                        Delete teacher
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showPlanLimitModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/60 px-4 py-8">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <h2 class="text-xl font-semibold text-slate-900">Plan limit reached</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    {{ $planLimitMessage }}
                </p>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        wire:click="closePlanLimitModal"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Okay
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
