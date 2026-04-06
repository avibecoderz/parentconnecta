<?php

namespace App\Livewire\School\Parent\Payments;

use App\Livewire\School\Parent\ParentPage;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Payment Records')]
class Records extends ParentPage
{
    use WithPagination;

    public string $scope = 'outstanding-records';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'term', except: 'all')]
    public string $termFilter = 'all';

    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'to', except: '')]
    public string $dateTo = '';

    protected string $paginationTheme = 'tailwind';

    public function mount(string $slug): void
    {
        parent::mount($slug);

        $this->scope = $this->resolveScopeFromRoute();
    }

    public function searchRecords(): void
    {
        $this->search = trim($this->search);
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTermFilter(): void
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
        $this->termFilter = 'all';
        $this->resetPage();
    }

    public function render(): View
    {
        $school = $this->currentSchool();
        $this->authorize('viewAny', [Student::class, $school]);
        $this->authorize('viewAny', [Payment::class, $school]);

        [$title, $description] = $this->pageMeta();

        $records = $this->recordsQuery($this->scope)
            ->with([
                'student:id,first_name,last_name,admission_number,school_class_id',
                'student.schoolClass:id,name,section',
            ])
            ->paginate(12);

        $summaryQuery = $this->recordsQuery($this->scope);

        return view('livewire.school.parent.payments.records', [
            'school' => $school,
            'scope' => $this->scope,
            'title' => $title,
            'description' => $description,
            'records' => $records,
            'recordCount' => (clone $summaryQuery)->count(),
            'totalBalance' => (float) (clone $summaryQuery)->sum('balance'),
            'totalPaid' => (float) (clone $summaryQuery)->sum('amount_paid'),
        ])->layout('layouts.school.parent');
    }

    protected function recordsQuery(string $scope): Builder
    {
        $query = $this->linkedPaymentsQuery()
            ->when($scope === 'paid-records', function (Builder $query): void {
                $query
                    ->where('status', 'paid')
                    ->whereNotNull('paid_at');
            }, function (Builder $query): void {
                $query->whereIn('status', ['unpaid', 'partial']);
            })
            ->when($this->termFilter !== 'all', fn (Builder $query) => $query->where('term', $this->termFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('payment_type', 'like', "%{$search}%")
                        ->orWhere('academic_year', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('admission_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($this->dateFrom !== '', function (Builder $query) use ($scope): void {
                $column = $scope === 'paid-records' ? 'paid_at' : 'created_at';
                $query->whereDate($column, '>=', $this->dateFrom);
            })
            ->when($this->dateTo !== '', function (Builder $query) use ($scope): void {
                $column = $scope === 'paid-records' ? 'paid_at' : 'created_at';
                $query->whereDate($column, '<=', $this->dateTo);
            });

        return $scope === 'paid-records'
            ? $query->orderByDesc('paid_at')->orderByDesc('id')
            : $query->orderByDesc('balance')->orderByDesc('created_at');
    }

    /**
     * @return array{0:string,1:string}
     */
    protected function pageMeta(): array
    {
        if ($this->scope === 'paid-records') {
            return [
                'Paid Records',
                'Review fully settled and verified payment records across your linked children.',
            ];
        }

        if ($this->scope === 'outstanding-balance') {
            return [
                'Outstanding Balance',
                'Track every payment record contributing to the remaining balance across your linked children.',
            ];
        }

        return [
            'Outstanding Records',
            'Review all open payment records that still need attention.',
        ];
    }

    protected function resolveScopeFromRoute(): string
    {
        if (request()->routeIs('school.parent.payments.paid-records')) {
            return 'paid-records';
        }

        if (request()->routeIs('school.parent.payments.outstanding-balance')) {
            return 'outstanding-balance';
        }

        return 'outstanding-records';
    }
}
