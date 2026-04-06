<?php

namespace App\Livewire\School\Admin\Classes;

use App\Livewire\School\Admin\SchoolAdminPage;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Title('Classes')]
class Index extends SchoolAdminPage
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = 'all';

    #[Url(as: 'create', history: true, except: '0')]
    public string $create = '0';

    public bool $showClassModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingClassId = null;

    public ?int $deletingClassId = null;

    public string $name = '';

    public ?string $section = null;

    public ?string $code = null;

    public ?string $capacity = null;

    public string $status = 'active';

    public function mount(string $slug): void
    {
        parent::mount($slug);

        if ($this->shouldOpenCreateModal()) {
            $this->createClass();
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

    public function createClass(): void
    {
        $this->create = '1';
        $this->resetValidation();
        $this->resetForm();
        $this->showClassModal = true;
    }

    public function editClass(int $classId): void
    {
        $schoolClass = $this->classLookupQuery()
            ->findOrFail($classId, ['id', 'name', 'section', 'code', 'capacity', 'status']);

        $this->resetValidation();
        $this->editingClassId = $schoolClass->id;
        $this->name = $schoolClass->name;
        $this->section = $schoolClass->section ?? '';
        $this->code = $schoolClass->code;
        $this->capacity = $schoolClass->capacity !== null ? (string) $schoolClass->capacity : null;
        $this->status = $schoolClass->status;
        $this->showClassModal = true;
    }

    public function saveClass(): void
    {
        $school = $this->currentSchool();
        $this->normalizeFormFields();

        $validated = $this->validate($this->rules($school));
        $payload = [
            'school_id' => $school->id,
            'name' => $validated['name'],
            'section' => $validated['section'] ?? '',
            'code' => $validated['code'],
            'capacity' => $validated['capacity'] !== null ? (int) $validated['capacity'] : null,
            'status' => $validated['status'],
        ];

        if ($this->editingClassId !== null) {
            $schoolClass = $this->classLookupQuery()->findOrFail($this->editingClassId);
            $schoolClass->update($payload);

            session()->flash('status', 'Class updated successfully.');
        } else {
            SchoolClass::query()->create($payload);

            session()->flash('status', 'Class created successfully.');
        }

        $this->closeClassModal();
    }

    public function confirmDelete(int $classId): void
    {
        $this->deletingClassId = $this->classLookupQuery()->findOrFail($classId, ['id'])->id;
        $this->showDeleteModal = true;
    }

    public function deleteClass(): void
    {
        $schoolClass = $this->classLookupQuery()
            ->withCount(['results', 'students'])
            ->findOrFail($this->deletingClassId);

        if ($schoolClass->results_count > 0) {
            $this->showDeleteModal = false;
            $this->deletingClassId = null;

            session()->flash('error', 'This class cannot be deleted because it already has result records.');

            return;
        }

        $studentCount = $schoolClass->students_count;
        $className = $this->displayClassName($schoolClass);
        $schoolClass->delete();

        $this->showDeleteModal = false;
        $this->deletingClassId = null;

        if ($studentCount > 0) {
            session()->flash(
                'status',
                "{$className} was deleted successfully. {$studentCount} student records were left unassigned.",
            );

            return;
        }

        session()->flash('status', "{$className} was deleted successfully.");
    }

    public function closeClassModal(): void
    {
        $this->showClassModal = false;
        $this->create = '0';
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingClassId = null;
    }

    public function render(): View
    {
        $school = $this->currentSchool();
        $classesQuery = $this->classesQuery();

        return view('livewire.school.admin.classes.index', [
            'school' => $school,
            'classes' => $classesQuery->paginate(10),
            'metrics' => [
                [
                    'label' => 'Total classes',
                    'value' => number_format(
                        SchoolClass::query()
                            ->where('school_id', $school->id)
                            ->count(),
                    ),
                    'hint' => 'Every class created for this school',
                ],
                [
                    'label' => 'Active classes',
                    'value' => number_format(
                        SchoolClass::query()
                            ->where('school_id', $school->id)
                            ->where('status', 'active')
                            ->count(),
                    ),
                    'hint' => 'Classes open to active use',
                ],
                [
                    'label' => 'Students assigned',
                    'value' => number_format(
                        Student::query()
                            ->where('school_id', $school->id)
                            ->whereNotNull('school_class_id')
                            ->count(),
                    ),
                    'hint' => 'Students currently placed into classes',
                ],
                [
                    'label' => 'Teacher assignments',
                    'value' => number_format(
                        (int) DB::table('teacher_class')
                            ->where('school_id', $school->id)
                            ->count(),
                    ),
                    'hint' => 'Teacher-class assignments already recorded',
                ],
            ],
            'deletingClass' => $this->deletingClassId
                ? $this->classLookupQuery()
                    ->withCount(['results', 'students'])
                    ->find($this->deletingClassId, ['id', 'name', 'section'])
                : null,
        ])->layout('layouts.school.admin');
    }

    protected function rules(School $school): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($school): void {
                    $query = SchoolClass::query()
                        ->where('school_id', $school->id)
                        ->where('name', $value)
                        ->where(function (Builder $sectionQuery): void {
                            if ($this->section === null || $this->section === '') {
                                $sectionQuery
                                    ->whereNull('section')
                                    ->orWhere('section', '');

                                return;
                            }

                            $sectionQuery->where('section', $this->section);
                        });

                    if ($this->editingClassId !== null) {
                        $query->whereKeyNot($this->editingClassId);
                    }

                    if ($query->exists()) {
                        $fail('A class with this name and section already exists in this school.');
                    }
                },
            ],
            'section' => ['nullable', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('school_classes', 'code')
                    ->where(fn ($query) => $query->where('school_id', $school->id))
                    ->ignore($this->editingClassId),
            ],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function resetForm(): void
    {
        $this->editingClassId = null;
        $this->name = '';
        $this->section = '';
        $this->code = null;
        $this->capacity = null;
        $this->status = 'active';
    }

    protected function normalizeFormFields(): void
    {
        $this->name = trim($this->name);
        $this->section = $this->normalizeNullableString($this->section) ?? '';
        $this->code = $this->normalizeNullableString($this->code);
        $this->capacity = $this->normalizeNullableString($this->capacity);
    }

    protected function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    protected function shouldOpenCreateModal(): bool
    {
        return $this->create === '1';
    }

    protected function classesQuery(): Builder
    {
        return $this->classLookupQuery()
            ->withCount(['students', 'teachers', 'results'])
            ->when(
                $this->statusFilter !== 'all',
                fn (Builder $query) => $query->where('status', $this->statusFilter),
            )
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('section', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->orderBy('section');
    }

    protected function classLookupQuery(): Builder
    {
        return SchoolClass::query()
            ->where('school_id', $this->currentSchool()->id);
    }

    protected function displayClassName(SchoolClass $schoolClass): string
    {
        return $schoolClass->section
            ? "{$schoolClass->name} / {$schoolClass->section}"
            : $schoolClass->name;
    }
}
