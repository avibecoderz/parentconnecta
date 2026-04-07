@php
    $pageNumbers = range(1, $directorySchools->lastPage());
    $windowStart = max(1, $directorySchools->currentPage() - 1);
    $windowEnd = min($directorySchools->lastPage(), $windowStart + 2);
    $windowStart = max(1, $windowEnd - 2);
    $visiblePages = array_filter($pageNumbers, fn (int $page) => $page >= $windowStart && $page <= $windowEnd);
@endphp

<div class="px-4 pb-10 pt-2 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-8">
        <section
            class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white px-5 py-6 shadow-[0_24px_70px_-48px_rgba(15,23,42,0.28)] sm:px-8 sm:py-8 lg:px-9 lg:py-9"
            style="background-image: linear-gradient(135deg, rgba(13, 59, 102, 0.06) 0%, rgba(37, 99, 235, 0.08) 55%, rgba(255, 255, 255, 0.96) 100%);"
        >
            <div
                class="absolute inset-y-0 right-0 hidden w-1/3 lg:block"
                style="background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 58%);"
            ></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl rounded-[1.5rem] border border-white/70 bg-white/75 px-6 py-6 shadow-[0_18px_45px_-36px_rgba(15,23,42,0.28)] backdrop-blur sm:px-7 sm:py-7 lg:px-8 lg:py-8">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-400">Platform command center</p>
                    <h1 class="mt-4 text-3xl font-semibold tracking-[-0.05em] text-slate-950 sm:text-[3.15rem]">
                        {{ auth()->user()?->name ?: 'Super Admin' }}
                    </h1>
                    <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600 sm:text-base">
                        Monitor school growth, platform usage, and tenant health from one clean control surface built for daily operations.
                    </p>
                </div>

                <div class="grid w-full gap-3 pt-1 sm:w-auto sm:grid-cols-2 lg:min-w-[34rem] lg:pt-0">
                    <a
                        href="{{ route('super-admin.schools.index', ['create' => 1]) }}"
                        wire:navigate
                        class="inline-flex w-full items-center justify-center rounded-[1.15rem] bg-[var(--pc-primary)] px-6 py-4 text-sm font-semibold text-white shadow-[0_20px_40px_-28px_rgba(13,59,102,0.45)] transition hover:bg-[var(--pc-primary-deep)] sm:min-w-[16rem] sm:w-auto"
                    >
                        Add School
                    </a>

                    <button
                        type="button"
                        wire:click="exportDirectory"
                        class="inline-flex w-full items-center justify-center rounded-[1.15rem] border border-slate-200 bg-white px-6 py-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:min-w-[16rem] sm:w-auto"
                    >
                        Export Directory
                    </button>
                </div>
            </div>
        </section>

        <section class="grid gap-5 lg:grid-cols-3">
            @foreach ($stats as $stat)
                @php
                    $toneClasses = match ($stat['tone']) {
                        'primary' => 'from-[#e9f1ff] to-white text-[#0f47b8] ring-[#d9e7ff]',
                        'success' => 'from-[#ecfdf3] to-white text-[#15803d] ring-[#d8f5e4]',
                        'danger' => 'from-[#fff1f2] to-white text-[#be123c] ring-[#ffd6de]',
                        default => 'from-slate-50 to-white text-slate-700 ring-slate-200',
                    };
                    $iconBg = match ($stat['tone']) {
                        'primary' => 'bg-[#dbeafe] text-[#0f47b8]',
                        'success' => 'bg-[#dcfce7] text-[#15803d]',
                        'danger' => 'bg-[#ffe4e6] text-[#be123c]',
                        default => 'bg-slate-100 text-slate-700',
                    };
                @endphp

                <article class="rounded-[1.65rem] bg-gradient-to-br {{ $toneClasses }} p-[1px] shadow-[0_22px_52px_-38px_rgba(15,23,42,0.22)]">
                    <div class="h-full rounded-[1.6rem] bg-white/96 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">{{ $stat['label'] }}</p>
                                <p class="mt-4 text-4xl font-semibold tracking-[-0.05em] text-slate-950">{{ number_format($stat['value']) }}</p>
                            </div>

                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] {{ $iconBg }}">
                                @if ($stat['icon'] === 'schools')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 4 3 8.5l9 4.5 9-4.5L12 4Zm-7.5 7.88V15l7.5 3.75L19.5 15v-3.12L12 15.63l-7.5-3.75Z" /></svg>
                                @elseif ($stat['icon'] === 'active')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10.01 10.01 0 0 0 12 2Zm4.24 7.76-5.66 5.66a1 1 0 0 1-1.41 0l-2.41-2.41a1 1 0 1 1 1.41-1.41l1.7 1.7 4.95-4.95a1 1 0 0 1 1.41 1.41Z" /></svg>
                                @else
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3a9 9 0 1 0 9 9 9.01 9.01 0 0 0-9-9Zm1 13h-2v-2h2Zm0-4h-2V7h2Z" /></svg>
                                @endif
                            </span>
                        </div>

                        <p class="mt-6 text-sm leading-6 text-slate-500">{{ $stat['hint'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.65fr_0.95fr]">
            <div class="space-y-6">
                <article class="pc-card overflow-hidden p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Growth</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">School Growth</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">{{ $trendMeta['summary'] }}</p>
                        </div>

                        <button
                            type="button"
                            wire:click="setTrendRange('{{ $trendMeta['range'] === 'monthly' ? 'quarterly' : 'monthly' }}')"
                            class="inline-flex items-center justify-center rounded-[1rem] bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"
                        >
                            {{ $trendMeta['range'] === 'monthly' ? 'View quarterly' : 'View monthly' }}
                        </button>
                    </div>

                    <div class="mt-6 h-80 rounded-[1.45rem] border border-slate-200/80 bg-[#f8fbff] p-4">
                        <canvas id="super-admin-school-growth-chart" wire:ignore></canvas>
                    </div>
                </article>

                <div class="grid gap-6 lg:grid-cols-2">
                    <article class="pc-card overflow-hidden p-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Engagement</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Active Users</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Tracks active account creation volume over the last six months.</p>
                        </div>

                        <div class="mt-6 h-72 rounded-[1.45rem] border border-slate-200/80 bg-white p-4">
                            <canvas id="super-admin-active-users-chart" wire:ignore></canvas>
                        </div>
                    </article>

                    <article class="pc-card overflow-hidden p-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Distribution</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">School Status</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">A quick split of active, suspended, and other tenant states.</p>
                        </div>

                        <div class="mt-6 h-72 rounded-[1.45rem] border border-slate-200/80 bg-white p-4">
                            <canvas id="super-admin-school-status-chart" wire:ignore></canvas>
                        </div>
                    </article>
                </div>
            </div>

            <div class="space-y-6">
                <article class="pc-card p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Platform pulse</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $platformHealth['value'] }}</h2>
                        </div>
                        <span class="inline-flex rounded-full bg-[#e9f1ff] px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-[#0f47b8]">
                            access rate
                        </span>
                    </div>

                    <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#0f47b8] to-[#60a5fa]" style="width: {{ $platformHealth['progress'] }}%"></div>
                    </div>

                    <p class="mt-4 text-sm leading-6 text-slate-500">{{ $platformHealth['note'] }}</p>
                </article>

                <article class="pc-card overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Directory</p>
                        <h2 class="mt-2 text-xl font-semibold tracking-tight text-slate-950">Recent schools</h2>
                    </div>

                    <div class="px-6 pt-5">
                        <x-ui.search-input
                            wire:model.live.debounce.300ms="search"
                            aria-label="Filter schools"
                            placeholder="Search schools by name, slug, or email"
                        />
                    </div>

                    <div class="mt-5 space-y-3 px-6 pb-6">
                        @forelse ($directorySchools as $school)
                            <article class="flex items-start justify-between gap-4 rounded-[1.25rem] border border-slate-200 bg-slate-50 px-4 py-4 transition hover:-translate-y-0.5 hover:bg-white">
                                <div class="flex min-w-0 items-start gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] bg-[#dce7ff] text-sm font-semibold text-[#0f47b8]">
                                        {{ str($school->name)->explode(' ')->take(2)->map(fn ($part) => str($part)->substr(0, 1))->join('') }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-semibold text-slate-950">{{ $school->name }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $school->email ?: '/school/'.$school->slug }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <x-ui.status-badge :status="$school->status" />
                                </div>
                            </article>
                        @empty
                            <x-ui.empty-state
                                title="No schools available"
                                description="New schools will appear here once the platform is provisioned."
                                class="bg-[#f8faff]"
                            />
                        @endforelse
                    </div>

                    <div class="border-t border-slate-100 px-6 py-4 text-sm text-slate-500">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <p>
                                Showing {{ $directorySchools->firstItem() ?? 0 }} to {{ $directorySchools->lastItem() ?? 0 }}
                                of {{ number_format($directorySchools->total()) }} schools
                            </p>

                            @if ($directorySchools->lastPage() > 1)
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="previousPage"
                                        @disabled($directorySchools->onFirstPage())
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-[0.95rem] bg-slate-100 text-slate-500 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <span class="sr-only">Previous page</span>
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.78 4.22a.75.75 0 0 1 0 1.06L7.06 10l4.72 4.72a.75.75 0 0 1-1.06 1.06l-5.25-5.25a.75.75 0 0 1 0-1.06l5.25-5.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                                    </button>

                                    @foreach ($visiblePages as $page)
                                        <button
                                            type="button"
                                            wire:click="gotoPage({{ $page }})"
                                            @class([
                                                'inline-flex h-10 min-w-10 items-center justify-center rounded-[0.95rem] px-3 text-sm font-semibold transition',
                                                'bg-[#0f47b8] text-white shadow-[0_14px_26px_-18px_rgba(15,71,184,0.7)]' => $directorySchools->currentPage() === $page,
                                                'bg-slate-100 text-slate-700 hover:bg-slate-200' => $directorySchools->currentPage() !== $page,
                                            ])
                                        >
                                            {{ $page }}
                                        </button>
                                    @endforeach

                                    <button
                                        type="button"
                                        wire:click="nextPage"
                                        @disabled(! $directorySchools->hasMorePages())
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-[0.95rem] bg-slate-100 text-slate-500 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <span class="sr-only">Next page</span>
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.superAdminDashboardCharts = window.superAdminDashboardCharts || {};
        window.superAdminChartPayload = @json($chartData);

        window.destroySuperAdminCharts = function () {
            Object.values(window.superAdminDashboardCharts).forEach((chart) => {
                if (chart) {
                    chart.destroy();
                }
            });

            window.superAdminDashboardCharts = {};
        };

        window.initializeSuperAdminCharts = function () {
            if (typeof Chart === 'undefined') {
                return;
            }

            window.destroySuperAdminCharts();

            const growthCanvas = document.getElementById('super-admin-school-growth-chart');
            const usersCanvas = document.getElementById('super-admin-active-users-chart');
            const statusCanvas = document.getElementById('super-admin-school-status-chart');

            if (growthCanvas) {
                window.superAdminDashboardCharts.growth = new Chart(growthCanvas, {
                    type: 'line',
                    data: {
                        labels: window.superAdminChartPayload.schoolGrowth.labels,
                        datasets: [{
                            label: 'Schools',
                            data: window.superAdminChartPayload.schoolGrowth.values,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.14)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#ffffff',
                            pointBorderWidth: 2,
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748b' }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(148, 163, 184, 0.16)' },
                                ticks: {
                                    color: '#64748b',
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            if (usersCanvas) {
                window.superAdminDashboardCharts.users = new Chart(usersCanvas, {
                    type: 'bar',
                    data: {
                        labels: window.superAdminChartPayload.activeUsers.labels,
                        datasets: [{
                            label: 'Active users',
                            data: window.superAdminChartPayload.activeUsers.values,
                            backgroundColor: ['#0f47b8', '#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'],
                            borderRadius: 14,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748b' }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(148, 163, 184, 0.16)' },
                                ticks: {
                                    color: '#64748b',
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            if (statusCanvas) {
                window.superAdminDashboardCharts.status = new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: window.superAdminChartPayload.schoolStatus.labels,
                        datasets: [{
                            data: window.superAdminChartPayload.schoolStatus.values,
                            backgroundColor: ['#16a34a', '#e11d48', '#cbd5e1'],
                            borderWidth: 0,
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#475569',
                                    usePointStyle: true,
                                    boxWidth: 10,
                                    padding: 18,
                                }
                            }
                        }
                    }
                });
            }
        };

        if (! window.superAdminDashboardListenersRegistered) {
            document.addEventListener('DOMContentLoaded', window.initializeSuperAdminCharts);
            document.addEventListener('livewire:navigated', window.initializeSuperAdminCharts);
            window.superAdminDashboardListenersRegistered = true;
        }

        window.initializeSuperAdminCharts();
    </script>
@endpush
