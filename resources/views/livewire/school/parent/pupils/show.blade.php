<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header
            eyebrow="Parent"
            :title="$child->first_name.' '.$child->last_name"
            description="View pupil details, recent published results, and payment records linked to your account."
        >
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.parent.pupils.index', ['slug' => $school->slug])" wire:navigate>
                    Back to My Pupils
                </x-ui.secondary-button>
                <x-ui.primary-button :href="route('school.parent.payments.index', ['slug' => $school->slug])" wire:navigate>
                    View Balances
                </x-ui.primary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <article class="pc-card p-6">
                <h2 class="text-lg font-semibold text-slate-900">Pupil profile</h2>

                <dl class="mt-6 space-y-4 text-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">Admission number</dt>
                        <dd class="font-medium text-slate-900">{{ $child->admission_number }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">Class</dt>
                        <dd class="font-medium text-slate-900">
                            @if ($child->schoolClass)
                                {{ $child->schoolClass->name }}{{ $child->schoolClass->section ? ' / '.$child->schoolClass->section : '' }}
                            @else
                                Not assigned
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">Relationship</dt>
                        <dd class="font-medium text-slate-900">{{ ucfirst((string) ($child->pivot?->relationship_type ?? 'guardian')) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">Status</dt>
                        <dd><x-ui.status-badge :status="$child->status" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">Date of birth</dt>
                        <dd class="font-medium text-slate-900">{{ optional($child->date_of_birth)->format('d M Y') ?? 'Not provided' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Admitted</dt>
                        <dd class="font-medium text-slate-900">{{ optional($child->admitted_at)->format('d M Y') ?? 'Not provided' }}</dd>
                    </div>
                </dl>

                <div class="mt-6 rounded-[1.35rem] bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Outstanding balance</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">NGN {{ number_format($outstandingBalance, 2) }}</p>
                </div>
            </article>

            <div class="space-y-6">
                <section class="pc-card overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h2 class="text-lg font-semibold text-slate-900">Recent term results</h2>
                        <p class="text-sm text-slate-500">Only published results are shown here.</p>
                    </div>

                    <div class="space-y-3 p-4 sm:p-6">
                        @forelse ($recentResults as $result)
                            <article class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-900">{{ $result->subject_name }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ ucfirst((string) $result->term) }} / {{ $result->academic_year }}</p>
                                    </div>

                                    <div class="rounded-[1rem] bg-white px-3 py-2 text-right shadow-[0_12px_30px_-24px_rgba(15,23,42,0.22)]">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Grade</p>
                                        <p class="text-sm font-semibold text-slate-900">{{ $result->grade }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-4">
                                    <div class="rounded-[1rem] bg-white p-3 text-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CA</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) $result->ca_score, 2) }}</p>
                                    </div>
                                    <div class="rounded-[1rem] bg-white p-3 text-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Exam</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) $result->exam_score, 2) }}</p>
                                    </div>
                                    <div class="rounded-[1rem] bg-white p-3 text-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) $result->total_score, 2) }}</p>
                                    </div>
                                    <div class="rounded-[1rem] bg-white p-3 text-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Remark</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ $result->remark ?: 'N/A' }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="p-6">
                                <x-ui.empty-state
                                    title="No published results available"
                                    description="Published term results for this pupil will appear here when ready."
                                />
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="pc-card overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h2 class="text-lg font-semibold text-slate-900">Payment records</h2>
                        <p class="text-sm text-slate-500">Recent balances and payment history for this pupil.</p>
                    </div>

                    <div class="space-y-3 p-4 sm:p-6">
                        @forelse ($paymentHistory as $payment)
                            <article class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-900">{{ $payment->payment_type }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ ucfirst((string) $payment->term) }} / {{ $payment->academic_year }}</p>
                                    </div>

                                    <x-ui.status-badge :status="$payment->status" />
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-[1rem] bg-white p-3 text-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Amount due</p>
                                        <p class="mt-1 font-semibold text-slate-900">NGN {{ number_format((float) $payment->amount_due, 2) }}</p>
                                    </div>
                                    <div class="rounded-[1rem] bg-white p-3 text-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Amount paid</p>
                                        <p class="mt-1 font-semibold text-slate-900">NGN {{ number_format((float) $payment->amount_paid, 2) }}</p>
                                    </div>
                                    <div class="rounded-[1rem] bg-white p-3 text-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Balance</p>
                                        <p class="mt-1 font-semibold text-slate-900">NGN {{ number_format((float) $payment->balance, 2) }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="p-6">
                                <x-ui.empty-state
                                    title="No payment records yet"
                                    description="Payment records for this pupil will appear here when they are added."
                                />
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </section>
    </div>
</div>
