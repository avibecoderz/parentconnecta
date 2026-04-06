<?php

namespace App\Livewire\School\Teacher\Payments;

use App\Livewire\School\Teacher\TeacherPage;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Title('Teacher Payments')]
class Index extends TeacherPage
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'class', history: true)]
    public string $classFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $school = $this->currentSchool();
        $this->authorize('viewAny', [Payment::class, $school]);

        return view('livewire.school.teacher.payments.index', [
            'school' => $school,
            'assignedClasses' => $this->assignedClassesQuery()
                ->orderBy('name')
                ->orderBy('section')
                ->get(['school_classes.id', 'school_classes.name', 'school_classes.section']),
            'payments' => $this->paymentsQuery()->paginate(10),
            'metrics' => [
                [
                    'label' => 'Students with balance',
                    'value' => number_format(
                        (clone $this->paymentsQueryBase())
                            ->select('student_id')
                            ->distinct()
                            ->count('student_id'),
                    ),
                    'hint' => 'Assigned-class students with unpaid or partial balances',
                ],
                [
                    'label' => 'Unpaid records',
                    'value' => number_format((clone $this->paymentsQueryBase())->where('status', 'unpaid')->count()),
                    'hint' => 'Balances with no payment made yet',
                ],
                [
                    'label' => 'Partial records',
                    'value' => number_format((clone $this->paymentsQueryBase())->where('status', 'partial')->count()),
                    'hint' => 'Balances awaiting more payment',
                ],
                [
                    'label' => 'Outstanding balance',
                    'value' => 'NGN '.number_format((float) (clone $this->paymentsQueryBase())->sum('balance'), 2),
                    'hint' => 'Remaining balance for your assigned-class students',
                ],
            ],
        ])->layout('layouts.school.teacher');
    }

    protected function paymentsQueryBase(): Builder
    {
        return $this->assignedPaymentsQuery()
            ->with([
                'student:id,school_class_id,first_name,last_name,admission_number',
                'student.schoolClass:id,name,section',
            ])
            ->whereIn('status', ['unpaid', 'partial']);
    }

    protected function paymentsQuery(): Builder
    {
        return $this->paymentsQueryBase()
            ->when(
                $this->resolvedClassFilter() !== null,
                fn (Builder $query, int $classId) => $query->whereHas('student', fn (Builder $studentQuery) => $studentQuery->where('school_class_id', $classId)),
            )
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('reference', 'like', "%{$search}%")
                        ->orWhere('payment_type', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('admission_number', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('balance')
            ->orderByDesc('created_at');
    }

    protected function resolvedClassFilter(): ?int
    {
        $classId = (int) $this->classFilter;

        if ($classId === 0) {
            return null;
        }

        return in_array($classId, $this->assignedClassIds(), true) ? $classId : null;
    }
}
