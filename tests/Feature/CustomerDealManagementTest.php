<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\Deal;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDealManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        if (! Role::where('name', 'customer')->exists()) {
            Role::create(['name' => 'customer', 'slug' => 'customer']);
        }
    }

    public function test_customer_can_access_dashboard()
    {
        $customer = User::factory()->create([
            'type' => UserType::CUSTOMER,
        ]);
        $customerRole = Role::where('name', 'customer')->first();
        $customer->roles()->attach($customerRole);

        $response = $this->actingAs($customer)->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Pages/Dashboard')
        );
    }

    public function test_customer_can_view_create_deal_page()
    {
        $customer = User::factory()->create([
            'type' => UserType::CUSTOMER,
        ]);
        $customerRole = Role::where('name', 'customer')->first();
        $customer->roles()->attach($customerRole);

        $response = $this->actingAs($customer)->get(route('customer.deals.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Deals/Create')
        );
    }

    public function test_customer_can_create_deal()
    {
        $customer = User::factory()->create([
            'type' => UserType::CUSTOMER,
        ]);
        $customerRole = Role::where('name', 'customer')->first();
        $customer->roles()->attach($customerRole);

        $dealData = [
            'title' => 'My New Project',
            'value' => 5000,
            'probability' => 75,
            'description' => 'A great opportunity',
            'expected_close_date' => now()->addDays(30)->format('Y-m-d'),
        ];

        $response = $this->actingAs($customer)->post(route('customer.deals.store'), $dealData);

        $response->assertRedirect(route('customer.deals.index'));
        $this->assertDatabaseHas('deals', [
            'title' => 'My New Project',
            'value' => 5000,
            'probability' => 75,
            'dealable_type' => 'App\Models\User',
            'dealable_id' => $customer->id,
            'stage' => 'new',
        ]);
    }

    public function test_customer_can_upload_files_to_deal()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $customer = User::factory()->create([
            'type' => UserType::CUSTOMER,
        ]);
        $customerRole = Role::where('name', 'customer')->first();
        $customer->roles()->attach($customerRole);

        $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100);

        $dealData = [
            'title' => 'Project with Files',
            'value' => 10000,
            'probability' => 50,
            'description' => 'Project with attachments',
            'expected_close_date' => now()->addDays(30)->format('Y-m-d'),
            'attachments' => [$file],
        ];

        $response = $this->actingAs($customer)->post(route('customer.deals.store'), $dealData);

        $response->assertRedirect(route('customer.deals.index'));

        $deal = Deal::where('title', 'Project with Files')->first();
        $this->assertNotNull($deal);
        $this->assertTrue($deal->hasMedia('attachments'));
        $this->assertEquals('document.pdf', $deal->getFirstMedia('attachments')->file_name);
    }

    public function test_customer_cannot_access_admin_deals()
    {
        $customer = User::factory()->create([
            'type' => UserType::CUSTOMER,
        ]);
        $customerRole = Role::where('name', 'customer')->first();
        $customer->roles()->attach($customerRole);

        $response = $this->actingAs($customer)->get(route('admin.deals.index'));

        $response->assertStatus(403);
    }
}
