<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header
            eyebrow="Parent"
            title="My Pupils"
            :description="'Every child shown here is linked directly to your parent account in '.$school->name.'.'"
        >
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.parent.dashboard', ['slug' => $school->slug])" wire:navigate>
                    Back
                </x-ui.secondary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        <section class="pc-card overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">Filter pupils</h2>
                <p class="text-sm text-slate-500">Search by child name or admission number, or narrow the list by admission date.</p>
            </div>

            <form wire:submit="searchChildren" class="grid gap-4 px-6 py-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label for="child-search" class="text-sm font-medium text-slate-700">Search</label>
                    <input id="child-search" type="text" wire:model.defer="search" class="pc-input mt-2" placeholder="Child name or admission number">
                </div>

                <div>
                    <label for="child-from" class="text-sm font-medium text-slate-700">From date</label>
                    <input id="child-from" type="date" wire:model="dateFrom" class="pc-input mt-2">
                </div>

                <div>
                    <label for="child-to" class="text-sm font-medium text-slate-700">To date</label>
                    <input id="child-to" type="date" wire:model="dateTo" class="pc-input mt-2">
                </div>

                <div class="md:col-span-2 xl:col-span-4 flex flex-wrap gap-3">
                    <x-ui.primary-button type="submit">
                        Search
                    </x-ui.primary-button>
                    <x-ui.secondary-button type="button" wire:click="clearFilters">
                        Clear filters
                    </x-ui.secondary-button>
                </div>
            </form>
        </section>

        <section class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($children as $child)
                <article class="pc-card p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">{{ $child->first_name }} {{ $child->last_name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $child->admission_number }}</p>
                        </div>

                        <x-ui.status-badge :status="$child->status" />
                    </div>

                    <dl class="mt-5 space-y-3 text-sm text-slate-600">
                        <div class="flex items-center justify-between gap-4">
                            <dt>Relationship</dt>
                            <dd class="font-medium text-slate-900">{{ ucfirst((string) ($child->pivot?->relationship_type ?? 'guardian')) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>Class</dt>
                            <dd class="font-medium text-slate-900">
                                @if ($child->schoolClass)
                                    {{ $child->schoolClass->name }}{{ $child->schoolClass->section ? ' / '.$child->schoolClass->section : '' }}
                                @else
                                    Not assigned
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>Latest grade</dt>
                            <dd class="font-medium text-slate-900">{{ optional($child->results->first())->grade ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>Outstanding</dt>
                            <dd class="font-medium text-slate-900">NGN {{ number_format((float) $child->payments->sum('balance'), 2) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex gap-3">
                        <a
                            href="{{ route('school.parent.pupils.show', ['slug' => $school->slug, 'student' => $child->id]) }}"
                            wire:navigate
                            class="pc-btn-primary flex-1"
                        >
                            View details
                        </a>

                        <a
                            href="{{ route('school.parent.payments.index', ['slug' => $school->slug]) }}"
                            wire:navigate
                            class="pc-btn-secondary"
                        >
                            Payments
                        </a>
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-3">
                    <x-ui.empty-state
                        title="No linked pupils found"
                        description="Linked children will appear here once your account is connected to a pupil."
                        class="pc-card p-10"
                    />
                </div>
            @endforelse
        </section>

        <div class="mt-6">
            {{ $children->links() }}
        </div>
    </div>
</div>
