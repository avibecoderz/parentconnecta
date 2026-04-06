<?php

namespace App\Policies\Concerns;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;

trait HandlesSchoolAuthorization
{
    protected function belongsToSchool(User $user, int $schoolId): bool
    {
        return $user->school_id !== null && (int) $user->school_id === $schoolId;
    }

    protected function isSchoolAdminFor(User $user, int $schoolId): bool
    {
        return $user->isSchoolAdmin() && $this->belongsToSchool($user, $schoolId);
    }

    protected function isTeacherFor(User $user, int $schoolId): bool
    {
        return $user->isTeacher() && $this->belongsToSchool($user, $schoolId);
    }

    protected function isParentFor(User $user, int $schoolId): bool
    {
        return $user->isParent() && $this->belongsToSchool($user, $schoolId);
    }

    protected function canManageAssignedClass(User $user, ?SchoolClass $schoolClass): bool
    {
        return $schoolClass !== null
            && $this->isTeacherFor($user, $schoolClass->school_id)
            && $user->isAssignedToClass($schoolClass);
    }

    protected function canAccessStudentAsTeacher(User $user, Student $student): bool
    {
        return $this->canManageAssignedClass($user, $student->schoolClass);
    }

    protected function canAccessStudentAsParent(User $user, Student $student): bool
    {
        return $this->isParentFor($user, $student->school_id)
            && $user->isLinkedToStudent($student);
    }
}
