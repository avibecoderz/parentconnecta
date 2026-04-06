<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header eyebrow="School Admin" title="Students" :description="'Manage student records for '.$school->name.' with class-aware filtering and school-scoped access.'">
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.admin.dashboard', ['slug' => $school->slug])" wire:navigate>Back</x-ui.secondary-button>
                <x-ui.primary-button :href="route('school.admin.students.index', ['slug' => $school->slug, 'create' => 1])" wire:navigate>Add Student</x-ui.primary-button>
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
                    <h2 class="text-lg font-semibold text-slate-900">Student directory</h2>
                    <p class="text-sm text-slate-500">Every student listed here belongs to the current school slug and a class in this same school.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.search-input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search students..."
                        class="sm:w-72"
                    />

                    <select
                        wire:model.live="classFilter"
                        class="rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-900 focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]"
                    >
                        <option value="all">All classes</option>
                        @foreach ($classOptions as $classOption)
                            <option value="{{ $classOption->id }}">
                                {{ $classOption->name }}{{ $classOption->section ? ' / '.$classOption->section : '' }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        wire:model.live="statusFilter"
                        class="rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-900 focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]"
                    >
                        <option value="all">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="graduated">Graduated</option>
                        <option value="withdrawn">Withdrawn</option>
                    </select>
                </div>
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                            <th class="px-6 py-4">Student</th>
                            <th class="px-6 py-4">Class</th>
                            <th class="px-6 py-4">Family</th>
                            <th class="px-6 py-4">Records</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($students as $student)
                            <tr class="align-top">
                                <td class="px-6 py-5">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $student->admission_number }}</p>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    {{ $student->schoolClass?->name }}{{ $student->schoolClass?->section ? ' / '.$student->schoolClass->section : '' }}
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    <p>{{ number_format($student->parents_count) }} parents</p>
                                    <p class="mt-1">{{ $student->gender ? ucfirst($student->gender) : 'Gender not set' }}</p>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    <p>{{ number_format($student->results_count) }} results</p>
                                    <p class="mt-1">{{ number_format($student->payments_count) }} payments</p>
                                </td>
                                <td class="px-6 py-5">
                                    <x-ui.status-badge :status="$student->status" />
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col items-end gap-2">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <button
                                                type="button"
                                                wire:click="viewStudent({{ $student->id }})"
                                                class="rounded-lg border border-sky-200 px-3 py-2 text-sm font-medium text-sky-700 transition hover:bg-sky-50"
                                            >
                                                View
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="editStudent({{ $student->id }})"
                                                class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                            >
                                                Edit
                                            </button>
                                        </div>

                                        <select
                                            wire:change="changeStatus({{ $student->id }}, $event.target.value)"
                                            class="rounded-[0.95rem] border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-900 focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]"
                                        >
                                            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'graduated' => 'Graduated', 'withdrawn' => 'Withdrawn'] as $value => $label)
                                                <option value="{{ $value }}" @selected($student->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No students matched your current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 lg:hidden">
                @forelse ($students as $student)
                    <article class="rounded-2xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $student->admission_number }}</p>
                            </div>

                            <x-ui.status-badge :status="$student->status" />
                        </div>

                        <dl class="mt-4 space-y-2 text-sm text-slate-500">
                            <div class="flex justify-between gap-3">
                                <dt>Class</dt>
                                <dd class="text-right text-slate-700">
                                    {{ $student->schoolClass?->name }}{{ $student->schoolClass?->section ? ' / '.$student->schoolClass->section : '' }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Parents</dt>
                                <dd class="text-right text-slate-700">{{ number_format($student->parents_count) }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Results</dt>
                                <dd class="text-right text-slate-700">{{ number_format($student->results_count) }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button
                                type="button"
                                wire:click="viewStudent({{ $student->id }})"
                                class="rounded-lg border border-sky-200 px-3 py-2 text-sm font-medium text-sky-700"
                            >
                                View
                            </button>
                            <button
                                type="button"
                                wire:click="editStudent({{ $student->id }})"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700"
                            >
                                Edit
                            </button>
                        </div>

                        <div class="mt-4">
                            <label class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</label>
                            <select
                                wire:change="changeStatus({{ $student->id }}, $event.target.value)"
                                class="mt-2 w-full rounded-[0.95rem] border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-900 focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]"
                            >
                                @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'graduated' => 'Graduated', 'withdrawn' => 'Withdrawn'] as $value => $label)
                                    <option value="{{ $value }}" @selected($student->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                        No students matched your current filters.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $students->links() }}
            </div>
        </section>
    </div>

    @if ($showStudentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-8">
            <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">
                            {{ $editingStudentId ? 'Edit student' : 'Add student' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">Every student must be assigned to a class in {{ $school->name }}.</p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeStudentModal"
                        class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveStudent" class="space-y-6 px-6 py-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Admission number</label>
                            <input type="text" wire:model.defer="admissionNumber" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('admissionNumber') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Class</label>
                            <select wire:model.defer="schoolClassId" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <option value="">Select class</option>
                                @foreach ($classOptions as $classOption)
                                    <option value="{{ $classOption->id }}">
                                        {{ $classOption->name }}{{ $classOption->section ? ' / '.$classOption->section : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('schoolClassId') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label class="text-sm font-medium text-slate-700">First name</label>
                            <input type="text" wire:model.defer="firstName" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('firstName') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Last name</label>
                            <input type="text" wire:model.defer="lastName" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('lastName') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Middle name</label>
                            <input type="text" wire:model.defer="middleName" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('middleName') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Date of birth</label>
                            <input type="date" wire:model.defer="dateOfBirth" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('dateOfBirth') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Gender</label>
                            <select wire:model.defer="gender" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            @error('gender') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Admitted at</label>
                            <input type="date" wire:model.defer="admittedAt" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('admittedAt') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Status</label>
                            <select wire:model.defer="status" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="graduated">Graduated</option>
                                <option value="withdrawn">Withdrawn</option>
                            </select>
                            @error('status') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        Admission numbers are unique per school, and students can only be assigned to classes that belong to the current school.
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            wire:click="closeStudentModal"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            {{ $editingStudentId ? 'Save changes' : 'Create student' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDetailsModal && $viewingStudent)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-8">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">
                            {{ $viewingStudent->first_name }} {{ $viewingStudent->last_name }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $viewingStudent->admission_number }}</p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeDetailsModal"
                        class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-6 px-6 py-6">
                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <x-school-admin.stat-card label="Class" :value="$viewingStudent->schoolClass?->name.($viewingStudent->schoolClass?->section ? ' / '.$viewingStudent->schoolClass->section : '')" hint="Current class placement" />
                        <x-school-admin.stat-card label="Parents" :value="number_format($viewingStudent->parents_count)" hint="Linked parent accounts" />
                        <x-school-admin.stat-card label="Results" :value="number_format($viewingStudent->results_count)" hint="Academic records stored" />
                        <x-school-admin.stat-card label="Payments" :value="number_format($viewingStudent->payments_count)" hint="Payment records linked to this student" />
                    </section>

                    <section class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <h3 class="text-lg font-semibold text-slate-900">Student details</h3>
                            <dl class="mt-4 space-y-3 text-sm text-slate-600">
                                <div class="flex justify-between gap-4">
                                    <dt>Full name</dt>
                                    <dd class="text-right font-medium text-slate-900">
                                        {{ $viewingStudent->first_name }} {{ $viewingStudent->middle_name ? $viewingStudent->middle_name.' ' : '' }}{{ $viewingStudent->last_name }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt>Gender</dt>
                                    <dd class="text-right font-medium text-slate-900">{{ $viewingStudent->gender ? ucfirst($viewingStudent->gender) : 'Not set' }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt>Date of birth</dt>
                                    <dd class="text-right font-medium text-slate-900">{{ $viewingStudent->date_of_birth?->format('M j, Y') ?? 'Not set' }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt>Admitted at</dt>
                                    <dd class="text-right font-medium text-slate-900">{{ $viewingStudent->admitted_at?->format('M j, Y') ?? 'Not set' }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt>Status</dt>
                                    <dd class="text-right font-medium capitalize text-slate-900">{{ $viewingStudent->status }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt>Created</dt>
                                    <dd class="text-right font-medium text-slate-900">{{ $viewingStudent->created_at->format('M j, Y') }}</dd>
                                </div>
                            </dl>
                        </article>

                        <article class="rounded-3xl border border-slate-200 bg-white p-5">
                            <h3 class="text-lg font-semibold text-slate-900">Linked parents</h3>
                            <div class="mt-4 space-y-3">
                                @forelse ($viewingStudent->parents as $parent)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-sm font-semibold text-slate-900">{{ $parent->name }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $parent->email }}</p>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-sm text-slate-500">
                                        No parent has been linked to this student yet.
                                    </div>
                                @endforelse
                            </div>
                        </article>
                    </section>
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
