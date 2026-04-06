<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header :eyebrow="$eyebrow" :title="$title" :description="$description">
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.teacher.dashboard', ['slug' => $school->slug])" wire:navigate>
                    Back
                </x-ui.secondary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($cards as $card)
                <x-school-admin.stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :hint="$card['hint']"
                />
            @endforeach
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
            <article class="pc-card p-6">
                <p class="pc-eyebrow text-slate-400">Teacher scope</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $school->name }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    This page is already isolated to <span class="font-medium text-slate-900">/school/{{ $school->slug }}</span> and should only surface data from the classes assigned to the logged-in teacher.
                </p>

                <div class="mt-6 rounded-[1.35rem] border border-dashed border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-medium text-slate-900">Page status</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        The teacher shell is ready. The next pass should turn this page into a real workflow with tables, forms, and scoped actions.
                    </p>
                </div>
            </article>

            <article class="pc-card p-6">
                <h2 class="text-lg font-semibold text-slate-900">Planned next work</h2>
                <p class="mt-1 text-sm text-slate-500">Recommended implementation sequence for this page.</p>

                <ol class="mt-5 space-y-3">
                    @foreach ($nextSteps as $step)
                        <li class="flex gap-3 rounded-[1.35rem] bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                            <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[var(--pc-primary)] text-xs font-semibold text-white">
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
