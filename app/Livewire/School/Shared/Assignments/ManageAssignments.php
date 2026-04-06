<?php

namespace App\Livewire\School\Shared\Assignments;

use App\Livewire\School\Admin\SchoolAdminPage;
use App\Models\Student;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

abstract class ManageAssignments extends SchoolAdminPage
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'create', history: true, except: '0')]
    public string $create = '0';

    public bool $showLinkModal = false;

    public string $parentUserId = '';

    public string $studentId = '';

    public string $relationshipType = 'other';

    public function mount(string $slug): void
    {
        parent::mount($slug);

        if ($this->shouldOpenCreateModal()) {
            $this->openLinkModal();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openLinkModal(): void
    {
        $this->authorize('viewAny', [Student::class, $this->currentSchool()]);
        $this->create = '1';
        $this->resetValidation();
        $this->resetLinkForm();
        $this->showLinkModal = true;
    }

    public function openLinkModalForParent(int $parentId): void
    {
        $this->authorize('viewAny', [Student::class, $this->currentSchool()]);
        $parent = $this->parentLookupQuery()->findOrFail($parentId);

        $this->create = '1';
        $this->resetValidation();
        $this->resetLinkForm();
        $this->parentUserId = (string) $parent->id;
        $this->showLinkModal = true;
    }

    public function linkStudentToParent(): void
    {
        $school = $this->currentSchool();
        $validated = $this->validate($this->rules($school->id));

        $parent = $this->parentLookupQuery()->findOrFail((int) $validated['parentUserId']);
        $student = $this->studentLookupQuery()->findOrFail((int) $validated['studentId']);
        $this->authorize('linkParent', $student);

        $alreadyLinked = DB::table('parent_student')
            ->where('school_id', $school->id)
            ->where('parent_user_id', $parent->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyLinked) {
            $this->addError('studentId', 'This student is already linked to the selected parent.');

            return;
        }

        $parent->children()->attach($student->id, [
            'school_id' => $school->id,
            'relationship_type' => $validated['relationshipType'] ?: 'other',
            'is_primary' => false,
        ]);

        session()->flash('status', $student->first_name.' '.$student->last_name.' was linked to '.$parent->name.'.');

        $this->closeLinkModal();
    }

    public function unlinkStudentFromParent(int $parentId, int $studentId): void
    {
        $parent = $this->parentLookupQuery()->findOrFail($parentId);
        $student = $this->studentLookupQuery()->findOrFail($studentId);
        $this->authorize('unlinkParent', $student);

        $deleted = DB::table('parent_student')
            ->where('school_id', $this->currentSchool()->id)
            ->where('parent_user_id', $parent->id)
            ->where('student_id', $student->id)
            ->delete();

        if ($deleted === 0) {
            session()->flash('error', 'That parent-student link could not be found.');

            return;
        }

        session()->flash('status', $student->first_name.' '.$student->last_name.' was unlinked from '.$parent->name.'.');
    }

    public function closeLinkModal(): void
    {
        $this->showLinkModal = false;
        $this->create = '0';
        $this->resetLinkForm();
    }

    public function render(): View
    {
        $school = $this->currentSchool();
        $this->authorize('viewAny', [Student::class, $school]);

        return view('livewire.school.shared.assignments.index', [
            'school' => $school,
            'eyebrow' => $this->eyebrow(),
            'dashboardRoute' => $this->schoolRoute($this->dashboardRouteName()),
            'createRoute' => $this->schoolRoute($this->indexRouteName(), ['create' => 1]),
            'parents' => $this->parentsQuery()->paginate(10),
            'parentOptions' => $this->parentOptionsQuery()
                ->get(['id', 'name', 'email']),
            'studentOptions' => $this->studentLookupQuery()
                ->with('schoolClass:id,name,section')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get([
                    'id',
                    'school_id',
                    'school_class_id',
                    'first_name',
                    'last_name',
                    'admission_number',
                ]),
            'metrics' => $this->metrics(),
        ])->layout($this->layoutView());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(int $schoolId): array
    {
        return [
            'parentUserId' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($schoolId): void {
                    $parentExists = User::query()
                        ->whereKey((int) $value)
                        ->where('school_id', $schoolId)
                        ->whereHas('roles', fn (Builder $query) => $query->where('name', 'parent'))
                        ->whereDoesntHave('roles', fn (Builder $query) => $query->whereIn('name', ['school_admin', 'super_admin']))
                        ->exists();

                    if (! $parentExists) {
                        $fail('The selected parent is invalid for this school.');
                    }
                },
            ],
            'studentId' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'relationshipType' => ['nullable', Rule::in(['father', 'mother', 'guardian', 'other'])],
        ];
    }

    protected function resetLinkForm(): void
    {
        $this->parentUserId = '';
        $this->studentId = '';
        $this->relationshipType = 'other';
    }

    protected function parentsQuery(): Builder
    {
        $schoolId = $this->currentSchool()->id;

        return $this->parentLookupQuery()
            ->with([
                'children' => fn ($query) => $query
                    ->where('students.school_id', $schoolId)
                    ->with('schoolClass:id,name,section')
                    ->orderBy('students.last_name')
                    ->orderBy('students.first_name')
                    ->select([
                        'students.id',
                        'students.school_id',
                        'students.school_class_id',
                        'students.first_name',
                        'students.last_name',
                        'students.admission_number',
                    ]),
            ])
            ->withCount([
                'children as children_count' => fn ($query) => $query->where('students.school_id', $schoolId),
            ])
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('children', function (Builder $childQuery) use ($search): void {
                            $childQuery->where(function (Builder $studentQuery) use ($search): void {
                                $studentQuery
                                    ->where('students.first_name', 'like', "%{$search}%")
                                    ->orWhere('students.last_name', 'like', "%{$search}%")
                                    ->orWhere('students.admission_number', 'like', "%{$search}%");
                            });
                        });
                });
            })
            ->orderBy('name');
    }

    protected function parentOptionsQuery(): Builder
    {
        return $this->parentLookupQuery()
            ->orderBy('name');
    }

    /**
     * @return array<int, array{label: string, value: string, hint: string}>
     */
    protected function metrics(): array
    {
        $school = $this->currentSchool();

        return [
            [
                'label' => 'Assignment links',
                'value' => number_format(
                    (int) DB::table('parent_student')
                        ->where('school_id', $school->id)
                        ->count(),
                ),
                'hint' => 'All student-parent relationships for this school',
            ],
            [
                'label' => 'Students linked',
                'value' => number_format(
                    Student::query()
                        ->where('school_id', $school->id)
                        ->whereHas('parents', fn (Builder $query) => $query->where('users.school_id', $school->id))
                        ->count(),
                ),
                'hint' => 'Students with at least one parent assigned',
            ],
            [
                'label' => 'Parent profiles',
                'value' => number_format($this->parentLookupQuery()->count()),
                'hint' => 'Available parent accounts in this school',
            ],
            [
                'label' => 'Students pending links',
                'value' => number_format(
                    Student::query()
                        ->where('school_id', $school->id)
                        ->whereDoesntHave('parents', fn (Builder $query) => $query->where('users.school_id', $school->id))
                        ->count(),
                ),
                'hint' => 'Students still missing a parent assignment',
            ],
        ];
    }

    protected function parentLookupQuery(): Builder
    {
        return User::query()
            ->where('school_id', $this->currentSchool()->id)
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'parent'))
            ->whereDoesntHave('roles', fn (Builder $query) => $query->whereIn('name', ['school_admin', 'super_admin']));
    }

    protected function studentLookupQuery(): Builder
    {
        return Student::query()
            ->where('school_id', $this->currentSchool()->id);
    }

    protected function shouldOpenCreateModal(): bool
    {
        return $this->create === '1';
    }

    abstract protected function layoutView(): string;

    abstract protected function dashboardRouteName(): string;

    abstract protected function indexRouteName(): string;

    abstract protected function eyebrow(): string;
}
