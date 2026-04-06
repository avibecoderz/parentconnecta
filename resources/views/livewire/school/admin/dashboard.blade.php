<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header
            eyebrow="School Admin"
            :title="$school->name.' Dashboard'"
            description="Monitor the health of your school and move quickly into each admin module."
        >
            <x-slot:actions>
                <x-ui.primary-button :href="route('school.admin.students.index', ['slug' => $school->slug, 'create' => 1])" wire:navigate>
                    Add Student
                </x-ui.primary-button>
                <x-ui.secondary-button :href="route('school.admin.payments.index', ['slug' => $school->slug])" wire:navigate>
                    Review Payments
                </x-ui.secondary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
            @foreach ($stats as $stat)
                <x-school-admin.stat-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :hint="$stat['hint']"
                />
            @endforeach
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[1.2fr,0.8fr]">
            <article class="pc-card overflow-hidden">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Admin modules</h2>
                        <p class="text-sm text-slate-500">Each section is already tenant-scoped and ready for daily school operations.</p>
                    </div>

                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                        <button
                            type="button"
                            wire:click="createClass"
                            class="pc-btn-secondary w-full sm:w-auto"
                        >
                            Add Class
                        </button>

                        <button
                            type="button"
                            wire:click="createParent"
                            class="pc-btn-primary w-full sm:w-auto"
                        >
                            Add Parent
                        </button>
                    </div>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-2">
                    @foreach ($moduleLinks as $module)
                        <a
                            href="{{ $module['route'] }}"
                            wire:navigate
                            class="group rounded-[1.35rem] border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-white"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-base font-semibold text-slate-900">{{ $module['label'] }}</h3>
                                <span class="text-sm font-medium text-[var(--pc-primary)] transition group-hover:text-[var(--pc-primary-deep)]">Open</span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $module['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </article>

            <article class="pc-card overflow-hidden">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Payment snapshot</h2>
                        <p class="text-sm text-slate-500">A quick view of payment activity for this school.</p>
                    </div>

                    <button
                        type="button"
                        wire:click="createPayment"
                        class="pc-btn-primary w-full sm:w-auto"
                    >
                        Add Payment
                    </button>
                </div>

                <div class="space-y-4 p-6">
                    @foreach ($paymentSummary as $item)
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-medium text-slate-500">{{ $item['label'] }}</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $item['value'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">{{ $item['hint'] }}</p>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="pc-card mt-6 overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Recent students</h2>
                    <p class="text-sm text-slate-500">Latest student records created inside this school workspace.</p>
                </div>

                <a
                    href="{{ route('school.admin.students.index', ['slug' => $school->slug]) }}"
                    wire:navigate
                    class="text-sm font-medium text-[var(--pc-primary)] transition hover:text-[var(--pc-primary-deep)]"
                >
                    Open students
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($recentStudents as $student)
                    <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $student->admission_number }} -
                                {{ $student->schoolClass?->name ? $student->schoolClass->name.($student->schoolClass->section ? ' / '.$student->schoolClass->section : '') : 'No class assigned' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-ui.status-badge :status="$student->status" />
                            <span class="text-sm text-slate-500">{{ $student->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8">
                        <x-ui.empty-state
                            title="No students created yet"
                            description="Student records for this school will appear here once enrollment begins."
                        />
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @if ($showCurrentTermModal)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-6">
            <button
                type="button"
                wire:click="closeCurrentTermModal"
                class="fixed inset-0 bg-slate-950/45 backdrop-blur-[2px]"
                aria-label="Close current term form"
            ></button>

            <div class="relative mx-auto w-full max-w-xl">
                <div class="pc-modal-panel px-6 py-6 sm:px-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="pc-eyebrow text-slate-400">School Admin</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Set Current Term</h2>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                                This term will be shared across teacher and parent dashboards for {{ $school->name }}.
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="closeCurrentTermModal"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.22 5.22a.75.75 0 0 1 1.06 0L10 8.94l3.72-3.72a.75.75 0 1 1 1.06 1.06L11.06 10l3.72 3.72a.75.75 0 0 1-1.06 1.06L10 11.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06L8.94 10 5.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="saveCurrentTerm" class="mt-6 space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label for="dashboard-current-academic-year" value="Academic Session" />
                                <x-text-input id="dashboard-current-academic-year" wire:model="currentAcademicYear" type="text" class="mt-2" placeholder="2026/2027" />
                                <x-input-error :messages="$errors->get('currentAcademicYear')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dashboard-current-term" value="Term" />
                                <select id="dashboard-current-term" wire:model="currentTerm" class="pc-input mt-2">
                                    <option value="first">First term</option>
                                    <option value="second">Second term</option>
                                    <option value="third">Third term</option>
                                </select>
                                <x-input-error :messages="$errors->get('currentTerm')" class="mt-2" />
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            Payment and results screens will also use this saved term as their default tenant context.
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                            <button
                                type="button"
                                wire:click="closeCurrentTermModal"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                Save current term
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showParentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-6">
            <button
                type="button"
                wire:click="closeParentModal"
                class="fixed inset-0 bg-slate-950/45 backdrop-blur-[2px]"
                aria-label="Close parent form"
            ></button>

            <div class="relative mx-auto w-full max-w-2xl">
                <div class="pc-modal-panel px-6 py-6 sm:px-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="pc-eyebrow text-slate-400">School Admin</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Add Parent</h2>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                                Create a new parent account for {{ $school->name }}. The email and password entered here will be the parent's login details.
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="closeParentModal"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.22 5.22a.75.75 0 0 1 1.06 0L10 8.94l3.72-3.72a.75.75 0 1 1 1.06 1.06L11.06 10l3.72 3.72a.75.75 0 0 1-1.06 1.06L10 11.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06L8.94 10 5.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="saveParent" class="mt-6 space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input-label for="dashboard-parent-name" value="Full Name" />
                                <x-text-input id="dashboard-parent-name" wire:model="parentName" type="text" class="mt-2" />
                                <x-input-error :messages="$errors->get('parentName')" class="mt-2" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="dashboard-parent-email" value="Email Address" />
                                <x-text-input id="dashboard-parent-email" wire:model="parentEmail" type="email" class="mt-2" />
                                <x-input-error :messages="$errors->get('parentEmail')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dashboard-parent-password" value="Password" />
                                <x-text-input id="dashboard-parent-password" wire:model="parentPassword" type="password" class="mt-2" />
                                <x-input-error :messages="$errors->get('parentPassword')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dashboard-parent-password-confirmation" value="Confirm Password" />
                                <x-text-input id="dashboard-parent-password-confirmation" wire:model="parentPasswordConfirmation" type="password" class="mt-2" />
                                <x-input-error :messages="$errors->get('parentPasswordConfirmation')" class="mt-2" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="dashboard-parent-status" value="Status" />
                                <select id="dashboard-parent-status" wire:model="parentStatus" class="pc-input mt-2">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <x-input-error :messages="$errors->get('parentStatus')" class="mt-2" />
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600">
                            This parent will sign in with:
                            <span class="font-semibold text-slate-900">the email and password entered above</span>.
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                            <button type="button" wire:click="closeParentModal" class="pc-btn-secondary">
                                Cancel
                            </button>
                            <button type="submit" class="pc-btn-primary">
                                Create Parent
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showClassModal)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-6">
            <button
                type="button"
                wire:click="closeClassModal"
                class="fixed inset-0 bg-slate-950/45 backdrop-blur-[2px]"
                aria-label="Close class form"
            ></button>

            <div class="relative mx-auto w-full max-w-2xl">
                <div class="pc-modal-panel px-6 py-6 sm:px-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="pc-eyebrow text-slate-400">School Admin</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Add Class</h2>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                                Create a new class for {{ $school->name }} directly from the dashboard.
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="closeClassModal"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.22 5.22a.75.75 0 0 1 1.06 0L10 8.94l3.72-3.72a.75.75 0 1 1 1.06 1.06L11.06 10l3.72 3.72a.75.75 0 0 1-1.06 1.06L10 11.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06L8.94 10 5.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="saveClass" class="mt-6 space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label for="dashboard-class-name" value="Class Name" />
                                <x-text-input id="dashboard-class-name" wire:model="className" type="text" class="mt-2" />
                                <x-input-error :messages="$errors->get('className')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dashboard-class-section" value="Section" />
                                <x-text-input id="dashboard-class-section" wire:model="classSection" type="text" class="mt-2" />
                                <x-input-error :messages="$errors->get('classSection')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dashboard-class-code" value="Code" />
                                <x-text-input id="dashboard-class-code" wire:model="classCode" type="text" class="mt-2" />
                                <x-input-error :messages="$errors->get('classCode')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dashboard-class-capacity" value="Capacity" />
                                <x-text-input id="dashboard-class-capacity" wire:model="classCapacity" type="number" min="1" class="mt-2" />
                                <x-input-error :messages="$errors->get('classCapacity')" class="mt-2" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="dashboard-class-status" value="Status" />
                                <select id="dashboard-class-status" wire:model="classStatus" class="pc-input mt-2">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <x-input-error :messages="$errors->get('classStatus')" class="mt-2" />
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600">
                            Class names are unique per section inside this school, and class codes stay unique within the same school.
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                            <button type="button" wire:click="closeClassModal" class="pc-btn-secondary">
                                Cancel
                            </button>
                            <button type="submit" class="pc-btn-primary">
                                Create Class
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-6">
            <button
                type="button"
                wire:click="closePaymentModal"
                class="fixed inset-0 bg-slate-950/45 backdrop-blur-[2px]"
                aria-label="Close payment form"
            ></button>

            <div class="relative mx-auto w-full max-w-4xl">
                <div class="pc-modal-panel px-6 py-6 sm:px-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="pc-eyebrow text-slate-400">School Admin</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Add Payment Record</h2>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                                Record a payment balance for {{ $school->name }}. Balance and status are calculated automatically from amount due and amount paid.
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="closePaymentModal"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.22 5.22a.75.75 0 0 1 1.06 0L10 8.94l3.72-3.72a.75.75 0 1 1 1.06 1.06L11.06 10l3.72 3.72a.75.75 0 0 1-1.06 1.06L10 11.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06L8.94 10 5.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="savePayment" class="mt-6 space-y-6">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label for="dashboard-payment-student" value="Student" />
                                <select id="dashboard-payment-student" wire:model.live="paymentStudentId" class="pc-input mt-2">
                                    <option value="">Select student</option>
                                    @foreach ($paymentStudents as $student)
                                        <option value="{{ $student->id }}">
                                            {{ $student->first_name }} {{ $student->last_name }} - {{ $student->admission_number }}
                                            @if ($student->schoolClass)
                                                ({{ $student->schoolClass->name }}{{ $student->schoolClass->section ? ' / '.$student->schoolClass->section : '' }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('paymentStudentId')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dashboard-payment-parent" value="Linked Parent" />
                                <select id="dashboard-payment-parent" wire:model="paymentParentUserId" class="pc-input mt-2">
                                    <option value="">No parent selected</option>
                                    @foreach ($paymentParentOptions as $parentOption)
                                        <option value="{{ $parentOption->id }}">{{ $parentOption->name }} - {{ $parentOption->email }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('paymentParentUserId')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-3">
                            <div>
                                <x-input-label for="dashboard-payment-type" value="Payment Type" />
                                <x-text-input id="dashboard-payment-type" wire:model="paymentType" type="text" class="mt-2" />
                                <x-input-error :messages="$errors->get('paymentType')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dashboard-payment-session" value="Academic Year" />
                                <x-text-input id="dashboard-payment-session" wire:model="paymentAcademicYear" type="text" class="mt-2" placeholder="2026/2027" />
                                <x-input-error :messages="$errors->get('paymentAcademicYear')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dashboard-payment-term" value="Term" />
                                <select id="dashboard-payment-term" wire:model="paymentTerm" class="pc-input mt-2">
                                    <option value="first">First term</option>
                                    <option value="second">Second term</option>
                                    <option value="third">Third term</option>
                                </select>
                                <x-input-error :messages="$errors->get('paymentTerm')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label for="dashboard-payment-due" value="Amount Due" />
                                <x-text-input id="dashboard-payment-due" wire:model.live.debounce.300ms="paymentAmountDue" type="number" min="0" step="0.01" class="mt-2" />
                                <x-input-error :messages="$errors->get('paymentAmountDue')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dashboard-payment-paid" value="Amount Paid" />
                                <x-text-input id="dashboard-payment-paid" wire:model.live.debounce.300ms="paymentAmountPaid" type="number" min="0" step="0.01" class="mt-2" />
                                <x-input-error :messages="$errors->get('paymentAmountPaid')" class="mt-2" />
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 p-4">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <p class="pc-eyebrow text-slate-400">Status</p>
                                    <p class="mt-2 text-lg font-semibold capitalize text-slate-900">{{ $paymentPreview['status'] }}</p>
                                </div>
                                <div>
                                    <p class="pc-eyebrow text-slate-400">Balance</p>
                                    <p class="mt-2 text-lg font-semibold text-slate-900">NGN {{ number_format((float) $paymentPreview['balance'], 2) }}</p>
                                </div>
                                <div>
                                    <p class="pc-eyebrow text-slate-400">Paid amount</p>
                                    <p class="mt-2 text-lg font-semibold text-slate-900">NGN {{ number_format((float) $paymentPreview['amount_paid'], 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="dashboard-payment-notes" value="Notes" />
                            <textarea id="dashboard-payment-notes" wire:model="paymentNotes" rows="3" class="pc-input mt-2" placeholder="Optional notes for this payment record"></textarea>
                            <x-input-error :messages="$errors->get('paymentNotes')" class="mt-2" />
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                            <button type="button" wire:click="closePaymentModal" class="pc-btn-secondary">
                                Cancel
                            </button>
                            <button type="submit" class="pc-btn-primary">
                                Create Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
