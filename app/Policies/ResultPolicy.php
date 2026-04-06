<?php

namespace App\Policies;

use App\Models\Result;
use App\Models\School;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class ResultPolicy
{
    use HandlesSchoolAuthorization;

    public function viewAny(User $user, ?School $school = null): bool
    {
        if (! $school instanceof School) {
            return false;
        }

        return $this->isSchoolAdminFor($user, $school->id)
            || $this->isTeacherFor($user, $school->id)
            || $this->isParentFor($user, $school->id);
    }

    public function view(User $user, Result $result): bool
    {
        return $this->isSchoolAdminFor($user, $result->school_id)
            || $this->canManageAssignedClass($user, $result->schoolClass)
            || (
                $result->published_at !== null
                && $result->student !== null
                && $this->canAccessStudentAsParent($user, $result->student)
            );
    }

    public function create(User $user, ?School $school = null): bool
    {
        if (! $school instanceof School) {
            return false;
        }

        return $this->isSchoolAdminFor($user, $school->id)
            || $this->isTeacherFor($user, $school->id);
    }

    public function update(User $user, Result $result): bool
    {
        return $this->isSchoolAdminFor($user, $result->school_id)
            || $this->canManageAssignedClass($user, $result->schoolClass);
    }

    public function delete(User $user, Result $result): bool
    {
        return $this->update($user, $result);
    }
}
