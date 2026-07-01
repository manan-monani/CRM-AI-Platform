<?php

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->permission = Permission::firstOrCreate(['name' => Permissions::DEAL_MANAGEMENT]);
    $this->role = Role::firstOrCreate(['name' => 'Admin', 'slug' => 'admin']);
    $this->role->permissions()->sync([$this->permission->id]);

    $this->admin = User::factory()->create(['type' => UserType::ADMIN]);
    $this->admin->roles()->attach($this->role);
});

test('admin can view a deal associated with a customer without RelationNotFoundException', function () {
    // 1. Create a Customer
    $customer = User::factory()->create(['type' => UserType::CUSTOMER]);

    // 2. Create a Lead converted to that Customer
    $lead = Lead::factory()->create([
        'status' => 'converted',
        'converted_to_customer_id' => $customer->id,
        'converted_at' => now(),
    ]);

    // 3. Create a Deal associated with that Customer (User model)
    $deal = Deal::factory()->create([
        'dealable_type' => User::class,
        'dealable_id' => $customer->id,
    ]);

    // 4. Access the admin.deals.show route
    $response = $this->actingAs($this->admin, 'admin')->get(route('admin.deals.show', $deal));

    // 5. Assert success
    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Deals/Show')
        ->has('deal')
    );
});
