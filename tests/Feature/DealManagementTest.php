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

test('admin can create a deal', function () {
    $lead = Lead::factory()->create();
    $rep = User::factory()->create(['type' => UserType::ADMIN]);

    $data = [
        'title' => 'New Enterprise Deal',
        'value' => 10000,
        'stage' => 'proposal',
        'dealable_type' => Lead::class,
        'dealable_id' => $lead->id,
        'assigned_to' => $rep->id,
        'expected_close_date' => now()->addMonths(1)->toDateTimeString(),
    ];

    $response = $this->actingAs($this->admin, 'admin')->post(route('admin.deals.store'), $data);

    $response->assertRedirect(route('admin.deals.index'));
    $this->assertDatabaseHas('deals', [
        'title' => 'New Enterprise Deal',
        'value' => 10000,
        'assigned_to' => $rep->id,
    ]);
});

test('admin can update deal stage', function () {
    $deal = Deal::factory()->create(['stage' => 'new']);

    $response = $this->actingAs($this->admin, 'admin')->patch(route('admin.deals.stage', $deal), [
        'stage' => 'won',
    ]);

    $response->assertRedirect();
    $this->assertEquals('won', $deal->fresh()->stage);
});

test('admin can delete a deal', function () {
    $deal = Deal::factory()->create();

    $response = $this->actingAs($this->admin, 'admin')->delete(route('admin.deals.destroy', $deal));

    $response->assertRedirect(route('admin.deals.index'));
    $this->assertSoftDeleted('deals', ['id' => $deal->id]);
});
test('manager can create a deal', function () {
    $managerRole = Role::firstOrCreate(['name' => 'Manager', 'slug' => 'manager']);
    $managerRole->permissions()->sync([$this->permission->id]);

    $lead = Lead::factory()->create();
    $manager = User::factory()->create([
        'type' => UserType::ADMIN,
        'sales_role' => \App\Enums\SalesRole::MANAGER,
    ]);
    $manager->roles()->attach($managerRole);

    $data = [
        'title' => 'Manager Deal',
        'value' => 5000,
        'stage' => 'new',
        'dealable_type' => Lead::class,
        'dealable_id' => $lead->id,
        'assigned_to' => $manager->id,
        'expected_close_date' => now()->addMonths(1)->toDateTimeString(),
    ];

    $response = $this->actingAs($manager, 'admin')->post(route('admin.deals.store'), $data);

    $response->assertRedirect(route('admin.deals.index'));
    $this->assertDatabaseHas('deals', [
        'title' => 'Manager Deal',
        'value' => 5000,
        'assigned_to' => $manager->id,
    ]);
});
