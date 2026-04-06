<?php

namespace App\Livewire\School\Teacher;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\Schools\SchoolPlanLimitService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Title('Teacher Dashboard')]
class Dashboard extends TeacherPage
{
    #[Url(as: 'exam-mode', history: true)]
    public string $examModePanel = '0';

    public bool $showStudentModal = false;

    public bool $showExamModeModal = false;

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

        if ($this->examModePanel === '1') {
            $this->openExamModeModal();
        }
    }

    public function openExamModeModal(): void
    {
        $this->examModePanel = '1';
        $this->showExamModeModal = true;
    }

    public function closeExamModeModal(): void
    {
        $this->examModePanel = '0';
        $this->showExamModeModal = false;
    }

    public function openStudentModal(): void
    {
        $this->authorize('create', [Student::class, $this->currentSchool()]);
        $this->resetValidation();
        $this->resetStudentForm();

        $assignedClassIds = $this->assignedClassIds();
        if (! empty($assignedClassIds)) {
            $this->schoolClassId = (string) $assignedClassIds[0];
        }

        $this->showStudentModal = true;
    }

    public function closeStudentModal(): void
    {
        $this->showStudentModal = false;
        $this->resetStudentForm();
    }

    public function saveStudent(): void
    {
        $school = $this->currentSchool();
        $this->authorize('create', [Student::class, $school]);
        $this->normalizeStudentForm();

        $validated = $this->validate($this->rules($school->id));
        app(SchoolPlanLimitService::class)->ensureCanAddStudent($school, 'firstName');

        DB::transaction(function () use ($school, $validated): void {
            app(SchoolPlanLimitService::class)->ensureCanAddStudent($school, 'firstName');

            Student::query()->create([
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
            ]);
        });

        session()->flash('status', 'Student registered successfully.');

        $this->closeStudentModal();
    }

    public function render(): View
    {
        $school = $this->currentSchool();
        $assignedClassIds = $this->assignedClassIds();

        $assignedClasses = $this->assignedClassesQuery()
            ->withCount([
                'students as active_students_count' => fn (Builder $query) => $query
                    ->where('students.school_id', $school->id)
                    ->where('students.status', 'active'),
            ])
            ->orderBy('name')
            ->orderBy('section')
            ->get();

        $recentStudents = $this->assignedStudentsQuery()
            ->with(['schoolClass:id,name,section'])
            ->latest()
            ->take(6)
            ->get([
                'id',
                'school_id',
                'school_class_id',
                'admission_number',
                'first_name',
                'last_name',
                'status',
                'created_at',
            ]);

        $paymentAlerts = (clone $this->assignedPaymentsQuery())
            ->with([
                'student:id,school_class_id,first_name,last_name,admission_number',
                'student.schoolClass:id,name,section',
            ])
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderByDesc('balance')
            ->orderByDesc('created_at')
            ->take(5)
            ->get([
                'id',
                'student_id',
                'reference',
                'payment_type',
                'amount_due',
                'amount_paid',
                'balance',
                'currency',
                'status',
                'created_at',
            ]);

        return view('livewire.school.teacher.dashboard', [
            'school' => $school,
            'teacher' => $this->teacher(),
            'stats' => [
                [
                    'label' => 'Assigned classes',
                    'value' => number_format(count($assignedClassIds)),
                    'hint' => 'Classes you are currently responsible for',
                    'href' => $this->schoolRoute('school.teacher.classes.index'),
                ],
                [
                    'label' => 'Students in scope',
                    'value' => number_format((clone $this->assignedStudentsQuery())->count()),
                    'hint' => 'Students in your assigned class list',
                    'href' => $this->schoolRoute('school.teacher.students.index'),
                ],
                [
                    'label' => 'Parent links',
                    'value' => number_format(
                        (clone $this->assignedStudentsQuery())
                            ->whereHas('parents', fn (Builder $query) => $query->where('users.school_id', $school->id))
                            ->count(),
                    ),
                    'hint' => 'Students who already have a linked parent',
                    'href' => $this->schoolRoute('school.teacher.assignments.index'),
                ],
                [
                    'label' => 'Outstanding payments',
                    'value' => 'NGN '.number_format((float) (clone $this->assignedPaymentsQuery())->whereIn('status', ['unpaid', 'partial'])->sum('balance'), 2),
                    'hint' => 'Remaining balances for students in your classes',
                    'href' => $this->schoolRoute('school.teacher.payments.index'),
                ],
                [
                    'label' => 'Current term',
                    'value' => $school->currentTermLabel(),
                    'hint' => $school->currentAcademicYear(),
                    'href' => $this->schoolRoute('school.teacher.results.index'),
                ],
            ],
            'assignedClasses' => $assignedClasses,
            'recentStudents' => $recentStudents,
            'paymentAlerts' => $paymentAlerts,
            'classOptions' => $assignedClasses->map(fn (SchoolClass $assignedClass) => [
                'id' => $assignedClass->id,
                'label' => $assignedClass->name.($assignedClass->section ? ' / '.$assignedClass->section : ''),
            ]),
            'moduleLinks' => [
                [
                    'label' => 'Students',
                    'description' => 'View and prepare student management for your assigned classes.',
                    'route' => $this->schoolRoute('school.teacher.students.index'),
                ],
                [
                    'label' => 'Assignments',
                    'description' => 'Link parents to students inside your assigned classes only.',
                    'route' => $this->schoolRoute('school.teacher.assignments.index'),
                ],
                [
                    'label' => 'Results',
                    'description' => 'Prepare subject and term result entry for your class lists.',
                    'route' => $this->schoolRoute('school.teacher.results.index'),
                ],
                [
                    'label' => 'Payments',
                    'description' => 'Watch pending fee records for the students you teach.',
                    'route' => $this->schoolRoute('school.teacher.payments.index'),
                ],
            ],
        ])->layout('layouts.school.teacher');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(int $schoolId): array
    {
        return [
            'schoolClassId' => [
                'required',
                'integer',
                Rule::exists('school_classes', 'id')->where(
                    fn ($query) => $query
                        ->where('school_id', $schoolId)
                        ->whereIn('id', empty($this->assignedClassIds()) ? [0] : $this->assignedClassIds()),
                ),
            ],
            'admissionNumber' => [
                'required',
                'string',
                'max:255',
                Rule::unique('students', 'admission_number')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)),
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

    protected function resetStudentForm(): void
    {
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

    protected function normalizeStudentForm(): void
    {
        $this->admissionNumber = trim($this->admissionNumber);
        $this->firstName = trim($this->firstName);
        $this->lastName = trim($this->lastName);
        $this->middleName = trim($this->middleName);
    }
}
