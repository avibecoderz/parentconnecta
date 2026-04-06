<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_is_redirected_to_the_super_admin_dashboard(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('super_admin', 'web');
        $user->assignRole('super_admin');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('super-admin.dashboard'));
    }

    public function test_school_admin_is_redirected_to_the_school_dashboard(): void
    {
        $school = School::query()->create([
            'name' => 'Demo School',
            'slug' => 'demo-school',
            'status' => 'active',
            'timezone' => 'Africa/Lagos',
        ]);

        $user = User::factory()->create([
            'school_id' => $school->id,
        ]);

        Role::findOrCreate('school_admin', 'web');
        $user->assignRole('school_admin');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('school.admin.dashboard', ['slug' => $school->slug]));
    }

    public function test_generic_dashboard_explains_when_no_workspace_role_is_assigned(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response
            ->assertOk()
            ->assertSee("You're logged in!")
            ->assertSee('does not currently have a dashboard workspace assigned');
    }
}
