<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header eyebrow="School Admin" title="Classes" :description="'Manage class records for '.$school->name.' without leaving the current school tenant.'">
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.admin.dashboard', ['slug' => $school->slug])" wire:navigate>Back</x-ui.secondary-button>
                <x-ui.primary-button :href="route('school.admin.classes.index', ['slug' => $school->slug, 'create' => 1])" wire:navigate>Add Class</x-ui.primary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ session('error') }}
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
                    <h2 class="text-lg font-semibold text-slate-900">Class directory</h2>
                    <p class="text-sm text-slate-500">All records shown here are filtered to the current school slug.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Search name, section, or code..." class="sm:w-80" />

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
                            <th class="px-6 py-4">Capacity</th>
                            <th class="px-6 py-4">Usage</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
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
                                <td class="px-6 py-5 text-sm text-slate-500">{{ $schoolClass->capacity ? number_format($schoolClass->capacity) : 'Not set' }}</td>
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    <p>{{ number_format($schoolClass->students_count) }} students</p>
                                    <p class="mt-1">{{ number_format($schoolClass->teachers_count) }} teachers</p>
                                </td>
                                <td class="px-6 py-5">
                                    <x-ui.status-badge :status="$schoolClass->status" />
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="editClass({{ $schoolClass->id }})"
                                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $schoolClass->id }})"
                                            class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No classes matched your current filters.
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

                            <span @class([
                                'inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                                'bg-emerald-100 text-emerald-700' => $schoolClass->status === 'active',
                                'bg-amber-100 text-amber-700' => $schoolClass->status === 'inactive',
                            ])>
                                {{ $schoolClass->status }}
                            </span>
                        </div>

                        <dl class="mt-4 space-y-2 text-sm text-slate-500">
                            <div class="flex justify-between gap-3">
                                <dt>Code</dt>
                                <dd class="text-right text-slate-700">{{ $schoolClass->code ?: 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Capacity</dt>
                                <dd class="text-right text-slate-700">{{ $schoolClass->capacity ? number_format($schoolClass->capacity) : 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Students</dt>
                                <dd class="text-right text-slate-700">{{ number_format($schoolClass->students_count) }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Teachers</dt>
                                <dd class="text-right text-slate-700">{{ number_format($schoolClass->teachers_count) }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button
                                type="button"
                                wire:click="editClass({{ $schoolClass->id }})"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                wire:click="confirmDelete({{ $schoolClass->id }})"
                                class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700"
                            >
                                Delete
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                        No classes matched your current filters.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $classes->links() }}
            </div>
        </section>
    </div>

    @if ($showClassModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-8">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">
                            {{ $editingClassId ? 'Edit class' : 'Add class' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">All changes apply only to {{ $school->name }}.</p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeClassModal"
                        class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveClass" class="space-y-6 px-6 py-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Class name</label>
                            <input type="text" wire:model.defer="name" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('name') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Section</label>
                            <input type="text" wire:model.defer="section" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('section') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Code</label>
                            <input type="text" wire:model.defer="code" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('code') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Capacity</label>
                            <input type="number" min="1" wire:model.defer="capacity" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('capacity') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
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

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        Class names are unique per section inside the current school. Class codes are also unique within the current school.
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            wire:click="closeClassModal"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            {{ $editingClassId ? 'Save changes' : 'Create class' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDeleteModal && $deletingClass)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <h2 class="text-xl font-semibold text-slate-900">Delete class</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    This will permanently remove
                    <span class="font-semibold text-slate-900">
                        {{ $deletingClass->section ? $deletingClass->name.' / '.$deletingClass->section : $deletingClass->name }}
                    </span>.
                </p>

                @if ($deletingClass->results_count > 0)
                    <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                        This class has result records and cannot be deleted until those records are addressed.
                    </div>
                @elseif ($deletingClass->students_count > 0)
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
                        {{ number_format($deletingClass->students_count) }} students are currently assigned here. Deleting the class will leave those students unassigned.
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
                        wire:click="deleteClass"
                        @disabled($deletingClass->results_count > 0)
                        class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:bg-rose-300"
                    >
                        Delete class
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
