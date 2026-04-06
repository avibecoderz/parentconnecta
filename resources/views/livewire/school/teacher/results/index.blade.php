<div class="pb-10">
    <x-slot name="header">
        <x-ui.page-header eyebrow="Teacher" title="Term Results" description="Enter and manage results for students in your assigned classes only.">
            <x-slot:actions>
                <x-ui.secondary-button :href="route('school.teacher.dashboard', ['slug' => $school->slug])" wire:navigate>
                    Back
                </x-ui.secondary-button>
                <x-ui.primary-button type="button" wire:click="createResult">
                    New Result
                </x-ui.primary-button>
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot>

    <div class="pc-shell py-8">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <x-school-admin.stat-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :hint="$stat['hint']"
                />
            @endforeach
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[0.95fr,1.05fr]">
            <article class="pc-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="pc-eyebrow text-slate-400">Entry form</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">
                            {{ $editingResultId ? 'Edit result' : 'Enter term result' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">Select one of your classes, choose a student, and save the subject result.</p>
                    </div>
                    @if ($editingResultId)
                        <button
                            type="button"
                            wire:click="createResult"
                            class="pc-btn-secondary px-3 py-2 text-sm"
                        >
                            Cancel edit
                        </button>
                    @endif
                </div>

                <form wire:submit="saveResult" class="mt-6 space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Assigned class</label>
                            <select wire:model.live="selectedClassId" class="mt-2 w-full rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]">
                                <option value="">Select class</option>
                                @foreach ($assignedClasses as $assignedClass)
                                    <option value="{{ $assignedClass->id }}">
                                        {{ $assignedClass->name }}{{ $assignedClass->section ? ' / '.$assignedClass->section : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedClassId') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Student</label>
                            <select wire:model.defer="studentId" class="mt-2 w-full rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]">
                                <option value="">Select student</option>
                                @foreach ($studentOptions as $studentOption)
                                    <option value="{{ $studentOption->id }}">
                                        {{ $studentOption->first_name }} {{ $studentOption->last_name }} - {{ $studentOption->admission_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('studentId') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Subject</label>
                            <input type="text" wire:model.defer="subjectName" class="mt-2 w-full rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]" placeholder="Mathematics">
                            @error('subjectName') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Session</label>
                            <input type="text" wire:model.defer="academicYear" class="mt-2 w-full rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]" placeholder="2026/2027">
                            @error('academicYear') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Term</label>
                            <select wire:model.defer="term" class="mt-2 w-full rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]">
                                <option value="first">First term</option>
                                <option value="second">Second term</option>
                                <option value="third">Third term</option>
                            </select>
                            @error('term') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">CA score</label>
                            <input type="number" min="0" max="40" step="0.01" wire:model.live.debounce.300ms="caScore" class="mt-2 w-full rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]" placeholder="0.00">
                            @error('caScore') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Exam score</label>
                            <input type="number" min="0" max="60" step="0.01" wire:model.live.debounce.300ms="examScore" class="mt-2 w-full rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]" placeholder="0.00">
                            @error('examScore') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">{{ number_format((float) $scorePreview['total_score'], 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Grade</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $scorePreview['grade'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Default remark</p>
                                <p class="mt-2 text-sm font-medium text-slate-700">{{ $scorePreview['remark'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Remark</label>
                        <textarea wire:model.defer="remark" rows="3" class="mt-2 w-full rounded-[1rem] border-slate-200 bg-slate-100 px-4 py-3 text-sm focus:border-[var(--pc-primary)] focus:ring-[var(--pc-primary)]" placeholder="Optional custom remark"></textarea>
                        @error('remark') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        The first MVP version publishes results immediately after save. Parents can later consume only these saved records through their linked children.
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            wire:click="createResult"
                            class="pc-btn-secondary"
                        >
                            Reset
                        </button>
                        <button
                            type="submit"
                            class="pc-btn-primary"
                        >
                            {{ $editingResultId ? 'Update result' : 'Save result' }}
                        </button>
                    </div>
                </form>
            </article>

            <article class="pc-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="pc-eyebrow text-slate-400">Recent results</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">Saved entries</h2>
                        <p class="mt-1 text-sm text-slate-500">Only results from your assigned classes appear here.</p>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    @forelse ($recentResults as $result)
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $result->student->first_name }} {{ $result->student->last_name }} - {{ $result->subject_name }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $result->student->admission_number }}
                                        @if ($result->student->schoolClass)
                                            - {{ $result->student->schoolClass->name }}{{ $result->student->schoolClass->section ? ' / '.$result->student->schoolClass->section : '' }}
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ ucfirst($result->term) }} term / {{ $result->academic_year }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">
                                        {{ $result->grade }}
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="editResult({{ $result->id }})"
                                        class="pc-btn-secondary px-3 py-2 text-sm"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deleteResult({{ $result->id }})"
                                        wire:confirm="Delete this result entry?"
                                        class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-4">
                                <div class="rounded-[1rem] bg-white p-3 text-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CA</p>
                                    <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) $result->ca_score, 2) }}</p>
                                </div>
                                <div class="rounded-[1rem] bg-white p-3 text-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Exam</p>
                                    <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) $result->exam_score, 2) }}</p>
                                </div>
                                <div class="rounded-[1rem] bg-white p-3 text-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total</p>
                                    <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) $result->total_score, 2) }}</p>
                                </div>
                                <div class="rounded-[1rem] bg-white p-3 text-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Remark</p>
                                    <p class="mt-1 font-medium text-slate-700">{{ $result->remark ?: 'No remark' }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state
                            title="No results entered yet"
                            description="Results for your assigned classes will appear here after you save them."
                        />
                    @endforelse
                </div>

                <div class="mt-6 border-t border-slate-100 pt-4">
                    {{ $recentResults->links() }}
                </div>
            </article>
        </section>
    </div>
</div>
