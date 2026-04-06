<?php

namespace App\Livewire\School\Parent\Pupils;

use App\Livewire\School\Parent\ParentPage;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Title('My Pupils')]
class Index extends ParentPage
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'to', except: '')]
    public string $dateTo = '';

    protected string $paginationTheme = 'tailwind';

    public function searchChildren(): void
    {
        $this->search = trim($this->search);
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render(): View
    {
        $school = $this->currentSchool();
        $this->authorize('viewAny', [Student::class, $school]);

        $children = $this->linkedChildrenQuery()
            ->with([
                'schoolClass:id,name,section',
                'results' => fn ($query) => $query
                    ->where('school_id', $school->id)
                    ->whereNotNull('published_at')
                    ->latest('published_at')
                    ->latest('id')
                    ->limit(1),
                'payments' => fn ($query) => $query
                    ->where('school_id', $school->id)
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->orderByDesc('balance')
                    ->orderByDesc('created_at'),
            ])
            ->when($this->search !== '', function ($query): void {
                $search = trim($this->search);

                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('students.first_name', 'like', "%{$search}%")
                        ->orWhere('students.last_name', 'like', "%{$search}%")
                        ->orWhere('students.admission_number', 'like', "%{$search}%");
                });
            })
            ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('students.admitted_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($query) => $query->whereDate('students.admitted_at', '<=', $this->dateTo))
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->paginate(12, [
                'students.id',
                'students.school_id',
                'students.school_class_id',
                'students.first_name',
                'students.last_name',
                'students.admission_number',
                'students.status',
                'students.admitted_at',
            ]);

        return view('livewire.school.parent.pupils.index', [
            'school' => $school,
            'children' => $children,
        ])->layout('layouts.school.parent');
    }
}
