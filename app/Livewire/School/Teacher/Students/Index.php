<?php

namespace App\Livewire\School\Teacher\Students;

use App\Livewire\School\Teacher\TeacherPage;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\Schools\SchoolPlanLimitService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Title('Teacher Students')]
class Index extends TeacherPage
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = 'all';

    #[Url(as: 'class', history: true)]
    public string $classFilter = 'all';

    #[Url(as: 'create', history: true, except: '0')]
    public string $create = '0';

    public bool $showStudentModal = false;

    public ?int $editingStudentId = null;

    public string $schoolClassId = '';

    public string $admissionNumber = '';

    public string $firstName = '';

    public string $lastName = '';

    public string $middleName = '';

    public string $dateOfBirth = '';

    public string $gender = '';

    public string $status = 'active';

    public string $admittedAt = '';

    public function mount(string $slug): void
    {
        parent::mount($slug);

        if ($this->shouldOpenCreateModal()) {
            $this->createStudent();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    public function createStudent(): void
    {
        $school = $this->currentSchool();
        $this->authorize('create', [Student::class, $school]);
        $assignedClassIds = $this->assignedClassIds();

        if ($assignedClassIds === []) {
            $this->create = '0';
            session()->flash('error', 'You need at least one assigned class before you can register students.');

            return;
        }

        $this->create = '1';
        $this->resetValidation();
        $this->resetForm();

        $this->schoolClassId = (string) $assignedClassIds[0];

        $this->showStudentModal = true;
    }

    public function editStudent(int $studentId): void
    {
        $student = $this->studentLookupQuery()
            ->findOrFail($studentId, [
                'id',
                'school_class_id',
                'admission_number',
                'first_name',
                'last_name',
                'middle_name',
                'date_of_birth',
                'gender',
                'status',
                'admitted_at',
            ]);
        $this->authorize('update', $student);

        $this->resetValidation();
        $this->editingStudentId = $student->id;
        $this->schoolClassId = (string) $student->school_class_id;
        $this->admissionNumber = $student->admission_number;
        $this->firstName = $student->first_name;
        $this->lastName = $student->last_name;
        $this->middleName = $student->middle_name ?? '';
        $this->dateOfBirth = $student->date_of_birth?->format('Y-m-d') ?? '';
        $this->gender = $student->gender ?? '';
        $this->status = $student->status;
        $this->admittedAt = $student->admitted_at?->format('Y-m-d') ?? '';
        $this->showStudentModal = true;
    }

    public function saveStudent(): void
    {
        $school = $this->currentSchool();
        $this->normalizeFormFields();
        $validated = $this->validate($this->rules($school));

        if ($this->editingStudentId !== null) {
            $student = $this->studentLookupQuery()->findOrFail($this->editingStudentId);
            $this->authorize('update', $student);

            $student->update($this->studentPayload($school, $validated));
            session()->flash('status', 'Student updated successfully.');
        } else {
            $this->authorize('create', [Student::class, $school]);
            app(SchoolPlanLimitService::class)->ensureCanAddStudent($school, 'firstName');

            DB::transaction(function () use ($school, $validated): void {
                app(SchoolPlanLimitService::class)->ensureCanAddStudent($school, 'firstName');
                Student::query()->create($this->studentPayload($school, $validated));
            });
            session()->flash('status', 'Student registered successfully.');
        }

        $this->closeStudentModal();
    }

    public function closeStudentModal(): void
    {
        $this->showStudentModal = false;
        $this->create = '0';
        $this->resetValidation();
        $this->resetForm();
    }

    public function render(): View
    {
        $school = $this->currentSchool();
        $this->authorize('viewAny', [Student::class, $school]);
        $classOptions = $this->classOptionsQuery()->get(['id', 'name', 'section', 'status']);
        $selectedClassId = $this->selectedClassId($classOptions);

        return view('livewire.school.teacher.students.index', [
            'school' => $school,
            'students' => $this->studentsQuery()->paginate(10),
            'classOptions' => $classOptions,
            'canCreateStudents' => $classOptions->isNotEmpty(),
            'metrics' => [
                [
                    'label' => 'Students in scope',
                    'value' => number_format((clone $this->assignedStudentsQuery())->count()),
                    'hint' => 'All students inside your assigned classes',
                ],
                [
                    'label' => 'Active students',
                    'value' => number_format(
                        (clone $this->assignedStudentsQuery())
                            ->where('status', 'active')
                            ->count(),
                    ),
                    'hint' => 'Students currently marked as active',
                ],
                [
                    'label' => 'With parent links',
                    'value' => number_format(
                        (clone $this->assignedStudentsQuery())
                            ->whereHas('parents', fn (Builder $query) => $query->where('users.school_id', $school->id))
                            ->count(),
                    ),
                    'hint' => 'Students in your classes already linked to a parent',
                ],
                [
                    'label' => 'Current class filter',
                    'value' => $selectedClassId === null
                        ? 'All assigned classes'
                        : $this->classDisplayName($classOptions->firstWhere('id', $selectedClassId)),
                    'hint' => 'Filter the directory to a single assigned class',
                ],
            ],
        ])->layout('layouts.school.teacher');
    }

    protected function rules(School $school): array
    {
        $assignedClassIds = $this->assignedClassIds();

        return [
            'schoolClassId' => [
                'required',
                'integer',
                Rule::exists('school_classes', 'id')->where(
                    fn ($query) => $query
                        ->where('school_id', $school->id)
                        ->whereIn('id', $assignedClassIds === [] ? [0] : $assignedClassIds),
                ),
            ],
            'admissionNumber' => [
                'required',
                'string',
                'max:255',
                Rule::unique('students', 'admission_number')
                    ->where(fn ($query) => $query->where('school_id', $school->id))
                    ->ignore($this->editingStudentId),
            ],
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'middleName' => ['nullable', 'string', 'max:255'],
            'dateOfBirth' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'status' => ['required', Rule::in(['active', 'graduated', 'withdrawn', 'inactive'])],
            'admittedAt' => ['nullable', 'date', 'after_or_equal:dateOfBirth'],
        ];
    }

    protected function resetForm(): void
    {
        $this->editingStudentId = null;
        $this->schoolClassId = '';
        $this->admissionNumber = '';
        $this->firstName = '';
        $this->lastName = '';
        $this->middleName = '';
        $this->dateOfBirth = '';
        $this->gender = '';
        $this->status = 'active';
        $this->admittedAt = '';
    }

    protected function normalizeFormFields(): void
    {
        $this->admissionNumber = trim($this->admissionNumber);
        $this->firstName = trim($this->firstName);
        $this->lastName = trim($this->lastName);
        $this->middleName = trim($this->middleName);
    }

    protected function studentsQuery(): Builder
    {
        $selectedClassId = $this->selectedClassId();

        return $this->assignedStudentsQuery()
            ->with('schoolClass:id,name,section')
            ->withCount([
                'parents as parents_count' => fn ($query) => $query->where('users.school_id', $this->currentSchool()->id),
                'results',
                'payments',
            ])
            ->when(
                $this->statusFilter !== 'all',
                fn (Builder $query) => $query->where('status', $this->statusFilter),
            )
            ->when(
                $selectedClassId !== null,
                fn (Builder $query) => $query->where('school_class_id', $selectedClassId),
            )
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('admission_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    protected function studentLookupQuery(): Builder
    {
        return $this->assignedStudentsQuery();
    }

    protected function classOptionsQuery(): Builder
    {
        $assignedClassIds = $this->assignedClassIds();

        return SchoolClass::query()
            ->where('school_id', $this->currentSchool()->id)
            ->whereIn('id', $assignedClassIds === [] ? [0] : $assignedClassIds)
            ->orderBy('name')
            ->orderBy('section');
    }

    protected function selectedClassId(?Collection $classOptions = null): ?int
    {
        if ($this->classFilter === 'all' || ! ctype_digit($this->classFilter)) {
            return null;
        }

        $selectedClassId = (int) $this->classFilter;
        $classOptions ??= $this->classOptionsQuery()->get(['id']);

        return $classOptions->contains('id', $selectedClassId) ? $selectedClassId : null;
    }

    protected function classDisplayName(?SchoolClass $schoolClass): string
    {
        if (! $schoolClass instanceof SchoolClass) {
            return 'Selected class';
        }

        return $schoolClass->name.($schoolClass->section ? ' / '.$schoolClass->section : '');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function studentPayload(School $school, array $validated): array
    {
        return [
            'school_id' => $school->id,
            'school_class_id' => (int) $validated['schoolClassId'],
            'admission_number' => $validated['admissionNumber'],
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'middle_name' => $validated['middleName'] ?: null,
            'date_of_birth' => $validated['dateOfBirth'] ?: null,
            'gender' => $validated['gender'] ?: null,
            'status' => $validated['status'],
            'admitted_at' => $validated['admittedAt'] ?: null,
        ];
    }

    protected function shouldOpenCreateModal(): bool
    {
        return $this->create === '1';
    }
}
