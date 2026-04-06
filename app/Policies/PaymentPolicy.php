<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\School;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class PaymentPolicy
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

    public function view(User $user, Payment $payment): bool
    {
        return $this->isSchoolAdminFor($user, $payment->school_id)
            || ($payment->student !== null && $this->canAccessStudentAsTeacher($user, $payment->student))
            || ($payment->student !== null && $this->canAccessStudentAsParent($user, $payment->student));
    }

    public function create(User $user, ?School $school = null): bool
    {
        return $school instanceof School
            && $this->isSchoolAdminFor($user, $school->id);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->isSchoolAdminFor($user, $payment->school_id);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->isSchoolAdminFor($user, $payment->school_id);
    }
}
