<?php

namespace Tests\Feature;

use App\Livewire\Admin\Notifications\Index;
use App\Models\School;
use App\Models\User;
use App\Notifications\PlatformMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_notifications_page(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('super_admin', 'web');
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->get(route('super-admin.notifications.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Send message to');
    }

    public function test_super_admin_can_send_a_notification_to_a_specific_school(): void
    {
        $superAdmin = User::factory()->create();
        Role::findOrCreate('super_admin', 'web');
        $superAdmin->assignRole('super_admin');

        $targetSchool = School::query()->create([
            'name' => 'Target School',
            'slug' => 'target-school',
            'status' => 'active',
            'timezone' => 'Africa/Lagos',
        ]);

        $otherSchool = School::query()->create([
            'name' => 'Other School',
            'slug' => 'other-school',
            'status' => 'active',
            'timezone' => 'Africa/Lagos',
        ]);

        $targetUser = User::factory()->create(['school_id' => $targetSchool->id, 'status' => 'active']);
        $otherUser = User::factory()->create(['school_id' => $otherSchool->id, 'status' => 'active']);

        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->set('audience', 'specific_school')
            ->set('notificationType', 'announcement')
            ->set('schoolId', (string) $targetSchool->id)
            ->set('subject', 'Portal maintenance')
            ->set('message', 'We will perform scheduled maintenance for this school tonight.')
            ->call('sendNotification')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $targetUser->id,
            'type' => PlatformMessageNotification::class,
        ]);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $otherUser->id,
            'type' => PlatformMessageNotification::class,
        ]);
    }
}
