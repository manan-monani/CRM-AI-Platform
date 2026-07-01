<?php

namespace Tests\Feature\Auth;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_admin_can_view_login_page()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page->component('Admin/Auth/Login'));
    }

    public function test_admin_can_register()
    {
        $response = $this->post('/admin/register', [
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated('admin');
        $response->assertRedirect(route('admin.dashboard'));

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($user->isAdmin());
    }

    public function test_admin_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'type' => UserType::ADMIN,
        ]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user, 'admin');
        $response->assertRedirect(route('admin.dashboard'));
    }
    public function test_guests_are_redirected_to_admin_login_when_accessing_admin_dashboard()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
    }

    public function test_guests_can_view_admin_login_page_status()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_authenticated_admin_is_redirected_to_dashboard_when_accessing_login()
    {
        $admin = User::factory()->create();

        $admin->adminProfile()->create();

        $response = $this->actingAs($admin, 'admin')->get('/admin/login');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_guest_can_access_admin_login_via_link()
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page->component('Admin/Auth/Login'));
    }

    public function test_admin_can_logout()
    {
        $admin = User::factory()->create([
            'type' => UserType::ADMIN,
        ]);
        $admin->adminProfile()->create();

        $this->actingAs($admin, 'admin');

        $this->assertTrue(auth('admin')->check());

        $response = $this->post(route('admin.logout'));

        if ($response->status() !== 302) {
            $response->dd();
        }

        $response->assertRedirect(route('admin.login'));

        // Check session has no user for admin guard
        $this->assertFalse(auth('admin')->check());
    }
}