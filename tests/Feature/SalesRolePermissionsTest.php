<?php

use App\Constants\Permissions;
use App\Enums\SalesRole;
use App\Enums\UserType;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    // Setup Permissions
    $permissions = [
        Permissions::DEAL_MANAGEMENT,
        Permissions::TASK_MANAGEMENT,
        Permissions::ACTIVITY_MANAGEMENT,
    ];

    foreach ($permissions as $p) {
        Permission::firstOrCreate(['name' => $p]);
    }

    $this->salesRole = Role::firstOrCreate(['name' => 'Sales Rep', 'slug' => 'sales-rep']);
    $this->salesRole->permissions()->sync(Permission::whereIn('name', $permissions)->pluck('id'));

    $this->managerRole = Role::firstOrCreate(['name' => 'Manager', 'slug' => 'manager']);
    $this->managerRole->permissions()->sync(Permission::whereIn('name', $permissions)->pluck('id'));
});

test('sales rep can only see their own deals', function () {
    /** @var User $rep */
    $rep = User::factory()->create([
        'type' => UserType::ADMIN,
        'sales_role' => SalesRole::REP,
    ]);
    $rep->roles()->attach($this->salesRole);

    /** @var User $otherRep */
    $otherRep = User::factory()->create([
        'type' => UserType::ADMIN,
        'sales_role' => SalesRole::REP,
    ]);

    $ownDeal = Deal::factory()->create(['assigned_to' => $rep->id, 'title' => 'My Deal']);
    $otherDeal = Deal::factory()->create(['assigned_to' => $otherRep->id, 'title' => 'Other Deal']);

    $response = $this->actingAs($rep)->get(route('admin.deals.index'));

    $response->assertStatus(200);
    $response->assertSee('My Deal');
    $response->assertDontSee('Other Deal');
});

test('sales rep cannot access other rep deal details', function () {
    /** @var User $rep */
    $rep = User::factory()->create([
        'type' => UserType::ADMIN,
        'sales_role' => SalesRole::REP,
    ]);
    $rep->roles()->attach($this->salesRole);

    $otherRep = User::factory()->create(['type' => UserType::ADMIN]);
    $otherDeal = Deal::factory()->create(['assigned_to' => $otherRep->id]);

    $response = $this->actingAs($rep)->get(route('admin.deals.show', $otherDeal));

    $response->assertStatus(403);
});

test('sales rep can only see their own tasks', function () {
    /** @var User $rep */
    $rep = User::factory()->create([
        'type' => UserType::ADMIN,
        'sales_role' => SalesRole::REP,
    ]);
    $rep->roles()->attach($this->salesRole);

    $otherRep = User::factory()->create(['type' => UserType::ADMIN]);

    $ownTask = Task::factory()->create(['assigned_to' => $rep->id, 'title' => 'My Task']);
    $otherTask = Task::factory()->create(['assigned_to' => $otherRep->id, 'title' => 'Other Task']);

    $response = $this->actingAs($rep)->get(route('admin.tasks.index'));

    $response->assertStatus(200);
    $response->assertSee('My Task');
    $response->assertDontSee('Other Task');
});

test('manager can see all deals', function () {
    /** @var User $manager */
    $manager = User::factory()->create([
        'type' => UserType::ADMIN,
        'sales_role' => SalesRole::MANAGER,
    ]);
    $manager->roles()->attach($this->managerRole);

    $rep = User::factory()->create(['type' => UserType::ADMIN]);
    $deal = Deal::factory()->create(['assigned_to' => $rep->id, 'title' => 'Rep Deal']);

    $response = $this->actingAs($manager)->get(route('admin.deals.index'));

    $response->assertStatus(200);
    $response->assertSee('Rep Deal');
});

test('sales rep cannot delete other user activity', function () {
    /** @var User $rep */
    $rep = User::factory()->create([
        'type' => UserType::ADMIN,
        'sales_role' => SalesRole::REP,
    ]);
    $rep->roles()->attach($this->salesRole);

    $otherUser = User::factory()->create(['type' => UserType::ADMIN]);
    $activity = Activity::factory()->create(['performed_by' => $otherUser->id]);

    $response = $this->actingAs($rep)->delete(route('admin.activities.destroy', $activity));

    $response->assertStatus(403);
});
