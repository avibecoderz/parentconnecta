<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header eyebrow="Teacher" title="Outstanding Payments" description="View unpaid and partial balances for students in your assigned classes only.">
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.teacher.dashboard', ['slug' => $school->slug])" wire:navigate>
                    Back
                </x-ui.secondary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <x-school-admin.stat-card :label="$metric['label']" :value="$metric['value']" :hint="$metric['hint']" />
            @endforeach
        </section>

        <section class="pc-card mt-6 overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Class balances</h2>
                    <p class="text-sm text-slate-500">Only unpaid and partial records appear here.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.search-input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search students or payments..."
                        class="sm:w-80"
                    />

                    <select wire:model.live="classFilter" class="rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-900 focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]">
                        <option value="all">All assigned classes</option>
                        @foreach ($assignedClasses as $assignedClass)
                            <option value="{{ $assignedClass->id }}">
                                {{ $assignedClass->name }}{{ $assignedClass->section ? ' / '.$assignedClass->section : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-4 p-4 sm:p-6">
                @forelse ($payments as $payment)
                    <article class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $payment->student->admission_number }}
                                    @if ($payment->student->schoolClass)
                                        - {{ $payment->student->schoolClass->name }}{{ $payment->student->schoolClass->section ? ' / '.$payment->student->schoolClass->section : '' }}
                                    @endif
                                </p>
                                <p class="mt-1 text-sm text-slate-500">{{ $payment->payment_type }} / {{ ucfirst((string) $payment->term) }} / {{ $payment->academic_year }}</p>
                            </div>

                            <x-ui.status-badge :status="$payment->status" />
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[1rem] bg-white p-3 text-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Due</p>
                                <p class="mt-1 font-semibold text-slate-900">NGN {{ number_format((float) $payment->amount_due, 2) }}</p>
                            </div>
                            <div class="rounded-[1rem] bg-white p-3 text-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Paid</p>
                                <p class="mt-1 font-semibold text-slate-900">NGN {{ number_format((float) $payment->amount_paid, 2) }}</p>
                            </div>
                            <div class="rounded-[1rem] bg-white p-3 text-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Balance</p>
                                <p class="mt-1 font-semibold text-slate-900">NGN {{ number_format((float) $payment->balance, 2) }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state
                        title="No outstanding payments matched"
                        description="Try a different search term or class filter."
                    />
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $payments->links() }}
            </div>
        </section>
    </div>
</div>
