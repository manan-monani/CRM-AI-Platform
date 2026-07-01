<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Role::where('name', 'customer')->exists()) {
            Role::create(['name' => 'customer', 'slug' => 'customer']);
        }
    }

    public function test_new_users_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test Customer',
            'email' => 'test@customer.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('customer.dashboard'));

        $user = User::where('email', 'test@customer.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserType::CUSTOMER, $user->type);

        // Check if role is assigned (This might fail currently, which is what we want to find out)
        $this->assertTrue($user->roles->contains('name', 'customer'), 'Customer role was not assigned');
    }

    public function test_registered_customer_can_login_and_create_deal()
    {
        // 1. Register
        $this->post('/register', [
            'name' => 'Deal Maker',
            'email' => 'maker@customer.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'maker@customer.com')->first();
        $user = User::where('email', 'maker@customer.com')->first();

        // 2. Create Deal
        $dealData = [
            'title' => 'Self Registered Deal',
            'value' => 1000,
            'description' => 'I just signed up!',
            'expected_close_date' => now()->addDays(7)->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)->post(route('customer.deals.store'), $dealData);

        $response->assertRedirect(route('customer.deals.index'));
        $this->assertDatabaseHas('deals', [
            'title' => 'Self Registered Deal',
            'dealable_id' => $user->id,
            'created_by' => $user->id,
        ]);
    }
}
