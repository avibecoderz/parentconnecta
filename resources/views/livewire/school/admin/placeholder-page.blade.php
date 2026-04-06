<div class="min-h-screen bg-slate-100">
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-sky-600">{{ $eyebrow }}</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-900">{{ $title }}</h1>
                <p class="mt-1 max-w-3xl text-sm text-slate-600">{{ $description }}</p>
            </div>

            <a
                href="{{ route('school.admin.dashboard', ['slug' => $school->slug]) }}"
                wire:navigate
                class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Back to dashboard
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($cards as $card)
                <x-school-admin.stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :hint="$card['hint']"
                />
            @endforeach
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium uppercase tracking-[0.16em] text-sky-600">Tenant context</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $school->name }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    This module is already isolated to <span class="font-medium text-slate-900">/school/{{ $school->slug }}</span>.
                    All upcoming CRUD actions here should continue reading and writing records with this school as the tenant boundary.
                </p>

                <div class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-medium text-slate-900">Page status</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        The structure is ready. The next step is building the actual CRUD flows, validation, and modal or table interactions for this module.
                    </p>
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Planned next work</h2>
                <p class="mt-1 text-sm text-slate-500">Recommended implementation sequence for this module.</p>

                <ol class="mt-5 space-y-3">
                    @foreach ($nextSteps as $step)
                        <li class="flex gap-3 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                            <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">
                                {{ $loop->iteration }}
                            </span>
                            <span>{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </article>
        </section>
    </div>
</div>
