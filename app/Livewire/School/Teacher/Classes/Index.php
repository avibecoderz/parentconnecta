<?php

namespace App\Livewire\School\Teacher\Classes;

use App\Livewire\School\Teacher\TeacherPage;
use App\Models\SchoolClass;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Title('Teacher Classes')]
class Index extends TeacherPage
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $school = $this->currentSchool();
        $classesQuery = $this->classesQuery();

        return view('livewire.school.teacher.classes.index', [
            'school' => $school,
            'classes' => $classesQuery->paginate(10),
            'metrics' => [
                [
                    'label' => 'Assigned classes',
                    'value' => number_format((clone $this->assignedClassesQuery())->count()),
                    'hint' => 'Classes currently assigned to you',
                ],
                [
                    'label' => 'Active classes',
                    'value' => number_format((clone $this->assignedClassesQuery())->where('school_classes.status', 'active')->count()),
                    'hint' => 'Assigned classes that are currently active',
                ],
                [
                    'label' => 'Students in scope',
                    'value' => number_format((clone $this->assignedStudentsQuery())->count()),
                    'hint' => 'Students inside your assigned classes',
                ],
                [
                    'label' => 'Parent-linked students',
                    'value' => number_format(
                        (clone $this->assignedStudentsQuery())
                            ->whereHas('parents', fn (Builder $query) => $query->where('users.school_id', $school->id))
                            ->count(),
                    ),
                    'hint' => 'Assigned-class students who already have a linked parent',
                ],
            ],
        ])->layout('layouts.school.teacher');
    }

    protected function classesQuery(): Builder
    {
        return $this->assignedClassesQuery()
            ->withCount([
                'students as students_count' => fn (Builder $query) => $query
                    ->where('students.school_id', $this->currentSchool()->id),
                'students as active_students_count' => fn (Builder $query) => $query
                    ->where('students.school_id', $this->currentSchool()->id)
                    ->where('students.status', 'active'),
                'teachers as teachers_count' => fn (Builder $query) => $query
                    ->where('users.school_id', $this->currentSchool()->id),
            ])
            ->when($this->statusFilter !== 'all', fn (Builder $query) => $query->where('school_classes.status', $this->statusFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('school_classes.name', 'like', "%{$search}%")
                        ->orWhere('school_classes.section', 'like', "%{$search}%")
                        ->orWhere('school_classes.code', 'like', "%{$search}%");
                });
            })
            ->orderBy('school_classes.name')
            ->orderBy('school_classes.section');
    }
}
