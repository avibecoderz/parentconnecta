<?php

namespace App\Livewire\School\Parent;

use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;

#[Title('Parent Dashboard')]
class Dashboard extends ParentPage
{
    public function render(): View
    {
        $school = $this->currentSchool();
        $this->authorize('viewAny', [Student::class, $school]);
        $this->authorize('viewAny', [Result::class, $school]);
        $this->authorize('viewAny', [Payment::class, $school]);

        $linkedChildren = $this->linkedChildrenQuery()
            ->with([
                'schoolClass:id,name,section',
                'payments' => fn ($query) => $query
                    ->where('school_id', $school->id)
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->orderByDesc('balance')
                    ->orderByDesc('created_at'),
            ])
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->limit(2)
            ->get([
                'students.id',
                'students.school_id',
                'students.school_class_id',
                'students.first_name',
                'students.last_name',
                'students.admission_number',
                'students.status',
            ]);

        $recentResults = $this->linkedResultsQuery()
            ->with([
                'student:id,first_name,last_name,admission_number',
                'schoolClass:id,name,section',
            ])
            ->latest('published_at')
            ->latest('id')
            ->limit(2)
            ->get();

        $outstandingPayments = $this->linkedPaymentsQuery()
            ->with([
                'student:id,first_name,last_name,admission_number,school_class_id',
                'student.schoolClass:id,name,section',
            ])
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderByDesc('balance')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $recentPaidPayments = $this->linkedPaymentsQuery()
            ->with([
                'student:id,first_name,last_name,admission_number,school_class_id',
                'student.schoolClass:id,name,section',
            ])
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('livewire.school.parent.dashboard', [
            'school' => $school,
            'linkedChildren' => $linkedChildren,
            'recentResults' => $recentResults,
            'outstandingPayments' => $outstandingPayments,
            'recentPaidPayments' => $recentPaidPayments,
            'metrics' => [
                [
                    'label' => 'Linked children',
                    'value' => number_format((clone $this->linkedChildrenQuery())->count()),
                    'hint' => 'Children linked directly to your parent account',
                    'href' => route('school.parent.pupils.index', ['slug' => $school->slug]),
                ],
                [
                    'label' => 'Outstanding records',
                    'value' => number_format((clone $this->linkedPaymentsQuery())->whereIn('status', ['unpaid', 'partial'])->count()),
                    'hint' => 'Open balances across your linked children',
                    'href' => route('school.parent.payments.outstanding-records', ['slug' => $school->slug]),
                ],
                [
                    'label' => 'Outstanding balance',
                    'value' => 'NGN '.number_format((float) (clone $this->linkedPaymentsQuery())->whereIn('status', ['unpaid', 'partial'])->sum('balance'), 2),
                    'hint' => 'Remaining balance across all linked children',
                    'href' => route('school.parent.payments.outstanding-balance', ['slug' => $school->slug]),
                ],
                [
                    'label' => 'Paid records',
                    'value' => number_format((clone $this->linkedPaymentsQuery())->where('status', 'paid')->whereNotNull('paid_at')->count()),
                    'hint' => 'Verified payment records already fully settled',
                    'href' => route('school.parent.payments.paid-records', ['slug' => $school->slug]),
                ],
                [
                    'label' => 'Current term',
                    'value' => $school->currentTermLabel(),
                    'hint' => $school->currentAcademicYear(),
                    'href' => route('school.parent.payments.index', ['slug' => $school->slug]),
                ],
            ],
        ])->layout('layouts.school.parent');
    }
}
