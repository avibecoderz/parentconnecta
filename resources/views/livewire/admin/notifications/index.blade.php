<div class="min-h-screen bg-slate-50">
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-sky-600">Super Admin</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-900">Notifications</h1>
            <p class="mt-1 text-sm text-slate-600">Send announcements and urgent system alerts to all schools or to one school at a time.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @unless ($notificationsTableAvailable)
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Notifications storage is not ready yet. Run the notifications migration, then this page will start saving and sending messages normally.
            </div>
        @endunless

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Active schools</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($metrics['schools']) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Reachable users</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($metrics['users']) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Announcements sent</p>
                <p class="mt-3 text-3xl font-semibold text-sky-600">{{ number_format($metrics['announcements']) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">System alerts sent</p>
                <p class="mt-3 text-3xl font-semibold text-rose-600">{{ number_format($metrics['alerts']) }}</p>
            </article>
        </section>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(18rem,0.9fr)]">
            <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <form wire:submit="sendNotification" class="space-y-6 p-6">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <label for="audience" class="text-sm font-semibold text-slate-900">Send message to</label>
                            <select
                                id="audience"
                                wire:model.live="audience"
                                class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500"
                            >
                                <option value="all_schools">All schools</option>
                                <option value="specific_school">Specific school</option>
                            </select>
                            @error('audience')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="notificationType" class="text-sm font-semibold text-slate-900">Message type</label>
                            <select
                                id="notificationType"
                                wire:model.live="notificationType"
                                class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500"
                            >
                                <option value="announcement">Announcements</option>
                                <option value="system_alert">System alerts</option>
                            </select>
                            @error('notificationType')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if ($audience === 'specific_school')
                        <div>
                            <label for="schoolId" class="text-sm font-semibold text-slate-900">Choose school</label>
                            <select
                                id="schoolId"
                                wire:model="schoolId"
                                class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500"
                            >
                                <option value="">Select a school</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }} ({{ $school->status }})</option>
                                @endforeach
                            </select>
                            @error('schoolId')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label for="subject" class="text-sm font-semibold text-slate-900">Subject</label>
                        <input
                            id="subject"
                            type="text"
                            wire:model="subject"
                            placeholder="Example: Planned maintenance this evening"
                            class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:ring-sky-500"
                        >
                        @error('subject')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="message" class="text-sm font-semibold text-slate-900">Message</label>
                        <textarea
                            id="message"
                            wire:model="message"
                            rows="7"
                            placeholder="Write the full message you want schools to receive."
                            class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:ring-sky-500"
                        ></textarea>
                        @error('message')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-500">Messages are saved as in-app database notifications for active users in active schools.</p>
                        <button
                            type="submit"
                            @disabled(! $notificationsTableAvailable)
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                        >
                            Send notification
                        </button>
                    </div>
                </form>
            </section>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">How it works</h2>
                    <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        <li>Choose <span class="font-semibold text-slate-900">All schools</span> to notify every active user linked to an active school.</li>
                        <li>Choose <span class="font-semibold text-slate-900">Specific school</span> to target one school only.</li>
                        <li>Use <span class="font-semibold text-slate-900">Announcements</span> for general updates and <span class="font-semibold text-slate-900">System alerts</span> for urgent platform issues.</li>
                    </ul>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-slate-900 p-6 text-white shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-200">Delivery note</p>
                    <h2 class="mt-3 text-xl font-semibold">This version sends in-app notifications</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">Each recipient gets a saved notification record in the database. That gives us a reliable backend foundation for later adding email, SMS, or push delivery without changing this screen.</p>
                </section>
            </aside>
        </div>
    </div>
</div>
