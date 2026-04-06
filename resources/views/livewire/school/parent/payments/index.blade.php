<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header
            eyebrow="Parent"
            title="Child Payment Balances"
            :description="'See open balances and verified payment history for each child linked to your account at '.$school->name.'.'"
        >
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.parent.dashboard', ['slug' => $school->slug])" wire:navigate>
                    Back
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

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <x-school-admin.stat-card :label="$metric['label']" :value="$metric['value']" :hint="$metric['hint']" />
            @endforeach
        </section>

        @if ($paymentGatewayError)
            <div class="mt-6 rounded-[1.5rem] border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                Online payment is currently unavailable for this school. {{ $paymentGatewayError }}
            </div>
        @endif

        <section class="mt-6 space-y-4">
            @forelse ($children as $child)
                <article class="pc-card p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">{{ $child->first_name }} {{ $child->last_name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $child->admission_number }}
                                @if ($child->schoolClass)
                                    - {{ $child->schoolClass->name }}{{ $child->schoolClass->section ? ' / '.$child->schoolClass->section : '' }}
                                @endif
                            </p>
                        </div>
                        <div class="rounded-[1rem] bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            Outstanding:
                            <span class="font-semibold text-slate-900">NGN {{ number_format((float) $child->openPayments->sum('balance'), 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-5 space-y-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">Open balances</h3>
                                <span class="text-xs font-medium text-slate-400">{{ number_format($child->openPayments->count()) }} record(s)</span>
                            </div>

                            @forelse ($child->openPayments as $payment)
                                <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $payment->payment_type }}</p>
                                            <p class="mt-1 text-sm text-slate-500">{{ ucfirst((string) $payment->term) }} / {{ $payment->academic_year }}</p>
                                        </div>
                                        <div class="flex flex-col items-start gap-3 sm:items-end">
                                            <x-ui.status-badge :status="$payment->status" />

                                            @if ($paymentGateway)
                                                <button
                                                    type="button"
                                                    wire:click="initializePayment({{ $payment->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="initializePayment"
                                                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                                                >
                                                    Pay Now
                                                </button>
                                            @else
                                                <span class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400">
                                                    Gateway unavailable
                                                </span>
                                            @endif
                                        </div>
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
                                </div>
                            @empty
                                <x-ui.empty-state
                                    title="No outstanding balances"
                                    description="There are no open balances for this child right now."
                                />
                            @endforelse
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">Verified payment history</h3>
                                <span class="text-xs font-medium text-slate-400">{{ number_format($child->verifiedPayments->count()) }} record(s)</span>
                            </div>

                            @forelse ($child->verifiedPayments as $payment)
                                <div class="rounded-[1.35rem] border border-emerald-200 bg-emerald-50/60 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $payment->payment_type }}</p>
                                            <p class="mt-1 text-sm text-slate-500">{{ ucfirst((string) $payment->term) }} / {{ $payment->academic_year }}</p>
                                            @if ($payment->reference)
                                                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Reference {{ $payment->reference }}</p>
                                            @endif
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
                                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Confirmed</p>
                                            <p class="mt-1 font-semibold text-slate-900">{{ $payment->paid_at?->format('M j, Y g:i A') ?? 'Confirmed' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-[1.35rem] border border-dashed border-slate-200 p-6 text-sm text-slate-500">
                                    Verified payments for this child will appear here after successful backend confirmation.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </article>
            @empty
                <x-ui.empty-state
                    title="No linked children found"
                    description="Linked children will appear here once your account is connected to a pupil."
                    class="pc-card p-10"
                />
            @endforelse
        </section>

        <section class="pc-card mt-6 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">Recent verified transactions</h2>
                <p class="text-sm text-slate-500">Only confirmed and applied school fee payments are listed here.</p>
            </div>

            <div class="space-y-3 p-4 sm:p-6">
                @forelse ($recentPaidPayments as $payment)
                    <article class="rounded-[1.35rem] border border-emerald-200 bg-emerald-50/60 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $payment->payment_type }} / {{ ucfirst((string) $payment->term) }} / {{ $payment->academic_year }}</p>
                                @if ($payment->student->schoolClass)
                                    <p class="mt-1 text-sm text-slate-400">{{ $payment->student->schoolClass->name }}{{ $payment->student->schoolClass->section ? ' / '.$payment->student->schoolClass->section : '' }}</p>
                                @endif
                                @if ($payment->reference)
                                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Reference {{ $payment->reference }}</p>
                                @endif
                            </div>

                            <x-ui.status-badge :status="$payment->status" />
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[1rem] bg-white p-3 text-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Amount paid</p>
                                <p class="mt-1 font-semibold text-slate-900">NGN {{ number_format((float) $payment->amount_paid, 2) }}</p>
                            </div>
                            <div class="rounded-[1rem] bg-white p-3 text-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Payment method</p>
                                <p class="mt-1 font-semibold text-slate-900">{{ $payment->payment_method ? ucfirst($payment->payment_method) : 'Recorded payment' }}</p>
                            </div>
                            <div class="rounded-[1rem] bg-white p-3 text-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Confirmed</p>
                                <p class="mt-1 font-semibold text-slate-900">{{ $payment->paid_at?->format('M j, Y g:i A') ?? 'Confirmed' }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state
                        title="No verified payments yet"
                        description="Confirmed school fee transactions will appear here after backend verification."
                        class="py-8"
                    />
                @endforelse
            </div>
        </section>
    </div>
</div>
