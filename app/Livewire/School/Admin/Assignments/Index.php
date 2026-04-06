<?php

namespace App\Livewire\School\Admin\Assignments;

use App\Livewire\School\Shared\Assignments\ManageAssignments;
use Livewire\Attributes\Title;

#[Title('Parent Assignments')]
class Index extends ManageAssignments
{
    protected function layoutView(): string
    {
        return 'layouts.school.admin';
    }

    protected function dashboardRouteName(): string
    {
        return 'school.admin.dashboard';
    }

    protected function indexRouteName(): string
    {
        return 'school.admin.assignments.index';
    }

    protected function eyebrow(): string
    {
        return 'School Admin';
    }
}
