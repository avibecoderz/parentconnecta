<div class="min-h-screen bg-slate-50">
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-sky-600">Super Admin</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-900">Schools</h1>
                <p class="mt-1 text-sm text-slate-600">Create, update, suspend, and manage tenant access for every school.</p>
            </div>

            <button
                type="button"
                wire:click="createSchool"
                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
            >
                Add school
            </button>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @unless ($paymentSettingsAvailable)
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Payment configuration is unavailable because the `school_payment_settings` table has not been migrated yet.
            </div>
        @endunless

        @unless ($planColumnAvailable)
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Plan management is unavailable because the `plan` column has not been migrated on the `schools` table yet.
            </div>
        @endunless

        @if ($createdSchoolCredentials)
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white px-5 py-5 text-sm shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-slate-900">School successfully provisioned</p>
                        <p class="mt-1 text-slate-600">The school, slug, and school admin account were created together.</p>
                    </div>

                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">
                        Active by default
                    </span>
                </div>

                <dl class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">School</dt>
                        <dd class="mt-2 font-medium text-slate-900">{{ $createdSchoolCredentials['school_name'] }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Generated slug</dt>
                        <dd class="mt-2 font-mono text-slate-900">{{ $createdSchoolCredentials['school_slug'] }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Portal URL</dt>
                        <dd class="mt-2 break-all text-slate-900">{{ $createdSchoolCredentials['school_url'] }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Admin email</dt>
                        <dd class="mt-2 break-all text-slate-900">{{ $createdSchoolCredentials['admin_email'] }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Admin password</dt>
                        <dd class="mt-2 font-mono text-slate-900">{{ $createdSchoolCredentials['admin_password'] }}</dd>
                    </div>
                </dl>
            </div>
        @endif

        @if ($temporaryPassword)
            <div class="mb-6 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4 text-sm text-sky-800">
                <p class="font-semibold">Temporary school admin password generated for {{ $temporaryPasswordSchoolName }}.</p>
                <p class="mt-2">
                    Password:
                    <span class="rounded bg-white px-2 py-1 font-mono text-slate-900 shadow-sm">{{ $temporaryPassword }}</span>
                </p>
                <p class="mt-2 text-sky-700">Share it securely. It will not be shown again after this page refreshes.</p>
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total schools</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($metrics['total']) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Active</p>
                <p class="mt-3 text-3xl font-semibold text-emerald-600">{{ number_format($metrics['active']) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Suspended</p>
                <p class="mt-3 text-3xl font-semibold text-rose-600">{{ number_format($metrics['suspended']) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Inactive</p>
                <p class="mt-3 text-3xl font-semibold text-amber-600">{{ number_format($metrics['inactive']) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Payments configured</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($metrics['payment_configured']) }}</p>
                <p class="mt-2 text-xs uppercase tracking-[0.16em] text-slate-400">Any gateway record saved</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Payments active</p>
                <p class="mt-3 text-3xl font-semibold text-emerald-600">{{ number_format($metrics['payment_active']) }}</p>
                <p class="mt-2 text-xs uppercase tracking-[0.16em] text-slate-400">Ready for tenant checkout</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Live mode</p>
                <p class="mt-3 text-3xl font-semibold text-sky-600">{{ number_format($metrics['payment_live']) }}</p>
                <p class="mt-2 text-xs uppercase tracking-[0.16em] text-slate-400">Active production gateways</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Test mode</p>
                <p class="mt-3 text-3xl font-semibold text-amber-600">{{ number_format($metrics['payment_test']) }}</p>
                <p class="mt-2 text-xs uppercase tracking-[0.16em] text-slate-400">Active sandbox gateways</p>
            </article>
        </section>

        <section class="mt-6 rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Tenant directory</h2>
                    <p class="text-sm text-slate-500">Search schools or school admin credentials.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search schools or admins..."
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:ring-sky-500 sm:w-80"
                    >

                    <select
                        wire:model.live="statusFilter"
                        class="rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500"
                    >
                        <option value="all">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                            <th class="px-6 py-4">School</th>
                            <th class="px-6 py-4">School admin</th>
                            <th class="px-6 py-4">Usage</th>
                            <th class="px-6 py-4">Plan</th>
                            <th class="px-6 py-4">Payments</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($schools as $school)
                            @php($admin = $school->users->first())
                            @php($usage = $school->plan_usage)
                            <tr class="align-top">
                                <td class="px-6 py-5">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $school->name }}</p>
                                        <p class="mt-1 text-sm text-slate-500">/school/{{ $school->slug }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $school->email ?: 'No school email' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-medium text-slate-900">{{ $admin?->name ?? 'No admin assigned' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $admin?->email ?? 'No admin email' }}</p>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-slate-500">Teachers</span>
                                            <span @class([
                                                'font-semibold',
                                                'text-slate-700' => ! $usage['teachers']['near_limit'],
                                                'text-amber-700' => $usage['teachers']['near_limit'] && ! $usage['teachers']['at_limit'],
                                                'text-rose-700' => $usage['teachers']['at_limit'],
                                            ])>{{ $usage['teachers']['label'] }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-slate-500">Students</span>
                                            <span @class([
                                                'font-semibold',
                                                'text-slate-700' => ! $usage['students']['near_limit'],
                                                'text-amber-700' => $usage['students']['near_limit'] && ! $usage['students']['at_limit'],
                                                'text-rose-700' => $usage['students']['at_limit'],
                                            ])>{{ $usage['students']['label'] }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-slate-500">Parents</span>
                                            <span @class([
                                                'font-semibold',
                                                'text-slate-700' => ! $usage['parents']['near_limit'],
                                                'text-amber-700' => $usage['parents']['near_limit'] && ! $usage['parents']['at_limit'],
                                                'text-rose-700' => $usage['parents']['at_limit'],
                                            ])>{{ $usage['parents']['label'] }}</span>
                                        </div>
                                        @if ($usage['has_limit_reached'])
                                            <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-700">
                                                Limit reached
                                            </span>
                                        @elseif ($usage['has_warning'])
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">
                                                Near limit
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @php($resolvedPlan = in_array((string) $school->plan, $planOptions, true) ? (string) $school->plan : 'free')
                                    <div class="space-y-2">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.14em]',
                                            'bg-slate-100 text-slate-700' => $resolvedPlan === 'free',
                                            'bg-sky-100 text-sky-700' => $resolvedPlan === 'basic',
                                            'bg-amber-100 text-amber-700' => $resolvedPlan === 'premium',
                                        ])>
                                            {{ $resolvedPlan }}
                                        </span>
                                        <p class="text-xs text-slate-500">Classrooms: {{ number_format($school->school_classes_count) }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if ($school->paymentSetting)
                                        <div class="space-y-2 text-sm">
                                            <div class="flex flex-wrap gap-2">
                                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                                    {{ ucfirst($school->paymentSetting->gateway_name) }}
                                                </span>
                                                <span @class([
                                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                                    'bg-emerald-100 text-emerald-700' => $school->paymentSetting->is_active,
                                                    'bg-amber-100 text-amber-700' => ! $school->paymentSetting->is_active,
                                                ])>
                                                    {{ $school->paymentSetting->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                <span @class([
                                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                                    'bg-sky-100 text-sky-700' => $school->paymentSetting->paystack_mode === 'live',
                                                    'bg-slate-100 text-slate-700' => $school->paymentSetting->paystack_mode !== 'live',
                                                ])>
                                                    {{ strtoupper($school->paymentSetting->paystack_mode) }}
                                                </span>
                                            </div>

                                            <p class="text-slate-500">
                                                {{ filled($school->paymentSetting->paystack_public_key) ? 'Public key saved' : 'Public key missing' }}
                                            </p>
                                            <p class="text-slate-500">
                                                Updated {{ $school->paymentSetting->updated_at?->format('M j, Y') ?? 'recently' }}
                                            </p>
                                        </div>
                                    @else
                                        <div class="space-y-2 text-sm">
                                            <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-700">
                                                Not configured
                                            </span>
                                            <p class="text-slate-500">Online payment setup has not been added yet.</p>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                                        'bg-emerald-100 text-emerald-700' => $school->status === 'active',
                                        'bg-amber-100 text-amber-700' => $school->status === 'inactive',
                                        'bg-rose-100 text-rose-700' => $school->status === 'suspended',
                                    ])>
                                        {{ $school->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button type="button" wire:click="editSchool({{ $school->id }})" aria-label="Edit school" title="Edit school" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-300">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M14.69 2.86a1.5 1.5 0 0 1 2.12 2.12l-8.4 8.4a1.5 1.5 0 0 1-.64.38l-3.08.88a.75.75 0 0 1-.93-.93l.88-3.08a1.5 1.5 0 0 1 .38-.64l8.4-8.4ZM13.63 4.98 5.7 12.9l-.47 1.66 1.66-.47 7.93-7.92-1.19-1.19Z" /></svg>
                                            <span class="sr-only">Edit</span>
                                        </button>
                                        <button type="button" wire:click="toggleSchoolSuspension({{ $school->id }})" aria-label="{{ $school->status === 'suspended' ? 'Activate school' : 'Suspend school' }}" title="{{ $school->status === 'suspended' ? 'Activate school' : 'Suspend school' }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-amber-200 text-amber-700 transition hover:bg-amber-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">
                                            @if ($school->status === 'suspended')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.78-8.53a.75.75 0 0 0-1.06-1.06l-3.47 3.47-1.97-1.97a.75.75 0 0 0-1.06 1.06l2.5 2.5a.75.75 0 0 0 1.06 0l4-4Z" clip-rule="evenodd" /></svg>
                                            @else
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3-8a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h4.5A.75.75 0 0 0 13 10Z" clip-rule="evenodd" /></svg>
                                            @endif
                                            <span class="sr-only">{{ $school->status === 'suspended' ? 'Activate' : 'Suspend' }}</span>
                                        </button>
                                        <button type="button" wire:click="resetSchoolAdminPassword({{ $school->id }})" aria-label="Reset admin password" title="Reset admin password" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-sky-200 text-sky-700 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-300">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a4 4 0 0 0-4 4v1H5.75A1.75 1.75 0 0 0 4 8.75v5.5C4 15.22 4.78 16 5.75 16h8.5c.97 0 1.75-.78 1.75-1.75v-5.5C16 7.78 15.22 7 14.25 7H14V6a4 4 0 0 0-4-4Zm2.5 5V6a2.5 2.5 0 0 0-5 0v1h5Zm-2.5 2a1.25 1.25 0 0 1 .75 2.25V13a.75.75 0 0 1-1.5 0v-1.75A1.25 1.25 0 0 1 10 9Z" clip-rule="evenodd" /></svg>
                                            <span class="sr-only">Reset admin password</span>
                                        </button>
                                        <button type="button" wire:click="editPaymentSettings({{ $school->id }})" @disabled(! $paymentSettingsAvailable) aria-label="Payment settings" title="Payment settings" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-emerald-200 text-emerald-700 transition hover:bg-emerald-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3.5 5.75A1.75 1.75 0 0 1 5.25 4h9.5A1.75 1.75 0 0 1 16.5 5.75v8.5A1.75 1.75 0 0 1 14.75 16h-9.5A1.75 1.75 0 0 1 3.5 14.25v-8.5Zm1.5 1.25v1.5h10V7H5Zm0 3v4.25c0 .14.11.25.25.25h9.5a.25.25 0 0 0 .25-.25V10H5Zm1.5 1.25A.75.75 0 0 1 7.25 10.5h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" /></svg>
                                            <span class="sr-only">Payment settings</span>
                                        </button>
                                        <button type="button" wire:click="confirmSchoolDeletion({{ $school->id }})" aria-label="Delete school" title="Delete school" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-rose-200 text-rose-700 transition hover:bg-rose-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-300">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.75 3a1 1 0 0 0-.97.757L7.53 5H5.75a.75.75 0 0 0 0 1.5h.53l.58 8.12A1.5 1.5 0 0 0 8.36 16h3.28a1.5 1.5 0 0 0 1.5-1.38l.58-8.12h.53a.75.75 0 0 0 0-1.5h-1.78l-.25-1.243A1 1 0 0 0 11.25 3h-2.5Zm-.93 3.5.55 7.7a.25.25 0 0 0 .25.23h3.28a.25.25 0 0 0 .25-.23l.55-7.7H7.82Zm1.93 1.25a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V8.5a.75.75 0 0 1 .75-.75Zm2.5 0a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V8.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" /></svg>
                                            <span class="sr-only">Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No schools matched your current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 lg:hidden">
                @forelse ($schools as $school)
                    @php($admin = $school->users->first())
                    @php($usage = $school->plan_usage)
                    <article class="rounded-2xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">{{ $school->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">/school/{{ $school->slug }}</p>
                            </div>

                            <span @class([
                                'inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                                'bg-emerald-100 text-emerald-700' => $school->status === 'active',
                                'bg-amber-100 text-amber-700' => $school->status === 'inactive',
                                'bg-rose-100 text-rose-700' => $school->status === 'suspended',
                            ])>
                                {{ $school->status }}
                            </span>
                        </div>

                        <dl class="mt-4 space-y-2 text-sm text-slate-500">
                            <div class="flex justify-between gap-3">
                                <dt>Admin</dt>
                                <dd class="text-right text-slate-700">{{ $admin?->name ?? 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Email</dt>
                                <dd class="text-right text-slate-700">{{ $admin?->email ?? 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Teachers</dt>
                                <dd @class([
                                    'text-right font-semibold',
                                    'text-slate-700' => ! $usage['teachers']['near_limit'],
                                    'text-amber-700' => $usage['teachers']['near_limit'] && ! $usage['teachers']['at_limit'],
                                    'text-rose-700' => $usage['teachers']['at_limit'],
                                ])>{{ $usage['teachers']['label'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Students</dt>
                                <dd @class([
                                    'text-right font-semibold',
                                    'text-slate-700' => ! $usage['students']['near_limit'],
                                    'text-amber-700' => $usage['students']['near_limit'] && ! $usage['students']['at_limit'],
                                    'text-rose-700' => $usage['students']['at_limit'],
                                ])>{{ $usage['students']['label'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Parents</dt>
                                <dd @class([
                                    'text-right font-semibold',
                                    'text-slate-700' => ! $usage['parents']['near_limit'],
                                    'text-amber-700' => $usage['parents']['near_limit'] && ! $usage['parents']['at_limit'],
                                    'text-rose-700' => $usage['parents']['at_limit'],
                                ])>{{ $usage['parents']['label'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Plan</dt>
                                @php($resolvedPlan = in_array((string) $school->plan, $planOptions, true) ? (string) $school->plan : 'free')
                                <dd class="text-right">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.14em]',
                                        'bg-slate-100 text-slate-700' => $resolvedPlan === 'free',
                                        'bg-sky-100 text-sky-700' => $resolvedPlan === 'basic',
                                        'bg-amber-100 text-amber-700' => $resolvedPlan === 'premium',
                                    ])>
                                        {{ $resolvedPlan }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Classrooms</dt>
                                <dd class="text-right text-slate-700">{{ number_format($school->school_classes_count) }}</dd>
                            </div>
                            @if ($usage['has_limit_reached'])
                                <div class="flex justify-between gap-3">
                                    <dt>Alert</dt>
                                    <dd class="text-right font-medium text-rose-700">Limit reached</dd>
                                </div>
                            @elseif ($usage['has_warning'])
                                <div class="flex justify-between gap-3">
                                    <dt>Alert</dt>
                                    <dd class="text-right font-medium text-amber-700">Near limit</dd>
                                </div>
                            @endif
                            <div class="flex justify-between gap-3">
                                <dt>Payments</dt>
                                <dd class="text-right">
                                    @if ($school->paymentSetting?->is_active)
                                        <span class="font-medium text-emerald-700">{{ ucfirst($school->paymentSetting->gateway_name) }} / {{ strtoupper($school->paymentSetting->paystack_mode) }}</span>
                                    @elseif ($school->paymentSetting)
                                        <span class="font-medium text-amber-700">Configured / Inactive</span>
                                    @else
                                        <span class="font-medium text-rose-700">Not configured</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($school->paymentSetting)
                                <div class="flex justify-between gap-3">
                                    <dt>Updated</dt>
                                    <dd class="text-right text-slate-700">{{ $school->paymentSetting->updated_at?->format('M j, Y') ?? 'Recently' }}</dd>
                                </div>
                            @endif
                        </dl>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" wire:click="editSchool({{ $school->id }})" aria-label="Edit school" title="Edit school" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-300">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M14.69 2.86a1.5 1.5 0 0 1 2.12 2.12l-8.4 8.4a1.5 1.5 0 0 1-.64.38l-3.08.88a.75.75 0 0 1-.93-.93l.88-3.08a1.5 1.5 0 0 1 .38-.64l8.4-8.4ZM13.63 4.98 5.7 12.9l-.47 1.66 1.66-.47 7.93-7.92-1.19-1.19Z" /></svg>
                                <span class="sr-only">Edit</span>
                            </button>
                            <button type="button" wire:click="toggleSchoolSuspension({{ $school->id }})" aria-label="{{ $school->status === 'suspended' ? 'Activate school' : 'Suspend school' }}" title="{{ $school->status === 'suspended' ? 'Activate school' : 'Suspend school' }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-amber-200 text-amber-700 transition hover:bg-amber-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">
                                @if ($school->status === 'suspended')
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.78-8.53a.75.75 0 0 0-1.06-1.06l-3.47 3.47-1.97-1.97a.75.75 0 0 0-1.06 1.06l2.5 2.5a.75.75 0 0 0 1.06 0l4-4Z" clip-rule="evenodd" /></svg>
                                @else
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3-8a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h4.5A.75.75 0 0 0 13 10Z" clip-rule="evenodd" /></svg>
                                @endif
                                <span class="sr-only">{{ $school->status === 'suspended' ? 'Activate' : 'Suspend' }}</span>
                            </button>
                            <button type="button" wire:click="resetSchoolAdminPassword({{ $school->id }})" aria-label="Reset admin password" title="Reset admin password" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-sky-200 text-sky-700 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-300">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a4 4 0 0 0-4 4v1H5.75A1.75 1.75 0 0 0 4 8.75v5.5C4 15.22 4.78 16 5.75 16h8.5c.97 0 1.75-.78 1.75-1.75v-5.5C16 7.78 15.22 7 14.25 7H14V6a4 4 0 0 0-4-4Zm2.5 5V6a2.5 2.5 0 0 0-5 0v1h5Zm-2.5 2a1.25 1.25 0 0 1 .75 2.25V13a.75.75 0 0 1-1.5 0v-1.75A1.25 1.25 0 0 1 10 9Z" clip-rule="evenodd" /></svg>
                                <span class="sr-only">Reset admin password</span>
                            </button>
                            <button type="button" wire:click="editPaymentSettings({{ $school->id }})" @disabled(! $paymentSettingsAvailable) aria-label="Payment settings" title="Payment settings" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-emerald-200 text-emerald-700 transition hover:bg-emerald-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3.5 5.75A1.75 1.75 0 0 1 5.25 4h9.5A1.75 1.75 0 0 1 16.5 5.75v8.5A1.75 1.75 0 0 1 14.75 16h-9.5A1.75 1.75 0 0 1 3.5 14.25v-8.5Zm1.5 1.25v1.5h10V7H5Zm0 3v4.25c0 .14.11.25.25.25h9.5a.25.25 0 0 0 .25-.25V10H5Zm1.5 1.25A.75.75 0 0 1 7.25 10.5h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" /></svg>
                                <span class="sr-only">Payment settings</span>
                            </button>
                            <button type="button" wire:click="confirmSchoolDeletion({{ $school->id }})" aria-label="Delete school" title="Delete school" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-rose-200 text-rose-700 transition hover:bg-rose-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-300">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.75 3a1 1 0 0 0-.97.757L7.53 5H5.75a.75.75 0 0 0 0 1.5h.53l.58 8.12A1.5 1.5 0 0 0 8.36 16h3.28a1.5 1.5 0 0 0 1.5-1.38l.58-8.12h.53a.75.75 0 0 0 0-1.5h-1.78l-.25-1.243A1 1 0 0 0 11.25 3h-2.5Zm-.93 3.5.55 7.7a.25.25 0 0 0 .25.23h3.28a.25.25 0 0 0 .25-.23l.55-7.7H7.82Zm1.93 1.25a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V8.5a.75.75 0 0 1 .75-.75Zm2.5 0a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V8.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" /></svg>
                                <span class="sr-only">Delete</span>
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                        No schools matched your current filters.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $schools->links() }}
            </div>
        </section>
    </div>

    @if ($showSchoolModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-8">
            <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">
                            {{ $editingSchoolId ? 'Edit school' : 'Add school' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">Manage tenant details and school admin access in one place.</p>
                    </div>

                    <button type="button" wire:click="closeSchoolModal" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveSchool" class="grid gap-6 px-6 py-6 lg:grid-cols-2">
                    <section class="space-y-5">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">School details</h3>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">School name</label>
                            <input type="text" wire:model.defer="name" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('name') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Slug</label>
                            <input type="text" wire:model.defer="slug" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('slug') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-slate-700">School email</label>
                                <input type="email" wire:model.defer="email" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                @error('email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-700">Phone</label>
                                <input type="text" wire:model.defer="phone" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                @error('phone') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Address</label>
                            <textarea wire:model.defer="address" rows="4" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500"></textarea>
                            @error('address') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-slate-700">Status</label>
                                @if ($editingSchoolId)
                                    <select wire:model.defer="status" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                @else
                                    <div class="mt-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                        New schools are provisioned as active by default.
                                    </div>
                                @endif
                                @error('status') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-700">Timezone</label>
                                <input type="text" wire:model.defer="timezone" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                @error('timezone') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-700">Plan</label>
                                <select wire:model.defer="plan" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                    @foreach ($planOptions as $planOption)
                                        <option value="{{ $planOption }}">{{ ucfirst($planOption) }}</option>
                                    @endforeach
                                </select>
                                @error('plan') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">School admin access</h3>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Admin full name</label>
                            <input type="text" wire:model.defer="adminName" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('adminName') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Admin email</label>
                            <input type="email" wire:model.defer="adminEmail" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            @error('adminEmail') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-slate-700">
                                    {{ $editingSchoolId ? 'New password (optional)' : 'Password' }}
                                </label>
                                <input type="password" wire:model.defer="adminPassword" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                                @error('adminPassword') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-700">Confirm password</label>
                                <input type="password" wire:model.defer="adminPassword_confirmation" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            Use this section to update the school admin email, rotate credentials, or create the admin account if it does not exist yet.
                        </div>
                    </section>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end lg:col-span-2">
                        <button type="button" wire:click="closeSchoolModal" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                            {{ $editingSchoolId ? 'Save changes' : 'Create school' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showPaymentSettingsModal && $paymentSchool)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-8">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Payment settings</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Configure gateway access for <span class="font-semibold text-slate-900">{{ $paymentSchool->name }}</span>.
                        </p>
                    </div>

                    <button type="button" wire:click="closePaymentSettingsModal" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="savePaymentSettings" class="space-y-6 px-6 py-6">
                    <section class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Gateway name</label>
                            <select wire:model.defer="paymentGatewayName" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="paystack">Paystack</option>
                            </select>
                            @error('paymentGatewayName') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Mode</label>
                            <select wire:model.defer="paystackMode" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="test">Test</option>
                                <option value="live">Live</option>
                            </select>
                            @error('paystackMode') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    <section class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-700">Paystack public key</label>
                            <input type="text" wire:model.defer="paystackPublicKey" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('paystackPublicKey') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-700">Paystack secret key</label>
                            <input type="password" wire:model.defer="paystackSecretKey" autocomplete="new-password" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('paystackSecretKey') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror

                            @if ($hasStoredSecretKey)
                                <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                    A secret key is already stored securely for this school. Leave this field blank to keep the existing key.
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Merchant name</label>
                            <input type="text" wire:model.defer="merchantName" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('merchantName') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Merchant email</label>
                            <input type="email" wire:model.defer="merchantEmail" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('merchantEmail') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-700">Merchant phone</label>
                            <input type="text" wire:model.defer="merchantPhone" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('merchantPhone') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" wire:model.defer="paymentSettingsActive" class="mt-1 rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">Activate payment gateway</span>
                                <span class="mt-1 block text-sm text-slate-600">
                                    When active, this school will use these Paystack settings during payment initialization and verification.
                                </span>
                            </span>
                        </label>
                        @error('paymentSettingsActive') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </section>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-800">
                        Secret keys are never displayed back in full after save and are encrypted at rest before storage.
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closePaymentSettingsModal" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Save payment settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDeleteModal && $deletingSchool)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <h2 class="text-xl font-semibold text-slate-900">Delete school</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    This will permanently remove <span class="font-semibold text-slate-900">{{ $deletingSchool->name }}</span>
                    and delete all users currently linked to that school.
                </p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="closeDeleteModal" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteSchool" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                        Delete school
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
