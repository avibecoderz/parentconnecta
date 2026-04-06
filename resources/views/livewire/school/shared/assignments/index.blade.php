<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header eyebrow="{{ $eyebrow }}" title="Parent Assignments" :description="'Link parent accounts to students inside '.$school->name.' and manage those relationships without crossing school boundaries.'">
            <x-slot:actions>
                <x-ui.secondary-button :href="$dashboardRoute" wire:navigate>Back</x-ui.secondary-button>
                <x-ui.primary-button :href="$createRoute" wire:navigate>Link Parent</x-ui.primary-button>
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
                <x-school-admin.stat-card
                    :label="$metric['label']"
                    :value="$metric['value']"
                    :hint="$metric['hint']"
                />
            @endforeach
        </section>

        <section class="pc-card mt-6 overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Parent profiles</h2>
                    <p class="text-sm text-slate-500">Search a parent or linked child to review and manage relationships.</p>
                </div>

                <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Search parents or linked students..." class="sm:w-96" />
            </div>

            <div class="space-y-4 p-4 sm:p-6">
                @forelse ($parents as $parent)
                    <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ $parent->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $parent->email }}</p>
                                <p class="mt-2 text-sm text-slate-500">{{ number_format($parent->children_count) }} linked child{{ $parent->children_count === 1 ? '' : 'ren' }}</p>
                            </div>

                            <button
                                type="button"
                                wire:click="openLinkModalForParent({{ $parent->id }})"
                                class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                            >
                                Add link
                            </button>
                        </div>

                        <div class="mt-5 space-y-3">
                            @forelse ($parent->children as $child)
                                <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $child->first_name }} {{ $child->last_name }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $child->admission_number }}
                                            @if ($child->schoolClass)
                                                - {{ $child->schoolClass->name }}{{ $child->schoolClass->section ? ' / '.$child->schoolClass->section : '' }}
                                            @endif
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500 capitalize">
                                            Relationship: {{ $child->pivot->relationship_type ?: 'other' }}
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="unlinkStudentFromParent({{ $parent->id }}, {{ $child->id }})"
                                        class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                                    >
                                        Unlink
                                    </button>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-sm text-slate-500">
                                    No students are linked to this parent yet.
                                </div>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-10 text-center text-sm text-slate-500">
                        No parents or linked students matched your current search.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $parents->links() }}
            </div>
        </section>
    </div>

    @if ($showLinkModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-8">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Link parent to student</h2>
                        <p class="mt-1 text-sm text-slate-500">Only parents and students from {{ $school->name }} can be linked here.</p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeLinkModal"
                        class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="linkStudentToParent" class="space-y-6 px-6 py-6">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Parent</label>
                        <select wire:model.defer="parentUserId" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="">Select parent</option>
                            @foreach ($parentOptions as $parentOption)
                                <option value="{{ $parentOption->id }}">{{ $parentOption->name }} - {{ $parentOption->email }}</option>
                            @endforeach
                        </select>
                        @error('parentUserId') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Student</label>
                        <select wire:model.defer="studentId" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="">Select student</option>
                            @foreach ($studentOptions as $studentOption)
                                <option value="{{ $studentOption->id }}">
                                    {{ $studentOption->first_name }} {{ $studentOption->last_name }} - {{ $studentOption->admission_number }}
                                    @if ($studentOption->schoolClass)
                                        ({{ $studentOption->schoolClass->name }}{{ $studentOption->schoolClass->section ? ' / '.$studentOption->schoolClass->section : '' }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('studentId') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Relationship type</label>
                        <select wire:model.defer="relationshipType" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="other">Other</option>
                            <option value="father">Father</option>
                            <option value="mother">Mother</option>
                            <option value="guardian">Guardian</option>
                        </select>
                        @error('relationshipType') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        Duplicate links are blocked. If a parent is already linked to a student, use the existing relationship entry instead of creating another one.
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            wire:click="closeLinkModal"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Save link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
