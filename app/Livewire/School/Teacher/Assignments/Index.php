<?php

namespace App\Livewire\School\Teacher\Assignments;

use App\Livewire\School\Shared\Assignments\ManageAssignments;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;

#[Title('Parent Assignments')]
class Index extends ManageAssignments
{
    protected function studentLookupQuery(): Builder
    {
        $assignedClassIds = $this->assignedClassIds();

        return parent::studentLookupQuery()
            ->whereIn('school_class_id', empty($assignedClassIds) ? [0] : $assignedClassIds);
    }

    protected function parentsQuery(): Builder
    {
        $schoolId = $this->currentSchool()->id;
        $assignedClassIds = $this->classScopeIds();

        return $this->visibleParentProfilesQuery()
            ->with([
                'children' => fn ($query) => $query
                    ->where('students.school_id', $schoolId)
                    ->whereIn('students.school_class_id', $assignedClassIds)
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
                'children as children_count' => fn ($query) => $query
                    ->where('students.school_id', $schoolId)
                    ->whereIn('students.school_class_id', $assignedClassIds),
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

    /**
     * @return array<int, array{label: string, value: string, hint: string}>
     */
    protected function metrics(): array
    {
        $schoolId = $this->currentSchool()->id;
        $assignedClassIds = $this->classScopeIds();

        return [
            [
                'label' => 'Assignment links',
                'value' => number_format(
                    (int) DB::table('parent_student')
                        ->join('students', 'students.id', '=', 'parent_student.student_id')
                        ->where('parent_student.school_id', $schoolId)
                        ->where('students.school_id', $schoolId)
                        ->whereIn('students.school_class_id', $assignedClassIds)
                        ->count(),
                ),
                'hint' => 'Parent-student relationships inside your assigned classes',
            ],
            [
                'label' => 'Students linked',
                'value' => number_format(
                    (clone $this->studentLookupQuery())
                        ->whereHas('parents', fn (Builder $query) => $query->where('users.school_id', $schoolId))
                        ->count(),
                ),
                'hint' => 'Assigned-class students with at least one parent link',
            ],
            [
                'label' => 'Parent profiles',
                'value' => number_format((clone $this->visibleParentProfilesQuery())->count()),
                'hint' => 'Parents connected to students in your assigned classes',
            ],
            [
                'label' => 'Students pending links',
                'value' => number_format(
                    (clone $this->studentLookupQuery())
                        ->whereDoesntHave('parents', fn (Builder $query) => $query->where('users.school_id', $schoolId))
                        ->count(),
                ),
                'hint' => 'Assigned-class students still missing a parent link',
            ],
        ];
    }

    protected function layoutView(): string
    {
        return 'layouts.school.teacher';
    }

    protected function dashboardRouteName(): string
    {
        return 'school.teacher.dashboard';
    }

    protected function indexRouteName(): string
    {
        return 'school.teacher.assignments.index';
    }

    protected function eyebrow(): string
    {
        return 'Teacher';
    }

    /**
     * @return list<int>
     */
    protected function assignedClassIds(): array
    {
        /** @var User $teacher */
        $teacher = auth()->user();

        return $teacher->teachingClasses()
            ->where('school_classes.school_id', $this->currentSchool()->id)
            ->wherePivot('school_id', $this->currentSchool()->id)
            ->pluck('school_classes.id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    protected function visibleParentProfilesQuery(): Builder
    {
        $schoolId = $this->currentSchool()->id;

        return $this->parentLookupQuery()
            ->whereHas('children', fn ($query) => $query
                ->where('students.school_id', $schoolId)
                ->whereIn('students.school_class_id', $this->classScopeIds()));
    }

    /**
     * @return list<int>
     */
    protected function classScopeIds(): array
    {
        $assignedClassIds = $this->assignedClassIds();

        return empty($assignedClassIds) ? [0] : $assignedClassIds;
    }
}
