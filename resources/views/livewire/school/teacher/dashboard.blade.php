@php
    $pageDescription = "Everything here is limited to {$teacher->name}'s assigned classes inside {$school->name}.";
@endphp

<x-slot name="header">
    <x-ui.page-header
        eyebrow="Teacher"
        title="Teaching Dashboard"
        :description="$pageDescription"
    >
        <x-slot:actions>
            <x-ui.secondary-button :href="route('school.teacher.students.index', ['slug' => $school->slug])" wire:navigate>
                View Students
            </x-ui.secondary-button>
            @if ($assignedClasses->isNotEmpty())
                <x-ui.primary-button :href="route('school.teacher.students.index', ['slug' => $school->slug, 'create' => 1])" wire:navigate>
                    Add Student
                </x-ui.primary-button>
            @else
                <x-ui.primary-button type="button" disabled>
                    Add Student
                </x-ui.primary-button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>
</x-slot>

<div class="pb-10">

    <div class="pc-shell py-8">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
            @foreach ($stats as $stat)
                <a href="{{ $stat['href'] }}" wire:navigate class="block transition hover:-translate-y-0.5">
                    <x-school-admin.stat-card
                        :label="$stat['label']"
                        :value="$stat['value']"
                        :hint="$stat['hint']"
                        class="h-full hover:border-[var(--pc-primary)]/25 hover:shadow-[0_20px_48px_-34px_rgba(13,59,102,0.32)]"
                    />
                </a>
            @endforeach
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
            <article class="pc-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="pc-eyebrow text-slate-400">Assigned classes</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">Classes in your teaching scope</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">
                        {{ number_format($assignedClasses->count()) }} total
                    </span>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($assignedClasses as $assignedClass)
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-900">
                                        {{ $assignedClass->name }}{{ $assignedClass->section ? ' / '.$assignedClass->section : '' }}
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $assignedClass->code ?: 'No class code yet' }}
                                    </p>
                                </div>
                                <div class="rounded-[1rem] bg-white px-4 py-3 text-sm text-slate-600 shadow-[0_12px_30px_-24px_rgba(15,23,42,0.22)]">
                                    <span class="font-semibold text-slate-900">{{ number_format($assignedClass->active_students_count) }}</span>
                                    active students
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state
                            title="No assigned classes yet"
                            description="Once a school admin links you to a class, student, result, and payment data will appear here automatically."
                        />
                    @endforelse
                </div>
            </article>

            <article class="pc-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="pc-eyebrow text-slate-400">Quick actions</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">Teacher work areas</h2>
                    </div>

                    <button
                        type="button"
                        wire:click="openStudentModal"
                        @disabled($assignedClasses->isEmpty())
                        class="pc-btn-primary px-4 py-2.5 text-xs disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Add Student
                    </button>
                </div>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Register a new student directly into one of your assigned classes.
                </p>

                @if ($assignedClasses->isEmpty())
                    <x-ui.empty-state
                        title="No class available yet"
                        description="A school admin needs to assign you to at least one class before you can register students."
                        class="mt-5 bg-slate-50"
                    />
                @endif

                <div class="mt-6 grid gap-3">
                    @foreach ($moduleLinks as $moduleLink)
                        <a
                            href="{{ $moduleLink['route'] }}"
                            wire:navigate
                            class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 hover:bg-white"
                        >
                            <p class="text-sm font-semibold text-slate-900">{{ $moduleLink['label'] }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $moduleLink['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
            <article class="pc-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="pc-eyebrow text-slate-400">Students</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">Recent students in your classes</h2>
                    </div>
                    <a
                        href="{{ route('school.teacher.students.index', ['slug' => $school->slug]) }}"
                        wire:navigate
                        class="text-sm font-semibold text-[var(--pc-primary)] hover:text-[var(--pc-primary-deep)]"
                    >
                        Open page
                    </a>
                </div>

                <div class="mt-6 space-y-3">
                    @forelse ($recentStudents as $student)
                        <div class="flex flex-col gap-3 rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $student->first_name }} {{ $student->last_name }}</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $student->admission_number }}
                                    @if ($student->schoolClass)
                                        - {{ $student->schoolClass->name }}{{ $student->schoolClass->section ? ' / '.$student->schoolClass->section : '' }}
                                    @endif
                                </p>
                            </div>
                            <x-ui.status-badge :status="$student->status" />
                        </div>
                    @empty
                        <x-ui.empty-state
                            title="No students in scope yet"
                            description="Student records will appear here once one of your assigned classes has enrolled learners."
                        />
                    @endforelse
                </div>
            </article>

            <article class="pc-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="pc-eyebrow text-slate-400">Payments</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">Outstanding payment watchlist</h2>
                    </div>
                    <a
                        href="{{ route('school.teacher.payments.index', ['slug' => $school->slug]) }}"
                        wire:navigate
                        class="text-sm font-semibold text-[var(--pc-primary)] hover:text-[var(--pc-primary-deep)]"
                    >
                        Open page
                    </a>
                </div>

                <div class="mt-6 space-y-3">
                    @forelse ($paymentAlerts as $payment)
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $payment->student?->first_name }} {{ $payment->student?->last_name }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $payment->payment_type }} - Balance {{ $payment->currency }} {{ number_format((float) $payment->balance, 2) }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $payment->student?->schoolClass?->name }}{{ $payment->student?->schoolClass?->section ? ' / '.$payment->student?->schoolClass?->section : '' }}
                            </p>
                        </div>
                    @empty
                        <x-ui.empty-state
                            title="No payment alerts right now"
                            description="There are no pending payment records for students in your assigned classes."
                        />
                    @endforelse
                </div>
            </article>
        </section>
    </div>

    @if ($showExamModeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-6">
            <button
                type="button"
                wire:click="closeExamModeModal"
                class="fixed inset-0 bg-slate-950/45 backdrop-blur-[2px]"
                aria-label="Close exam mode message"
            ></button>

            <div class="relative mx-auto w-full max-w-lg">
                <div class="pc-modal-panel px-6 py-6 sm:px-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="pc-eyebrow text-slate-400">Teacher</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Exam Mode</h2>
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                We are currently working for the AI Agnet to handle this taks check back!
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="closeExamModeModal"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.22 5.22a.75.75 0 0 1 1.06 0L10 8.94l3.72-3.72a.75.75 0 1 1 1.06 1.06L11.06 10l3.72 3.72a.75.75 0 0 1-1.06 1.06L10 11.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06L8.94 10 5.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        This feature will be available here once the AI workflow is ready for teacher exam operations.
                    </div>

                    <div class="mt-6 flex justify-end border-t border-slate-100 pt-6">
                        <button
                            type="button"
                            wire:click="closeExamModeModal"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showStudentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-6">
            <button
                type="button"
                wire:click="closeStudentModal"
                class="fixed inset-0 bg-slate-950/45 backdrop-blur-[2px]"
                aria-label="Close student form"
            ></button>

            <div class="relative mx-auto w-full max-w-2xl">
                <div class="pc-modal-panel px-6 py-6 sm:px-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="pc-eyebrow text-slate-400">Teacher</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Register New Student</h2>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                                Add a student into one of your assigned classes without leaving the dashboard.
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="closeStudentModal"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.22 5.22a.75.75 0 0 1 1.06 0L10 8.94l3.72-3.72a.75.75 0 1 1 1.06 1.06L11.06 10l3.72 3.72a.75.75 0 0 1-1.06 1.06L10 11.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06L8.94 10 5.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="saveStudent" class="mt-6 space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input-label for="teacher-school-class" value="Class" />
                                <select
                                    id="teacher-school-class"
                                    wire:model="schoolClassId"
                                    class="pc-input mt-2"
                                >
                                    <option value="">Select a class</option>
                                    @foreach ($classOptions as $classOption)
                                        <option value="{{ $classOption['id'] }}">{{ $classOption['label'] }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('schoolClassId')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="teacher-admission-number" value="Admission Number" />
                                <x-text-input id="teacher-admission-number" wire:model="admissionNumber" type="text" class="mt-2" />
                                <x-input-error :messages="$errors->get('admissionNumber')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="teacher-status" value="Status" />
                                <select id="teacher-status" wire:model="status" class="pc-input mt-2">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="graduated">Graduated</option>
                                    <option value="withdrawn">Withdrawn</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="teacher-first-name" value="First Name" />
                                <x-text-input id="teacher-first-name" wire:model="firstName" type="text" class="mt-2" />
                                <x-input-error :messages="$errors->get('firstName')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="teacher-last-name" value="Last Name" />
                                <x-text-input id="teacher-last-name" wire:model="lastName" type="text" class="mt-2" />
                                <x-input-error :messages="$errors->get('lastName')" class="mt-2" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="teacher-middle-name" value="Middle Name" />
                                <x-text-input id="teacher-middle-name" wire:model="middleName" type="text" class="mt-2" />
                                <x-input-error :messages="$errors->get('middleName')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="teacher-date-of-birth" value="Date of Birth" />
                                <x-text-input id="teacher-date-of-birth" wire:model="dateOfBirth" type="date" class="mt-2" />
                                <x-input-error :messages="$errors->get('dateOfBirth')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="teacher-admitted-at" value="Admission Date" />
                                <x-text-input id="teacher-admitted-at" wire:model="admittedAt" type="date" class="mt-2" />
                                <x-input-error :messages="$errors->get('admittedAt')" class="mt-2" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="teacher-gender" value="Gender" />
                                <select id="teacher-gender" wire:model="gender" class="pc-input mt-2">
                                    <option value="">Select gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                            <button type="button" wire:click="closeStudentModal" class="pc-btn-secondary">
                                Cancel
                            </button>
                            <button type="submit" class="pc-btn-primary">
                                Save Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
