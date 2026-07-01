<?php

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Models\Deal;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    /** @var Permission $permission */
    $permission = Permission::firstOrCreate(['name' => Permissions::TASK_MANAGEMENT]);
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

test('admin can create task for a deal', function () {
    /** @var Deal $deal */
    $deal = Deal::factory()->create();
    /** @var User $rep */
    $rep = User::factory()->create(['type' => UserType::ADMIN]);

    $data = [
        'title' => 'Follow up with client',
        'description' => 'Send proposal by Friday',
        'assigned_to' => $rep->id,
        'due_date' => now()->addDays(2)->toDateTimeString(),
        'taskable_type' => Deal::class,
        'taskable_id' => $deal->id,
        'priority' => 'high',
    ];

    $response = $this->actingAs($this->admin, 'admin')->post(route('admin.tasks.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', [
        'title' => 'Follow up with client',
        'assigned_to' => $rep->id,
        'taskable_id' => $deal->id,
    ]);
});

test('admin can mark task as complete', function () {
    /** @var Task $task */
    $task = Task::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($this->admin, 'admin')->patch(route('admin.tasks.complete', $task));

    $response->assertRedirect();
    $this->assertEquals('completed', $task->fresh()->status);
});

test('admin can delete task', function () {
    /** @var Task $task */
    $task = Task::factory()->create();

    $response = $this->actingAs($this->admin, 'admin')->delete(route('admin.tasks.destroy', $task));

    $response->assertRedirect();
    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});
