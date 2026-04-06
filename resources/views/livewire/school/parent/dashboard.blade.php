<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header
            eyebrow="Parent"
            title="Family Dashboard"
            :description="'Track linked pupils, recent published results, and fee balances for '.$school->name.' in one place.'"
        >
            <x-slot:actions>
                <x-ui.primary-button :href="route('school.parent.pupils.index', ['slug' => $school->slug])" wire:navigate>
                    View My Pupils
                </x-ui.primary-button>
                <x-ui.secondary-button :href="route('school.parent.payments.index', ['slug' => $school->slug])" wire:navigate>
                    View Balances
                </x-ui.secondary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
            @foreach ($metrics as $metric)
                <a href="{{ $metric['href'] }}" wire:navigate class="block transition hover:-translate-y-0.5">
                    <x-school-admin.stat-card :label="$metric['label']" :value="$metric['value']" :hint="$metric['hint']" class="h-full hover:border-[var(--pc-primary)]/25 hover:shadow-[0_20px_48px_-34px_rgba(13,59,102,0.32)]" />
                </a>
            @endforeach
        </section>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section class="pc-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Linked pupils</h2>
                        <p class="text-sm text-slate-500">Only children linked directly to your account appear here.</p>
                    </div>

                    <a
                        href="{{ route('school.parent.pupils.index', ['slug' => $school->slug]) }}"
                        wire:navigate
                        class="text-sm font-semibold text-[var(--pc-primary)] transition hover:text-[var(--pc-primary-deep)]"
                    >
                        View all
                    </a>
                </div>

                <div class="space-y-4 p-4 sm:p-6">
                    @forelse ($linkedChildren as $child)
                        <article class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-900">{{ $child->first_name }} {{ $child->last_name }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $child->admission_number }}
                                        @if ($child->schoolClass)
                                            - {{ $child->schoolClass->name }}{{ $child->schoolClass->section ? ' / '.$child->schoolClass->section : '' }}
                                        @endif
                                    </p>
                                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $child->pivot?->relationship_type ?? 'Linked child' }}</p>
                                </div>

                                <div class="flex flex-col items-start gap-2 lg:items-end">
                                    <x-ui.status-badge :status="$child->status" />

                                    <a
                                        href="{{ route('school.parent.pupils.show', ['slug' => $school->slug, 'student' => $child->id]) }}"
                                        wire:navigate
                                        class="pc-btn-primary px-3 py-2 text-sm"
                                    >
                                        View details
                                    </a>
                                </div>
                            </div>

                            <div class="mt-4 rounded-[1rem] bg-white px-4 py-3 text-sm text-slate-600 shadow-[0_12px_30px_-24px_rgba(15,23,42,0.22)]">
                                Outstanding balance:
                                <span class="font-semibold text-slate-900">NGN {{ number_format((float) $child->payments->sum('balance'), 2) }}</span>
                            </div>
                        </article>
                    @empty
                        <div class="p-6">
                            <x-ui.empty-state
                                title="No linked pupils found"
                                description="Linked children will appear here once your account is connected to a pupil."
                            />
                        </div>
                    @endforelse
                </div>
            </section>

            <div class="space-y-6">
                <section class="pc-card overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h2 class="text-lg font-semibold text-slate-900">Recent term results</h2>
                        <p class="text-sm text-slate-500">Latest published result records across your linked children.</p>
                    </div>

                    <div class="space-y-3 p-4 sm:p-6">
                        @forelse ($recentResults as $result)
                            <article class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-900">{{ $result->student->first_name }} {{ $result->student->last_name }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $result->subject_name }} / {{ ucfirst((string) $result->term) }} / {{ $result->academic_year }}</p>
                                    </div>

                                    <div class="rounded-[1rem] bg-white px-3 py-2 text-right shadow-[0_12px_30px_-24px_rgba(15,23,42,0.22)]">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Grade</p>
                                        <p class="text-sm font-semibold text-slate-900">{{ $result->grade }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-3">
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
                                </div>
                            </article>
                        @empty
                            <div class="p-6">
                                <x-ui.empty-state
                                    title="No published results yet"
                                    description="Recent published results for your linked pupils will appear here."
                                />
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="pc-card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Recent verified payments</h2>
                            <p class="text-sm text-slate-500">Latest school fee payments that were confirmed and applied to your linked children.</p>
                        </div>

                        <a
                            href="{{ route('school.parent.payments.index', ['slug' => $school->slug]) }}"
                            wire:navigate
                            class="text-sm font-semibold text-[var(--pc-primary)] transition hover:text-[var(--pc-primary-deep)]"
                        >
                            View all
                        </a>
                    </div>

                    <div class="space-y-3 p-4 sm:p-6">
                        @forelse ($recentPaidPayments as $payment)
                            <article class="rounded-[1.35rem] border border-emerald-200 bg-emerald-50/60 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-900">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $payment->payment_type }} / {{ ucfirst((string) $payment->term) }} / {{ $payment->academic_year }}</p>
                                        @if ($payment->reference)
                                            <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Reference {{ $payment->reference }}</p>
                                        @endif
                                    </div>

                                    <x-ui.status-badge :status="$payment->status" />
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-[1rem] bg-white p-3 text-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Amount paid</p>
                                        <p class="mt-1 font-semibold text-slate-900">NGN {{ number_format((float) $payment->amount_paid, 2) }}</p>
                                    </div>
                                    <div class="rounded-[1rem] bg-white p-3 text-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Confirmed</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ $payment->paid_at?->format('M j, Y g:i A') ?? 'Confirmed' }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="p-6">
                                <x-ui.empty-state
                                    title="No verified payments yet"
                                    description="Confirmed school fee payments will appear here once a payment has been processed successfully."
                                />
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
