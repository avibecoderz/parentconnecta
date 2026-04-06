<?php

namespace App\Livewire\School\Admin\Teachers;

use App\Livewire\School\Admin\SchoolAdminPage;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\Schools\SchoolPlanLimitService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Teachers')]
class Index extends SchoolAdminPage
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = 'all';

    #[Url(as: 'create', history: true, except: '0')]
    public string $create = '0';

    public bool $showTeacherModal = false;

    public bool $showDeleteModal = false;

    public bool $showPlanLimitModal = false;

    public ?int $editingTeacherId = null;

    public ?int $deletingTeacherId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $status = 'active';

    public string $planLimitMessage = '';

    /**
     * @var array<int, string>
     */
    public array $assignedClassIds = [];

    public function mount(string $slug): void
    {
        parent::mount($slug);

        if ($this->shouldOpenCreateModal()) {
            $this->createTeacher();
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

    public function createTeacher(): void
    {
        $school = $this->currentSchool();
        $this->authorize('update', $school);

        try {
            $this->ensureTeacherCreationAllowedByPlan($school);
        } catch (ValidationException $exception) {
            if ($this->openPlanLimitModalFromValidationException($exception)) {
                $this->create = '0';
                $this->showTeacherModal = false;

                return;
            }

            throw $exception;
        }

        $this->create = '1';
        $this->resetValidation();
        $this->resetForm();
        $this->showTeacherModal = true;
    }

    public function editTeacher(int $teacherId): void
    {
        $this->authorize('update', $this->currentSchool());
        $teacher = $this->teacherLookupQuery()
            ->with(['teachingClasses' => fn ($query) => $query
                ->where('school_classes.school_id', $this->currentSchool()->id)
                ->orderBy('name')
                ->orderBy('section')])
            ->findOrFail($teacherId, ['id', 'name', 'email', 'status']);

        $this->resetValidation();
        $this->editingTeacherId = $teacher->id;
        $this->name = $teacher->name;
        $this->email = $teacher->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->status = $teacher->status;
        $this->assignedClassIds = $teacher->teachingClasses
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->values()
            ->all();
        $this->showTeacherModal = true;
    }

    public function saveTeacher(): void
    {
        try {
            $school = $this->currentSchool();
            $this->authorize('update', $school);
            $this->normalizeFormFields();
            $validated = $this->validate($this->rules($school));

            if ($this->editingTeacherId === null) {
                $this->ensureTeacherCreationAllowedByPlan($school);
            }

            DB::transaction(function () use ($school, $validated): void {
                $teacherRole = Role::findOrCreate('teacher', 'web');

                if ($this->editingTeacherId !== null) {
                    $teacher = $this->teacherLookupQuery()->findOrFail($this->editingTeacherId);
                    $teacher->fill([
                        'school_id' => $school->id,
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'status' => $validated['status'],
                    ]);

                    if (($validated['password'] ?? null) !== null && $validated['password'] !== '') {
                        $teacher->password = $validated['password'];
                    }

                    $teacher->save();

                    if (! $teacher->hasRole($teacherRole)) {
                        $teacher->assignRole($teacherRole);
                    }

                    $teacher->teachingClasses()->sync(
                        $this->teachingClassSyncPayload($teacher, $school->id, $validated['assignedClassIds'] ?? []),
                    );

                    session()->flash('status', 'Teacher updated successfully.');

                    return;
                }

                $this->ensureTeacherCreationAllowedByPlan($school);

                $teacher = User::query()->create([
                    'school_id' => $school->id,
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'status' => $validated['status'],
                ]);

                $teacher->assignRole($teacherRole);
                $teacher->teachingClasses()->sync(
                    $this->teachingClassSyncPayload($teacher, $school->id, $validated['assignedClassIds'] ?? []),
                );

                session()->flash('status', 'Teacher created successfully.');
            });

            $this->closeTeacherModal();
        } catch (ValidationException $exception) {
            if ($this->openPlanLimitModalFromValidationException($exception)) {
                return;
            }

            throw $exception;
        }
    }

    public function toggleTeacherStatus(int $teacherId): void
    {
        $this->authorize('update', $this->currentSchool());
        $teacher = $this->teacherLookupQuery()->findOrFail($teacherId);
        $nextStatus = $teacher->status === 'active' ? 'inactive' : 'active';

        $teacher->update([
            'status' => $nextStatus,
        ]);

        session()->flash(
            'status',
            $nextStatus === 'active'
                ? "{$teacher->name} has been activated."
                : "{$teacher->name} has been suspended.",
        );
    }

    public function confirmDelete(int $teacherId): void
    {
        $this->authorize('update', $this->currentSchool());
        $this->deletingTeacherId = $this->teacherLookupQuery()->findOrFail($teacherId, ['id'])->id;
        $this->showDeleteModal = true;
    }

    public function deleteTeacher(): void
    {
        $this->authorize('update', $this->currentSchool());
        $teacher = $this->teacherLookupQuery()
            ->withCount([
                'teachingClasses' => fn ($query) => $query->where('school_classes.school_id', $this->currentSchool()->id),
                'results',
            ])
            ->findOrFail($this->deletingTeacherId);

        $teacherName = $teacher->name;
        $assignmentCount = $teacher->teaching_classes_count;
        $resultCount = $teacher->results_count;

        DB::transaction(function () use ($teacher): void {
            $teacher->delete();
        });

        $this->showDeleteModal = false;
        $this->deletingTeacherId = null;

        $message = "{$teacherName} was deleted successfully.";

        if ($assignmentCount > 0 || $resultCount > 0) {
            $details = [];

            if ($assignmentCount > 0) {
                $details[] = "{$assignmentCount} class assignments were removed";
            }

            if ($resultCount > 0) {
                $details[] = "{$resultCount} result records were preserved and detached from the teacher";
            }

            $message .= ' '.implode('; ', $details).'.';
        }

        session()->flash('status', $message);
    }

    public function closeTeacherModal(): void
    {
        $this->showTeacherModal = false;
        $this->create = '0';
        $this->resetValidation();
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingTeacherId = null;
    }

    public function closePlanLimitModal(): void
    {
        $this->showPlanLimitModal = false;
        $this->planLimitMessage = '';
    }

    public function render(): View
    {
        $school = $this->currentSchool();
        $this->authorize('view', $school);

        return view('livewire.school.admin.teachers.index', [
            'school' => $school,
            'teachers' => $this->teachersQuery()->paginate(10),
            'availableClasses' => $this->availableClassesQuery()->get(['id', 'name', 'section', 'code', 'status']),
            'metrics' => [
                [
                    'label' => 'Teacher accounts',
                    'value' => number_format(
                        $this->teacherLookupQuery()->count(),
                    ),
                    'hint' => 'All teacher users under this school',
                ],
                [
                    'label' => 'Active teachers',
                    'value' => number_format(
                        $this->teacherLookupQuery()
                            ->where('status', 'active')
                            ->count(),
                    ),
                    'hint' => 'Teachers currently allowed to sign in',
                ],
                [
                    'label' => 'Teaching assignments',
                    'value' => number_format(
                        (int) DB::table('teacher_class')
                            ->where('school_id', $school->id)
                            ->count(),
                    ),
                    'hint' => 'Teacher-to-class links already recorded',
                ],
                [
                    'label' => 'Classes needing teachers',
                    'value' => number_format(
                        SchoolClass::query()
                            ->where('school_id', $school->id)
                            ->whereDoesntHave(
                                'teachers',
                                fn (Builder $query) => $query->where('users.school_id', $school->id),
                            )
                            ->count(),
                    ),
                    'hint' => 'Classes with no teacher assignment yet',
                ],
            ],
            'deletingTeacher' => $this->deletingTeacherId
                ? $this->teacherLookupQuery()
                    ->withCount([
                        'teachingClasses' => fn ($query) => $query->where('school_classes.school_id', $school->id),
                        'results',
                    ])
                    ->find($this->deletingTeacherId, ['id', 'name', 'email'])
                : null,
        ])->layout('layouts.school.admin');
    }

    protected function rules(School $school): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingTeacherId),
            ],
            'password' => [
                Rule::requiredIf($this->editingTeacherId === null),
                'nullable',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'assignedClassIds' => ['nullable', 'array'],
            'assignedClassIds.*' => [
                'integer',
                Rule::exists('school_classes', 'id')->where(
                    fn ($query) => $query->where('school_id', $school->id),
                ),
            ],
        ];
    }

    protected function resetForm(): void
    {
        $this->editingTeacherId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->status = 'active';
        $this->assignedClassIds = [];
    }

    protected function normalizeFormFields(): void
    {
        $this->name = trim($this->name);
        $this->email = strtolower(trim($this->email));
        $this->assignedClassIds = collect($this->assignedClassIds)
            ->filter(fn (mixed $classId): bool => $classId !== null && $classId !== '')
            ->map(fn (mixed $classId): string => (string) (int) $classId)
            ->unique()
            ->values()
            ->all();
    }

    protected function teachersQuery(): Builder
    {
        return $this->teacherLookupQuery()
            ->with([
                'teachingClasses' => fn ($query) => $query
                    ->where('school_classes.school_id', $this->currentSchool()->id)
                    ->orderBy('name')
                    ->orderBy('section'),
            ])
            ->withCount([
                'teachingClasses' => fn ($query) => $query->where('school_classes.school_id', $this->currentSchool()->id),
                'results',
            ])
            ->when(
                $this->statusFilter !== 'all',
                fn (Builder $query) => $query->where('status', $this->statusFilter),
            )
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');
    }

    protected function teacherLookupQuery(): Builder
    {
        return User::query()
            ->where('school_id', $this->currentSchool()->id)
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'teacher'))
            ->whereDoesntHave('roles', fn (Builder $query) => $query->whereIn('name', ['school_admin', 'super_admin']));
    }

    protected function availableClassesQuery(): Builder
    {
        return SchoolClass::query()
            ->where('school_id', $this->currentSchool()->id)
            ->orderBy('name')
            ->orderBy('section');
    }

    /**
     * @param  array<int, int|string>  $classIds
     * @return array<int, array<string, mixed>>
     */
    protected function teachingClassSyncPayload(User $teacher, int $schoolId, array $classIds): array
    {
        $existingAssignedAt = $teacher->teachingClasses()
            ->wherePivot('school_id', $schoolId)
            ->pluck('teacher_class.assigned_at', 'school_classes.id');

        return collect($classIds)
            ->map(fn (mixed $classId): int => (int) $classId)
            ->unique()
            ->values()
            ->mapWithKeys(function (int $classId, int $index) use ($schoolId, $existingAssignedAt): array {
                return [
                    $classId => [
                        'school_id' => $schoolId,
                        'is_primary' => $index === 0,
                        'assigned_at' => $existingAssignedAt->get($classId) ?? Carbon::now(),
                    ],
                ];
            })
            ->all();
    }

    protected function shouldOpenCreateModal(): bool
    {
        return $this->create === '1';
    }

    protected function ensureTeacherCreationAllowedByPlan(School $school): void
    {
        app(SchoolPlanLimitService::class)->ensureCanAddTeacher($school, 'name');
    }

    protected function openPlanLimitModalFromValidationException(ValidationException $exception): bool
    {
        $message = collect($exception->errors())
            ->flatten()
            ->first();

        if (! is_string($message)) {
            return false;
        }

        $normalizedMessage = strtolower($message);

        if (! str_contains($normalizedMessage, 'limit reached for') || ! str_contains($normalizedMessage, 'upgrade the school plan')) {
            return false;
        }

        $this->resetErrorBag();
        $this->planLimitMessage = $message;
        $this->showPlanLimitModal = true;

        return true;
    }
}
