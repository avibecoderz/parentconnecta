<?php

namespace App\Livewire\School\Teacher\Results;

use App\Livewire\School\Teacher\TeacherPage;
use App\Models\Result;
use App\Services\Results\GradeCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('Teacher Results')]
class Index extends TeacherPage
{
    use WithPagination;

    public ?int $editingResultId = null;

    public string $selectedClassId = '';

    public string $studentId = '';

    public string $subjectName = '';

    public string $academicYear = '';

    public string $term = 'first';

    public string $caScore = '';

    public string $examScore = '';

    public string $remark = '';

    public function mount(string $slug): void
    {
        parent::mount($slug);
        $this->academicYear = $this->currentSchool()->currentAcademicYear();
        $this->term = $this->currentSchool()->currentTerm();
    }

    public function updatingSelectedClassId(): void
    {
        $this->studentId = '';
        $this->resetValidation();
        $this->resetPage();
    }

    public function createResult(): void
    {
        $this->authorize('create', [Result::class, $this->currentSchool()]);
        $this->resetValidation();
        $this->resetForm();
    }

    public function editResult(int $resultId): void
    {
        $result = $this->resultLookupQuery()->findOrFail($resultId);
        $this->authorize('update', $result);

        $this->resetValidation();
        $this->editingResultId = $result->id;
        $this->selectedClassId = (string) $result->school_class_id;
        $this->studentId = (string) $result->student_id;
        $this->subjectName = $result->subject_name;
        $this->academicYear = $result->academic_year;
        $this->term = $result->term;
        $this->caScore = number_format((float) $result->ca_score, 2, '.', '');
        $this->examScore = number_format((float) $result->exam_score, 2, '.', '');
        $this->remark = (string) ($result->remark ?? '');
    }

    public function saveResult(GradeCalculator $gradeCalculator): void
    {
        $school = $this->currentSchool();
        $this->normalizeFormFields();
        $validated = $this->validate($this->rules());
        $selectedClass = $this->assignedClassesQuery()
            ->whereKey((int) $validated['selectedClassId'])
            ->firstOrFail();

        Gate::authorize('manage-school-class', $selectedClass);

        if ($this->editingResultId !== null) {
            $this->authorize('update', $this->resultLookupQuery()->findOrFail($this->editingResultId));
        } else {
            $this->authorize('create', [Result::class, $school]);
        }

        $student = $this->studentOptionsQuery()
            ->whereKey((int) $validated['studentId'])
            ->where('school_class_id', $selectedClass->id)
            ->firstOrFail();

        $calculated = $gradeCalculator->fromScores(
            (float) $validated['caScore'],
            (float) $validated['examScore'],
        );

        DB::transaction(function () use ($school, $student, $validated, $calculated): void {
            $existingResult = $this->editingResultId !== null
                ? $this->resultLookupQuery()->findOrFail($this->editingResultId)
                : null;

            $payload = [
                'school_id' => $school->id,
                'student_id' => $student->id,
                'school_class_id' => $student->school_class_id,
                'teacher_user_id' => $this->teacher()->id,
                'subject_name' => $validated['subjectName'],
                'academic_year' => $validated['academicYear'],
                'term' => $validated['term'],
                'ca_score' => $validated['caScore'],
                'exam_score' => $validated['examScore'],
                'total_score' => $calculated['total_score'],
                'grade' => $calculated['grade'],
                'remark' => $validated['remark'] !== '' ? $validated['remark'] : $calculated['remark'],
                'published_at' => $existingResult?->published_at ?? now(),
            ];

            if ($existingResult !== null) {
                $existingResult->update($payload);

                session()->flash('status', 'Result updated successfully.');

                return;
            }

            Result::query()->create($payload);

            session()->flash('status', 'Result saved successfully.');
        });

        $this->resetForm();
    }

    public function deleteResult(int $resultId): void
    {
        $result = $this->resultLookupQuery()->findOrFail($resultId);
        $this->authorize('delete', $result);

        $studentName = $result->student->first_name.' '.$result->student->last_name;
        $subjectName = $result->subject_name;

        $result->delete();

        if ($this->editingResultId === $resultId) {
            $this->resetForm();
        }

        session()->flash('status', "Deleted {$subjectName} result for {$studentName}.");
    }

    public function render(GradeCalculator $gradeCalculator): View
    {
        $school = $this->currentSchool();
        $this->authorize('viewAny', [Result::class, $school]);
        $selectedClassId = $this->resolvedSelectedClassId();
        $preview = $gradeCalculator->fromScores(
            (float) ($this->caScore !== '' ? $this->caScore : 0),
            (float) ($this->examScore !== '' ? $this->examScore : 0),
        );

        return view('livewire.school.teacher.results.index', [
            'school' => $school,
            'assignedClasses' => $this->assignedClassesQuery()
                ->orderBy('name')
                ->orderBy('section')
                ->get(['school_classes.id', 'school_classes.name', 'school_classes.section']),
            'studentOptions' => $this->studentOptionsQuery()
                ->when(
                    $selectedClassId !== null,
                    fn (Builder $query) => $query->where('school_class_id', $selectedClassId),
                    fn (Builder $query) => $query->whereRaw('1 = 0'),
                )
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(['id', 'school_class_id', 'first_name', 'last_name', 'admission_number']),
            'recentResults' => $this->resultsTableQuery()->paginate(10),
            'scorePreview' => $preview,
            'stats' => [
                [
                    'label' => 'Assigned classes',
                    'value' => number_format(count($this->assignedClassIds())),
                    'hint' => 'Classes you can enter results for',
                ],
                [
                    'label' => 'Students in scope',
                    'value' => number_format((clone $this->assignedStudentsQuery())->count()),
                    'hint' => 'Students available for result entry',
                ],
                [
                    'label' => 'Results entered',
                    'value' => number_format((clone $this->assignedResultsQuery())->count()),
                    'hint' => 'Published result records in your assigned classes',
                ],
                [
                    'label' => 'Current term',
                    'value' => ucfirst($this->term),
                    'hint' => $this->academicYear !== '' ? $this->academicYear : 'Select session and term below',
                ],
            ],
        ])->layout('layouts.school.teacher');
    }

    protected function rules(): array
    {
        return [
            'selectedClassId' => [
                'required',
                'integer',
                Rule::in($this->assignedClassIds()),
            ],
            'studentId' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $studentExists = $this->studentOptionsQuery()
                        ->whereKey((int) $value)
                        ->where('school_class_id', (int) $this->selectedClassId)
                        ->exists();

                    if (! $studentExists) {
                        $fail('The selected student is invalid for the chosen class.');
                    }
                },
            ],
            'subjectName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('results', 'subject_name')
                    ->ignore($this->editingResultId)
                    ->where(function ($query) {
                        return $query
                            ->where('school_id', $this->currentSchool()->id)
                            ->where('student_id', (int) $this->studentId)
                            ->where('academic_year', $this->academicYear)
                            ->where('term', $this->term);
                    }),
            ],
            'academicYear' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'term' => ['required', Rule::in(['first', 'second', 'third'])],
            'caScore' => ['required', 'numeric', 'min:0', 'max:40'],
            'examScore' => ['required', 'numeric', 'min:0', 'max:60'],
            'remark' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function resetForm(): void
    {
        $this->editingResultId = null;
        $this->selectedClassId = '';
        $this->studentId = '';
        $this->subjectName = '';
        $this->academicYear = $this->currentSchool()->currentAcademicYear();
        $this->term = $this->currentSchool()->currentTerm();
        $this->caScore = '';
        $this->examScore = '';
        $this->remark = '';
    }

    protected function normalizeFormFields(): void
    {
        $this->subjectName = trim($this->subjectName);
        $this->academicYear = trim($this->academicYear);
        $this->remark = trim($this->remark);
    }

    protected function studentOptionsQuery(): Builder
    {
        return $this->assignedStudentsQuery();
    }

    protected function resultLookupQuery(): Builder
    {
        return $this->assignedResultsQuery()
            ->with([
                'student:id,school_class_id,first_name,last_name,admission_number',
                'student.schoolClass:id,name,section',
            ]);
    }

    protected function resultsTableQuery(): Builder
    {
        return $this->resultLookupQuery()
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    protected function defaultAcademicYear(): string
    {
        return $this->currentSchool()->currentAcademicYear();
    }

    protected function resolvedSelectedClassId(): ?int
    {
        $selectedClassId = (int) $this->selectedClassId;

        if ($selectedClassId === 0) {
            return null;
        }

        return in_array($selectedClassId, $this->assignedClassIds(), true) ? $selectedClassId : null;
    }
}
