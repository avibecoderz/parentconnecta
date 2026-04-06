<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header
            eyebrow="Parent"
            :title="$title"
            :description="$description"
        >
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.parent.dashboard', ['slug' => $school->slug])" wire:navigate>
                    Back
                </x-ui.secondary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        <section class="grid gap-4 md:grid-cols-3">
            <x-school-admin.stat-card label="Records" :value="number_format($recordCount)" hint="Payment records matching your current filters" />
            <x-school-admin.stat-card label="Total balance" :value="'NGN '.number_format($totalBalance, 2)" hint="Outstanding amount across the filtered records" />
            <x-school-admin.stat-card label="Total paid" :value="'NGN '.number_format($totalPaid, 2)" hint="Amount already settled across the filtered records" />
        </section>

        <section class="pc-card mt-6 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">Filter records</h2>
                <p class="text-sm text-slate-500">Search by child, payment type, session, or reference. Narrow results by term or date.</p>
            </div>

            <form wire:submit="searchRecords" class="grid gap-4 px-6 py-5 md:grid-cols-2 xl:grid-cols-5">
                <div class="xl:col-span-2">
                    <label for="payment-search" class="text-sm font-medium text-slate-700">Search</label>
                    <input id="payment-search" type="text" wire:model.defer="search" class="pc-input mt-2" placeholder="Child name, admission no, payment type, session, reference">
                </div>

                <div>
                    <label for="payment-term" class="text-sm font-medium text-slate-700">Term</label>
                    <select id="payment-term" wire:model="termFilter" class="pc-input mt-2">
                        <option value="all">All terms</option>
                        <option value="first">First term</option>
                        <option value="second">Second term</option>
                        <option value="third">Third term</option>
                    </select>
                </div>

                <div>
                    <label for="payment-from" class="text-sm font-medium text-slate-700">From date</label>
                    <input id="payment-from" type="date" wire:model="dateFrom" class="pc-input mt-2">
                </div>

                <div>
                    <label for="payment-to" class="text-sm font-medium text-slate-700">To date</label>
                    <input id="payment-to" type="date" wire:model="dateTo" class="pc-input mt-2">
                </div>

                <div class="md:col-span-2 xl:col-span-5 flex flex-wrap gap-3">
                    <x-ui.primary-button type="submit">
                        Search
                    </x-ui.primary-button>
                    <x-ui.secondary-button type="button" wire:click="clearFilters">
                        Clear filters
                    </x-ui.secondary-button>
                </div>
            </form>

            <div class="space-y-3 p-4 sm:p-6">
                @forelse ($records as $payment)
                    <article @class([
                        'rounded-[1.35rem] border p-4',
                        'border-emerald-200 bg-emerald-50/60' => $scope === 'paid-records',
                        'border-slate-200 bg-slate-50' => $scope !== 'paid-records',
                    ])>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
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

                        <div class="mt-4 grid gap-3 sm:grid-cols-4">
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
                            <div class="rounded-[1rem] bg-white p-3 text-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ $scope === 'paid-records' ? 'Confirmed' : 'Recorded' }}</p>
                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ ($scope === 'paid-records' ? $payment->paid_at : $payment->created_at)?->format('M j, Y') ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state
                        :title="'No records found'"
                        description="No payment records matched the current filters."
                        class="py-8"
                    />
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $records->links() }}
            </div>
        </section>
    </div>
</div>
