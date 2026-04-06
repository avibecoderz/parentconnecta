<?php

namespace App\Livewire\School\Parent\Pupils;

use App\Livewire\School\Parent\ParentPage;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;

#[Title('Pupil Details')]
class Show extends ParentPage
{
    public int $studentId;

    public function mount(string $slug, int|string $student = 0): void
    {
        parent::mount($slug);
        $this->studentId = (int) $student;
    }

    public function render(): View
    {
        $school = $this->currentSchool();

        /** @var Student $child */
        $child = $this->linkedChildOrFail($this->studentId, [
            'students.id',
            'students.school_id',
            'students.school_class_id',
            'students.first_name',
            'students.last_name',
            'students.middle_name',
            'students.admission_number',
            'students.date_of_birth',
            'students.gender',
            'students.status',
            'students.admitted_at',
        ]);
        $this->authorize('view', $child);

        $child->load('schoolClass:id,name,section');

        $recentResults = $child->results()
            ->where('school_id', $school->id)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->values();

        $paymentHistory = $child->payments()
            ->where('school_id', $school->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->values();

        $outstandingBalance = (float) $child->payments()
            ->where('school_id', $school->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('balance');

        return view('livewire.school.parent.pupils.show', [
            'school' => $school,
            'child' => $child,
            'recentResults' => $recentResults,
            'paymentHistory' => $paymentHistory,
            'outstandingBalance' => $outstandingBalance,
        ])->layout('layouts.school.parent');
    }
}
