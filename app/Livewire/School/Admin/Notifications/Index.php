<?php

namespace App\Livewire\School\Admin\Notifications;

use App\Livewire\School\Admin\SchoolAdminPage;
use App\Notifications\PlatformMessageNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Title('Notifications')]
class Index extends SchoolAdminPage
{
    use WithPagination;

    #[Url(as: 'type', history: true)]
    public string $typeFilter = 'all';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = 'all';

    public function updatingTypeFilter(): void
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
        $notificationsTableAvailable = Schema::hasTable('notifications');
        $notifications = null;
        $metrics = [
            'total' => 0,
            'unread' => 0,
            'announcements' => 0,
            'alerts' => 0,
        ];

        if ($notificationsTableAvailable) {
            $this->validate([
                'typeFilter' => ['required', Rule::in(['all', 'announcement', 'system_alert'])],
                'statusFilter' => ['required', Rule::in(['all', 'read', 'unread'])],
            ]);

            $notificationsQuery = auth()->user()
                ?->notifications()
                ->where('type', PlatformMessageNotification::class);

            if ($notificationsQuery !== null) {
                $notifications = (clone $notificationsQuery)
                    ->when($this->typeFilter !== 'all', function ($query): void {
                        $query->where('data->notification_type', $this->typeFilter);
                    })
                    ->when($this->statusFilter === 'read', fn ($query) => $query->whereNotNull('read_at'))
                    ->when($this->statusFilter === 'unread', fn ($query) => $query->whereNull('read_at'))
                    ->latest()
                    ->paginate(10);

                $metrics = [
                    'total' => (clone $notificationsQuery)->count(),
                    'unread' => (clone $notificationsQuery)->whereNull('read_at')->count(),
                    'announcements' => (clone $notificationsQuery)->where('data->notification_type', 'announcement')->count(),
                    'alerts' => (clone $notificationsQuery)->where('data->notification_type', 'system_alert')->count(),
                ];
            }
        }

        return view('livewire.school.admin.notifications.index', [
            'school' => $school,
            'notifications' => $notifications,
            'notificationsTableAvailable' => $notificationsTableAvailable,
            'metrics' => $metrics,
        ])->layout('layouts.school.admin');
    }
}
