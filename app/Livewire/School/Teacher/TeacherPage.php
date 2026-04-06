<?php

namespace App\Livewire\School\Teacher;

use App\Livewire\School\Admin\SchoolAdminPage;
use App\Models\Payment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

abstract class TeacherPage extends SchoolAdminPage
{
    protected ?Collection $cachedAssignedClassIds = null;

    protected function teacher(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    protected function assignedClassesQuery(): Builder
    {
        return SchoolClass::query()
            ->select('school_classes.*')
            ->join('teacher_class', 'teacher_class.school_class_id', '=', 'school_classes.id')
            ->where('school_classes.school_id', $this->currentSchool()->id)
            ->where('teacher_class.school_id', $this->currentSchool()->id)
            ->where('teacher_class.teacher_user_id', $this->teacher()->id)
            ->distinct();
    }

    /**
     * @return list<int>
     */
    protected function assignedClassIds(): array
    {
        if ($this->cachedAssignedClassIds instanceof Collection) {
            /** @var list<int> $ids */
            $ids = $this->cachedAssignedClassIds->all();

            return $ids;
        }

        $ids = $this->assignedClassesQuery()
            ->pluck('school_classes.id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values();

        $this->cachedAssignedClassIds = $ids;

        /** @var list<int> $assignedIds */
        $assignedIds = $ids->all();

        return $assignedIds;
    }

    protected function assignedStudentsQuery(): Builder
    {
        $assignedClassIds = $this->assignedClassIds();

        return Student::query()
            ->where('school_id', $this->currentSchool()->id)
            ->whereIn('school_class_id', empty($assignedClassIds) ? [0] : $assignedClassIds);
    }

    protected function assignedPaymentsQuery(): Builder
    {
        return Payment::query()
            ->where('school_id', $this->currentSchool()->id)
            ->whereHas('student', fn (Builder $query) => $query->whereIn('school_class_id', empty($this->assignedClassIds()) ? [0] : $this->assignedClassIds()));
    }

    protected function assignedResultsQuery(): Builder
    {
        return Result::query()
            ->where('school_id', $this->currentSchool()->id)
            ->whereIn('school_class_id', empty($this->assignedClassIds()) ? [0] : $this->assignedClassIds());
    }

    /**
     * @return array<int, array{label: string, value: string, hint: string}>
     */
    protected function placeholderCards(): array
    {
        return [
            [
                'label' => 'Assigned classes',
                'value' => number_format(count($this->assignedClassIds())),
                'hint' => 'Classes currently assigned to you',
            ],
            [
                'label' => 'Students in scope',
                'value' => number_format((clone $this->assignedStudentsQuery())->count()),
                'hint' => 'Students you can currently work with',
            ],
            [
                'label' => 'Pending results',
                'value' => number_format((clone $this->assignedResultsQuery())->whereNull('published_at')->count()),
                'hint' => 'Result records not yet published',
            ],
            [
                'label' => 'Outstanding payments',
                'value' => 'NGN '.number_format((float) (clone $this->assignedPaymentsQuery())->whereIn('status', ['unpaid', 'partial'])->sum('balance'), 2),
                'hint' => 'Remaining balances for your assigned students',
            ],
        ];
    }
}
