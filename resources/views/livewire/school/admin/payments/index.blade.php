<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header eyebrow="School Admin" title="Payments Tracking" description="Track school balances by student, term, and session without a payment gateway yet.">
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.admin.dashboard', ['slug' => $school->slug])" wire:navigate>Back</x-ui.secondary-button>
                <x-ui.primary-button :href="route('school.admin.payments.index', ['slug' => $school->slug, 'create' => 1])" wire:navigate>Add Payment</x-ui.primary-button>
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
                <x-school-admin.stat-card :label="$metric['label']" :value="$metric['value']" :hint="$metric['hint']" />
            @endforeach
        </section>

        <section class="pc-card mt-6 overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Payment records</h2>
                    <p class="text-sm text-slate-500">Balances are automatically recalculated from amount due and amount paid.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Search payments..." class="sm:w-80" />

                    <select wire:model.live="statusFilter" class="rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500">
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}">{{ ucfirst($statusOption) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                            <th class="px-6 py-4">Student</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Session</th>
                            <th class="px-6 py-4">Due</th>
                            <th class="px-6 py-4">Paid</th>
                            <th class="px-6 py-4">Balance</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($payments as $payment)
                            <tr class="align-top">
                                <td class="px-6 py-5">
                                    <p class="text-sm font-semibold text-slate-900">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $payment->student->admission_number }}
                                        @if ($payment->student->schoolClass)
                                            - {{ $payment->student->schoolClass->name }}{{ $payment->student->schoolClass->section ? ' / '.$payment->student->schoolClass->section : '' }}
                                        @endif
                                    </p>
                                    <p class="mt-1 text-xs font-medium uppercase tracking-[0.14em] text-slate-400">{{ $payment->reference }}</p>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">{{ $payment->payment_type }}</td>
                                <td class="px-6 py-5 text-sm text-slate-500">{{ ucfirst((string) $payment->term) }} / {{ $payment->academic_year }}</td>
                                <td class="px-6 py-5 text-sm font-medium text-slate-700">NGN {{ number_format((float) $payment->amount_due, 2) }}</td>
                                <td class="px-6 py-5 text-sm text-slate-500">NGN {{ number_format((float) $payment->amount_paid, 2) }}</td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-900">NGN {{ number_format((float) $payment->balance, 2) }}</td>
                                <td class="px-6 py-5">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                                        'bg-emerald-100 text-emerald-700' => $payment->status === 'paid',
                                        'bg-amber-100 text-amber-700' => $payment->status === 'partial',
                                        'bg-rose-100 text-rose-700' => $payment->status === 'unpaid',
                                    ])>
                                        {{ $payment->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="editPayment({{ $payment->id }})"
                                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="deletePayment({{ $payment->id }})"
                                            wire:confirm="Delete this payment record?"
                                            class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No payment records matched your current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 lg:hidden">
                @forelse ($payments as $payment)
                    <article class="rounded-2xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $payment->payment_type }} / {{ $payment->academic_year }}</p>
                            </div>
                            <span @class([
                                'inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                                'bg-emerald-100 text-emerald-700' => $payment->status === 'paid',
                                'bg-amber-100 text-amber-700' => $payment->status === 'partial',
                                'bg-rose-100 text-rose-700' => $payment->status === 'unpaid',
                            ])>
                                {{ $payment->status }}
                            </span>
                        </div>

                        <dl class="mt-4 space-y-2 text-sm text-slate-500">
                            <div class="flex justify-between gap-3">
                                <dt>Due</dt>
                                <dd class="text-right text-slate-700">NGN {{ number_format((float) $payment->amount_due, 2) }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Paid</dt>
                                <dd class="text-right text-slate-700">NGN {{ number_format((float) $payment->amount_paid, 2) }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Balance</dt>
                                <dd class="text-right font-semibold text-slate-900">NGN {{ number_format((float) $payment->balance, 2) }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button
                                type="button"
                                wire:click="editPayment({{ $payment->id }})"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                wire:click="deletePayment({{ $payment->id }})"
                                wire:confirm="Delete this payment record?"
                                class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700"
                            >
                                Delete
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                        No payment records matched your current filters.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $payments->links() }}
            </div>
        </section>
    </div>

    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-8">
            <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">{{ $editingPaymentId ? 'Edit payment record' : 'Add payment record' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Balance and status are recalculated automatically every time you save.</p>
                    </div>

                    <button
                        type="button"
                        wire:click="closePaymentModal"
                        class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="savePayment" class="space-y-6 px-6 py-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Student</label>
                            <select wire:model.live="studentId" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <option value="">Select student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ $student->first_name }} {{ $student->last_name }} - {{ $student->admission_number }}
                                        @if ($student->schoolClass)
                                            ({{ $student->schoolClass->name }}{{ $student->schoolClass->section ? ' / '.$student->schoolClass->section : '' }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('studentId') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Linked parent</label>
                            <select wire:model.defer="parentUserId" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <option value="">No parent selected</option>
                                @foreach ($parentOptions as $parentOption)
                                    <option value="{{ $parentOption->id }}">{{ $parentOption->name }} - {{ $parentOption->email }}</option>
                                @endforeach
                            </select>
                            @error('parentUserId') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Payment type</label>
                            <input type="text" wire:model.defer="paymentType" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500" placeholder="School fees">
                            @error('paymentType') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Session</label>
                            <input type="text" wire:model.defer="academicYear" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500" placeholder="2026/2027">
                            @error('academicYear') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Term</label>
                            <select wire:model.defer="term" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <option value="first">First term</option>
                                <option value="second">Second term</option>
                                <option value="third">Third term</option>
                            </select>
                            @error('term') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Amount due</label>
                            <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="amountDue" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500" placeholder="0.00">
                            @error('amountDue') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Amount paid</label>
                            <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="amountPaid" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500" placeholder="0.00">
                            @error('amountPaid') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</p>
                                <p class="mt-2 text-lg font-semibold capitalize text-slate-900">{{ $preview['status'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Balance</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">NGN {{ number_format((float) $preview['balance'], 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Paid amount</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">NGN {{ number_format((float) $preview['amount_paid'], 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Notes</label>
                        <textarea wire:model.defer="notes" rows="3" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500" placeholder="Optional notes for this balance"></textarea>
                        @error('notes') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            wire:click="closePaymentModal"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            {{ $editingPaymentId ? 'Save changes' : 'Create payment record' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
