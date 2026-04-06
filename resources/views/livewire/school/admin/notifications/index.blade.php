<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header
            eyebrow="School Admin"
            title="Notifications"
            :description="'Review announcements and system alerts sent to '.$school->name.'.'"
        >
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.admin.dashboard', ['slug' => $school->slug, 'notifications' => 1])" wire:navigate>
                    Open Popup
                </x-ui.secondary-button>
                <x-ui.secondary-button :href="route('school.admin.dashboard', ['slug' => $school->slug])" wire:navigate>
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

        @unless ($notificationsTableAvailable)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Notifications are not available yet because the notifications table has not been migrated.
            </div>
        @else
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <x-school-admin.stat-card label="Total" :value="number_format($metrics['total'])" hint="All platform notifications for this account" />
                <x-school-admin.stat-card label="Unread" :value="number_format($metrics['unread'])" hint="Notifications that still need review" />
                <x-school-admin.stat-card label="Announcements" :value="number_format($metrics['announcements'])" hint="General platform updates" />
                <x-school-admin.stat-card label="System Alerts" :value="number_format($metrics['alerts'])" hint="Urgent platform notices" />
            </section>

            <section class="pc-card mt-6 overflow-hidden">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Notification history</h2>
                        <p class="text-sm text-slate-500">Filter recent activity and mark unread items as handled.</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <select
                            wire:model.live="typeFilter"
                            class="rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-900 focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]"
                        >
                            <option value="all">All types</option>
                            <option value="announcement">Announcements</option>
                            <option value="system_alert">System alerts</option>
                        </select>

                        <select
                            wire:model.live="statusFilter"
                            class="rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-900 focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]"
                        >
                            <option value="all">All statuses</option>
                            <option value="unread">Unread</option>
                            <option value="read">Read</option>
                        </select>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($notifications as $notification)
                        @php($isAlert = data_get($notification->data, 'notification_type') === 'system_alert')
                        <article class="px-6 py-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.12em]',
                                            'bg-rose-100 text-rose-700' => $isAlert,
                                            'bg-sky-100 text-sky-700' => ! $isAlert,
                                        ])>
                                            {{ $isAlert ? 'System Alert' : 'Announcement' }}
                                        </span>

                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.12em]',
                                            'bg-emerald-100 text-emerald-700' => blank($notification->read_at),
                                            'bg-slate-100 text-slate-600' => filled($notification->read_at),
                                        ])>
                                            {{ blank($notification->read_at) ? 'Unread' : 'Read' }}
                                        </span>
                                    </div>

                                    <h3 class="mt-3 text-base font-semibold text-slate-900">
                                        {{ data_get($notification->data, 'subject', 'Platform update') }}
                                    </h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        {{ data_get($notification->data, 'message') }}
                                    </p>
                                    <p class="mt-3 text-xs text-slate-500">
                                        {{ data_get($notification->data, 'sender_name', 'Super Admin') }} • {{ $notification->created_at?->format('M j, Y g:i A') }}
                                    </p>
                                </div>

                                <div class="shrink-0">
                                    @if (blank($notification->read_at))
                                        <form method="POST" action="{{ route('school.admin.notifications.read', ['slug' => $school->slug, 'notification' => $notification->id]) }}">
                                            @csrf
                                            <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                                Mark as read
                                            </button>
                                        </form>
                                    @else
                                        <p class="text-sm text-slate-500">
                                            Read {{ $notification->read_at?->format('M j, Y g:i A') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="px-6 py-10">
                            <x-ui.empty-state
                                title="No notifications found"
                                description="Try changing the filters or check back when the super admin sends a new update."
                            />
                        </div>
                    @endforelse
                </div>

                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $notifications?->links() }}
                </div>
            </section>
        @endunless
    </div>
</div>
