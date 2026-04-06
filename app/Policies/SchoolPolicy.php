<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class SchoolPolicy
{
    use HandlesSchoolAuthorization;

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, School $school): bool
    {
        return $this->isSchoolAdminFor($user, $school->id);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, School $school): bool
    {
        return $this->isSchoolAdminFor($user, $school->id);
    }

    public function delete(User $user, School $school): bool
    {
        return false;
    }
}
