<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PlatformMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $type,
        public readonly string $subject,
        public readonly string $message,
        public readonly string $audience,
        public readonly int $senderId,
        public readonly string $senderName,
        public readonly ?int $schoolId = null,
        public readonly ?string $schoolName = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'notification_type' => $this->type,
            'subject' => $this->subject,
            'message' => $this->message,
            'audience' => $this->audience,
            'sender_id' => $this->senderId,
            'sender_name' => $this->senderName,
            'school_id' => $this->schoolId,
            'school_name' => $this->schoolName,
            'sent_at' => now()->toDateTimeString(),
        ];
    }
}
