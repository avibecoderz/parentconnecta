<?php

namespace App\Livewire\Admin;

use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Super Admin Dashboard')]
class Dashboard extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public string $trendRange = 'monthly';

    protected string $paginationTheme = 'tailwind';

    public function updatedSearch(string $value): void
    {
        $this->search = $this->normalizedSearch($value);
        $this->resetPage();
    }

    public function setTrendRange(string $range): void
    {
        if (! in_array($range, ['monthly', 'quarterly'], true)) {
            return;
        }

        $this->trendRange = $range;
    }

    public function exportDirectory(): StreamedResponse
    {
        $schools = $this->schoolDirectoryQuery()
            ->orderBy('name')
            ->get(['name', 'slug', 'email', 'status', 'created_at']);

        $filename = 'parentconnecta-school-directory-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($schools): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Name', 'Slug', 'Email', 'Status', 'Created At']);

            foreach ($schools as $school) {
                fputcsv($handle, [
                    $school->name,
                    $school->slug,
                    $school->email,
                    $school->status,
                    $school->created_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render(): View
    {
        $schoolStatusCounts = $this->schoolStatusCounts();
        $totalSchools = (int) ($schoolStatusCounts['total'] ?? 0);
        $activeSchools = (int) ($schoolStatusCounts['active'] ?? 0);
        $suspendedSchools = (int) ($schoolStatusCounts['suspended'] ?? 0);

        $schoolRegistrationCounts = $this->schoolRegistrationCounts();
        $newSchoolsThisMonth = (int) ($schoolRegistrationCounts['current_month'] ?? 0);
        $newSchoolsLastMonth = (int) ($schoolRegistrationCounts['previous_month'] ?? 0);
        $directorySchools = $this->schoolDirectoryQuery()
            ->orderBy('name')
            ->paginate(10, ['id', 'name', 'slug', 'email', 'status', 'created_at']);

        $tenantAccessRate = $totalSchools > 0
            ? round(($activeSchools / $totalSchools) * 100, 2)
            : 100.00;

        $trendPoints = $this->buildTrendPoints($this->trendRange);
        $activeUserTrend = $this->buildActiveUserTrendPoints();
        $statusChart = [
            'labels' => ['Active Schools', 'Suspended Schools', 'Other'],
            'values' => [
                $activeSchools,
                $suspendedSchools,
                max($totalSchools - $activeSchools - $suspendedSchools, 0),
            ],
        ];

        return view('livewire.admin.dashboard', [
            'stats' => [
                [
                    'label' => 'Total schools',
                    'value' => $totalSchools,
                    'hint' => $newSchoolsThisMonth > 0
                        ? '+'.$newSchoolsThisMonth.' onboarded this month'
                        : 'No new schools this month',
                    'tone' => 'primary',
                    'icon' => 'schools',
                ],
                [
                    'label' => 'Active schools',
                    'value' => $activeSchools,
                    'hint' => $totalSchools > 0
                        ? number_format($tenantAccessRate, 1).'% tenant access rate'
                        : 'Ready for first school onboarding',
                    'tone' => 'success',
                    'icon' => 'active',
                ],
                [
                    'label' => 'Suspended schools',
                    'value' => $suspendedSchools,
                    'hint' => $suspendedSchools > 0
                        ? 'Requires attention from operations'
                        : 'No suspended schools right now',
                    'tone' => $suspendedSchools > 0 ? 'danger' : 'neutral',
                    'icon' => 'suspended',
                ],
            ],
            'platformHealth' => [
                'label' => 'Tenant access rate',
                'value' => number_format($tenantAccessRate, 2).'%',
                'progress' => min(100, max($tenantAccessRate, 0)),
                'note' => $totalSchools === 0
                    ? 'No schools have been onboarded yet. The platform is ready for the first tenant.'
                    : ($suspendedSchools > 0
                    ? $suspendedSchools.' suspended school account(s) currently need review.'
                    : 'Stable platform access across all active schools.'),
            ],
            'trendMeta' => [
                'range' => $this->trendRange,
                'summary' => $this->trendSummary($trendPoints, $newSchoolsThisMonth, $newSchoolsLastMonth),
            ],
            'chartData' => [
                'schoolGrowth' => [
                    'labels' => array_column($trendPoints, 'label'),
                    'values' => array_column($trendPoints, 'value'),
                ],
                'activeUsers' => [
                    'labels' => array_column($activeUserTrend, 'label'),
                    'values' => array_column($activeUserTrend, 'value'),
                ],
                'schoolStatus' => $statusChart,
            ],
            'directorySchools' => $directorySchools,
        ]);
    }

    private function schoolDirectoryQuery(): Builder
    {
        $search = $this->normalizedSearch();

        return School::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            });
    }

    /**
     * @return array{total:int, active:int, suspended:int}
     */
    private function schoolStatusCounts(): array
    {
        /** @var object{total:int|string|null,active:int|string|null,suspended:int|string|null}|null $totals */
        $totals = Cache::remember(
            'admin.dashboard.school-status-counts',
            now()->addMinutes(2),
            fn () => School::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
                ->selectRaw("SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended")
                ->first()
        );

        return [
            'total' => (int) ($totals?->total ?? 0),
            'active' => (int) ($totals?->active ?? 0),
            'suspended' => (int) ($totals?->suspended ?? 0),
        ];
    }

    /**
     * @return array{current_month:int, previous_month:int}
     */
    private function schoolRegistrationCounts(): array
    {
        $startOfCurrentMonth = now()->startOfMonth();
        $endOfCurrentMonth = now()->endOfMonth();
        $startOfPreviousMonth = now()->copy()->subMonth()->startOfMonth();
        $endOfPreviousMonth = now()->copy()->subMonth()->endOfMonth();

        /** @var object{current_month:int|string|null,previous_month:int|string|null}|null $counts */
        $counts = Cache::remember(
            'admin.dashboard.school-registration-counts',
            now()->addMinutes(2),
            fn () => School::query()
                ->selectRaw(
                    'SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as current_month',
                    [$startOfCurrentMonth, $endOfCurrentMonth]
                )
                ->selectRaw(
                    'SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as previous_month',
                    [$startOfPreviousMonth, $endOfPreviousMonth]
                )
                ->first()
        );

        return [
            'current_month' => (int) ($counts?->current_month ?? 0),
            'previous_month' => (int) ($counts?->previous_month ?? 0),
        ];
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function buildTrendPoints(string $range): array
    {
        return $range === 'quarterly'
            ? $this->buildQuarterlyTrendPoints()
            : $this->buildMonthlyTrendPoints();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function buildMonthlyTrendPoints(): array
    {
        $start = now()->startOfMonth()->subMonths(5);
        $end = now()->endOfMonth();

        /** @var array<string, int|string> $counts */
        $counts = Cache::remember(
            'admin.dashboard.school-growth.monthly',
            now()->addMinutes(5),
            fn () => School::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bucket, COUNT(*) as aggregate")
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('bucket')
                ->pluck('aggregate', 'bucket')
                ->all()
        );

        return collect(range(0, 5))
            ->map(function (int $offset) use ($start, $counts): array {
                $month = $start->copy()->addMonths($offset);
                $bucket = $month->format('Y-m');

                return [
                    'label' => strtoupper($month->format('M')),
                    'value' => (int) ($counts[$bucket] ?? 0),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function buildQuarterlyTrendPoints(): array
    {
        $start = now()->firstOfQuarter()->subQuarters(5);
        $end = now()->endOfQuarter();

        /** @var array<string, int|string> $counts */
        $counts = Cache::remember(
            'admin.dashboard.school-growth.quarterly',
            now()->addMinutes(5),
            fn () => School::query()
                ->selectRaw("CONCAT(YEAR(created_at), '-Q', QUARTER(created_at)) as bucket, COUNT(*) as aggregate")
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('bucket')
                ->pluck('aggregate', 'bucket')
                ->all()
        );

        return collect(range(0, 5))
            ->map(function (int $offset) use ($start, $counts): array {
                $quarterStart = $start->copy()->addQuarters($offset);
                $quarter = $quarterStart->quarter;
                $bucket = $quarterStart->year.'-Q'.$quarter;

                return [
                    'label' => 'Q'.$quarter.' '.$quarterStart->format('y'),
                    'value' => (int) ($counts[$bucket] ?? 0),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function buildActiveUserTrendPoints(): array
    {
        $start = now()->startOfMonth()->subMonths(5);
        $end = now()->endOfMonth();

        /** @var array<string, int|string> $counts */
        $counts = Cache::remember(
            'admin.dashboard.active-users.monthly',
            now()->addMinutes(5),
            fn () => User::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bucket, COUNT(*) as aggregate")
                ->where('status', 'active')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('bucket')
                ->pluck('aggregate', 'bucket')
                ->all()
        );

        return collect(range(0, 5))
            ->map(function (int $offset) use ($start, $counts): array {
                $month = $start->copy()->addMonths($offset);
                $bucket = $month->format('Y-m');

                return [
                    'label' => strtoupper($month->format('M')),
                    'value' => (int) ($counts[$bucket] ?? 0),
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array{label: string, value: int}>  $trendPoints
     */
    private function trendSummary(array $trendPoints, int $newSchoolsThisMonth, int $newSchoolsLastMonth): string
    {
        $totalTrendCount = array_sum(array_column($trendPoints, 'value'));

        if ($this->trendRange === 'quarterly') {
            return $totalTrendCount.' total registrations tracked across the current 6-quarter window.';
        }

        if ($newSchoolsThisMonth === 0 && $newSchoolsLastMonth === 0) {
            return 'No new schools registered across the last two months yet.';
        }

        $difference = $newSchoolsThisMonth - $newSchoolsLastMonth;

        if ($difference > 0) {
            return 'Up by '.$difference.' school(s) compared with last month.';
        }

        if ($difference < 0) {
            return 'Down by '.abs($difference).' school(s) compared with last month.';
        }

        return 'Matching last month with '.$newSchoolsThisMonth.' new school(s).';
    }

    private function normalizedSearch(?string $value = null): string
    {
        return trim($value ?? $this->search);
    }
}
