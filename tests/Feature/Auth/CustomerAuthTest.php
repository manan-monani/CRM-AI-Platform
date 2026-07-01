<?php

namespace Tests\Feature\Auth;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_customer_can_view_login_page()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Guest/Pages/Login'));
    }

    public function test_customer_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('customer.dashboard'));

        $user = User::where('email', 'customer@example.com')->first();
        $this->assertTrue($user->isCustomer());
    }

    public function test_customer_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'type' => UserType::CUSTOMER,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_customer_logout()
    {
        $user = User::factory()->create([
            'type' => UserType::CUSTOMER,
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest('web');
        $response->assertRedirect('/');
    }

    public function test_customer_logout_does_not_logout_admin()
    {
        $admin = User::factory()->create(['type' => UserType::ADMIN]);
        $customer = User::factory()->create(['type' => UserType::CUSTOMER]);

        $this->actingAs($admin, 'admin');
        $this->actingAs($customer, 'web');

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertAuthenticatedAs($customer, 'web');

        $response = $this->post(route('logout'));

        $this->assertGuest('web');
        $this->assertAuthenticatedAs($admin, 'admin');
        $response->assertRedirect('/');
    }
}
