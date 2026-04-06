<?php

namespace App\Policies;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class StudentPolicy
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

    public function view(User $user, Student $student): bool
    {
        return $this->isSchoolAdminFor($user, $student->school_id)
            || $this->canAccessStudentAsTeacher($user, $student)
            || $this->canAccessStudentAsParent($user, $student);
    }

    public function create(User $user, ?School $school = null): bool
    {
        if (! $school instanceof School) {
            return false;
        }

        return $this->isSchoolAdminFor($user, $school->id)
            || $this->isTeacherFor($user, $school->id);
    }

    public function update(User $user, Student $student): bool
    {
        return $this->isSchoolAdminFor($user, $student->school_id)
            || $this->canAccessStudentAsTeacher($user, $student);
    }

    public function delete(User $user, Student $student): bool
    {
        return $this->isSchoolAdminFor($user, $student->school_id);
    }

    public function linkParent(User $user, Student $student): bool
    {
        return $this->isSchoolAdminFor($user, $student->school_id)
            || $this->canAccessStudentAsTeacher($user, $student);
    }

    public function unlinkParent(User $user, Student $student): bool
    {
        return $this->linkParent($user, $student);
    }
}
