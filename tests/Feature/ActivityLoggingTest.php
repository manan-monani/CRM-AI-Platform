<?php

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    /** @var Permission $permission */
    $permission = Permission::firstOrCreate(['name' => Permissions::ACTIVITY_MANAGEMENT]);
    $this->permission = $permission;

    /** @var Role $role */
    $role = Role::firstOrCreate(['name' => 'Admin', 'slug' => 'admin']);
    $this->role = $role;

    $role->permissions()->sync([$permission->id]);

    /** @var User $admin */
    $admin = User::factory()->create(['type' => UserType::ADMIN]);
    $this->admin = $admin;

    $admin->roles()->attach($role);
});

test('admin can log activity on a deal', function () {
    /** @var Deal $deal */
    $deal = Deal::factory()->create();

    $data = [
        'type' => 'call',
        'subject' => 'Introduction Call',
        'description' => 'Discussed project requirements',
        'activityable_type' => Deal::class,
        'activityable_id' => $deal->id,
        'outcome' => 'connected',
    ];

    $response = $this->actingAs($this->admin, 'admin')->post(route('admin.activities.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('activities', [
        'subject' => 'Introduction Call',
        'activityable_type' => Deal::class,
        'activityable_id' => $deal->id,
        'performed_by' => $this->admin->id,
    ]);
});

test('admin can delete activity', function () {
    /** @var Activity $activity */
    $activity = Activity::factory()->create();

    $response = $this->actingAs($this->admin, 'admin')->delete(route('admin.activities.destroy', $activity));

    $response->assertRedirect();
    $this->assertSoftDeleted('activities', ['id' => $activity->id]);
});
