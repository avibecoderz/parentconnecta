<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Notifications\PlatformMessageNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function markAsRead(Request $request, string $slug, string $notification): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $notificationModel = $user->notifications()
            ->where('type', PlatformMessageNotification::class)
            ->whereKey($notification)
            ->firstOrFail();

        if ($notificationModel->read_at === null) {
            $notificationModel->markAsRead();
        }

        return back();
    }

    public function markAllAsRead(Request $request, string $slug): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $user->unreadNotifications()
            ->where('type', PlatformMessageNotification::class)
            ->update([
            'read_at' => now(),
        ]);

        return back();
    }
}
