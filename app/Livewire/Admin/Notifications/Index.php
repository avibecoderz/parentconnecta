<?php

namespace App\Livewire\Admin\Notifications;

use App\Models\School;
use App\Models\User;
use App\Notifications\PlatformMessageNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Notifications')]
class Index extends Component
{
    protected ?bool $notificationsTableAvailable = null;

    public string $audience = 'all_schools';

    public string $notificationType = 'announcement';

    public string $schoolId = '';

    public string $subject = '';

    public string $message = '';

    public function updatedAudience(string $value): void
    {
        if ($value !== 'specific_school') {
            $this->schoolId = '';
        }
    }

    public function sendNotification(): void
    {
        if (! $this->notificationsTableAvailable()) {
            session()->flash(
                'status',
                'Notifications cannot be sent yet because the notifications table has not been migrated.',
            );

            return;
        }

        $validated = $this->validate([
            'audience' => ['required', Rule::in(['all_schools', 'specific_school'])],
            'notificationType' => ['required', Rule::in(['announcement', 'system_alert'])],
            'schoolId' => [
                Rule::requiredIf($this->audience === 'specific_school'),
                'nullable',
                'integer',
                Rule::exists('schools', 'id'),
            ],
            'subject' => ['required', 'string', 'min:3', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $school = $this->audience === 'specific_school'
            ? School::query()->findOrFail((int) $validated['schoolId'])
            : null;

        $recipients = $this->recipientQuery($school?->id)->get();

        if ($recipients->isEmpty()) {
            $this->addError('audience', 'No active users matched the selected school audience.');

            return;
        }

        $recipients->each(function (User $user) use ($validated, $school): void {
            $user->notify(new PlatformMessageNotification(
                type: $validated['notificationType'],
                subject: $validated['subject'],
                message: $validated['message'],
                audience: $validated['audience'],
                senderId: (int) auth()->id(),
                senderName: (string) auth()->user()?->name,
                schoolId: $school?->id,
                schoolName: $school?->name,
            ));
        });

        session()->flash(
            'status',
            $validated['audience'] === 'specific_school'
                ? "Notification sent to {$recipients->count()} active users in {$school?->name}."
                : "Notification sent to {$recipients->count()} active users across all schools.",
        );

        $this->reset(['schoolId', 'subject', 'message']);
        $this->audience = 'all_schools';
        $this->notificationType = 'announcement';
        $this->resetValidation();
    }

    public function render(): View
    {
        $schools = School::query()
            ->select(['id', 'name', 'slug', 'status'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.notifications.index', [
            'schools' => $schools,
            'notificationsTableAvailable' => $this->notificationsTableAvailable(),
            'metrics' => [
                'schools' => School::query()->where('status', 'active')->count(),
                'users' => $this->recipientQuery()->count(),
                'announcements' => $this->countNotificationsByType('announcement'),
                'alerts' => $this->countNotificationsByType('system_alert'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    protected function recipientQuery(?int $schoolId = null)
    {
        return User::query()
            ->where('status', 'active')
            ->whereNotNull('school_id')
            ->whereHas('school', function ($query): void {
                $query->where('status', 'active');
            })
            ->when($schoolId !== null, function ($query) use ($schoolId): void {
                $query->where('school_id', $schoolId);
            });
    }

    protected function countNotificationsByType(string $type): int
    {
        if (! $this->notificationsTableAvailable()) {
            return 0;
        }

        return DatabaseNotification::query()
            ->where('type', PlatformMessageNotification::class)
            ->where('data->notification_type', $type)
            ->where('data->sender_id', auth()->id())
            ->count();
    }

    protected function notificationsTableAvailable(): bool
    {
        if ($this->notificationsTableAvailable !== null) {
            return $this->notificationsTableAvailable;
        }

        return $this->notificationsTableAvailable = Schema::hasTable('notifications');
    }
}
