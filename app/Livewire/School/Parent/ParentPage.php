<?php

namespace App\Livewire\School\Parent;

use App\Livewire\School\Admin\SchoolAdminPage;
use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

abstract class ParentPage extends SchoolAdminPage
{
    protected function parentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    protected function linkedChildrenQuery(): BelongsToMany
    {
        return $this->parentUser()
            ->children()
            ->wherePivot('school_id', $this->currentSchool()->id)
            ->where('students.school_id', $this->currentSchool()->id);
    }

    protected function linkedChildIdsQuery(): BelongsToMany
    {
        return $this->linkedChildrenQuery()
            ->select('students.id');
    }

    protected function linkedChildQuery(int $studentId): BelongsToMany
    {
        return $this->linkedChildrenQuery()
            ->where('students.id', $studentId);
    }

    protected function linkedChildOrFail(int $studentId, array $columns = ['students.*']): Student
    {
        return $this->linkedChildQuery($studentId)
            ->firstOrFail($columns);
    }

    protected function linkedResultsQuery(): Builder
    {
        return Result::query()
            ->where('school_id', $this->currentSchool()->id)
            ->whereNotNull('published_at')
            ->whereIn('student_id', $this->linkedChildIdsQuery());
    }

    protected function linkedPaymentsQuery(): Builder
    {
        return Payment::query()
            ->where('school_id', $this->currentSchool()->id)
            ->whereIn('student_id', $this->linkedChildIdsQuery());
    }
}
