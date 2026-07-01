<?php

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (! Schema::hasTable('permissions')) {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        }
        );
    }

    if (! Schema::hasTable('role_permissions')) {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        }
        );
    }
});

function givePermissionTo(User $user, string $permissionName)
{
    // Ensure roles table exists (it should via migration, but safety first)
    if (! Schema::hasTable('roles')) {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
            $table->json('permissions')->nullable();
        });
    }

    $permission = Permission::firstOrCreate(['name' => $permissionName]);
    $role = Role::firstOrCreate(['name' => 'Manager', 'slug' => 'manager']);
    $role->permissions()->syncWithoutDetaching([$permission->id]);
    $user->roles()->syncWithoutDetaching([$role->id]);
}

test('super admin can see all deals', function () {
    $superAdmin = User::factory()->create(['type' => UserType::SUPER_ADMIN]);
    $otherUser = User::factory()->create(['type' => UserType::ADMIN]);

    $deal1 = Deal::factory()->create(['assigned_to' => $superAdmin->id]);
    $deal2 = Deal::factory()->create(['assigned_to' => $otherUser->id]);
    $deal3 = Deal::factory()->create(['assigned_to' => null]);

    $response = $this->actingAs($superAdmin, 'admin')->get(route('admin.deals.index'));

    $response->assertStatus(200);
    // In Inertia, we'd check prop data, but for now let's assume if it loads it's good.
    // We can filter the prop 'deals.data' to see if all ids are present.
    $deals = $response->inertiaProps('deals.data');
    expect($deals)->toHaveCount(3);
});

test('staff sees only their own, created by them, or unassigned deals', function () {
    $staff = User::factory()->create(['type' => UserType::ADMIN]);
    givePermissionTo($staff, Permissions::DEAL_MANAGEMENT);
    $otherUser = User::factory()->create(['type' => UserType::ADMIN]);

    $assignedDeal = Deal::factory()->create(['assigned_to' => $staff->id]);
    $createdDeal = Deal::factory()->create(['created_by' => $staff->id, 'assigned_to' => $otherUser->id]);
    $unassignedDeal = Deal::factory()->create(['assigned_to' => null]);
    $otherDeal = Deal::factory()->create(['assigned_to' => $otherUser->id, 'created_by' => $otherUser->id]);

    $response = $this->actingAs($staff, 'admin')->get(route('admin.deals.index'));

    $response->assertStatus(200);
    $deals = collect($response->inertiaProps('deals.data'));

    expect($deals->pluck('id'))
        ->toContain($assignedDeal->id)
        ->toContain($createdDeal->id)
        ->toContain($unassignedDeal->id)
        ->not->toContain($otherDeal->id);
});

test('staff sees assigned tasks, created tasks, and tasks from assigned deals', function () {
    $staff = User::factory()->create(['type' => UserType::ADMIN]);
    givePermissionTo($staff, Permissions::TASK_MANAGEMENT);
    $otherUser = User::factory()->create(['type' => UserType::ADMIN]);

    $assignedTask = Task::factory()->create(['assigned_to' => $staff->id]);
    $createdTask = Task::factory()->create(['created_by' => $staff->id, 'assigned_to' => $otherUser->id]);

    // Task belonging to a Deal assigned to Staff
    $myDeal = Deal::factory()->create(['assigned_to' => $staff->id]);
    $dealTask = Task::factory()->create([
        'taskable_type' => Deal::class,
        'taskable_id' => $myDeal->id,
        'assigned_to' => $otherUser->id,
    ]);

    $otherTask = Task::factory()->create(['assigned_to' => $otherUser->id, 'created_by' => $otherUser->id]);

    $response = $this->actingAs($staff, 'admin')->get(route('admin.tasks.index', ['status' => 'all']));

    $response->assertStatus(200);
    $tasks = collect($response->inertiaProps('tasks.data'));

    expect($tasks->pluck('id'))
        ->toContain($assignedTask->id)
        ->toContain($createdTask->id)
        ->toContain($dealTask->id)
        ->not->toContain($otherTask->id);
});

test('staff activity notifies super admin', function () {
    Notification::fake();

    $staff = User::factory()->create(['type' => UserType::ADMIN]);
    givePermissionTo($staff, Permissions::DEAL_MANAGEMENT);

    $superAdmin = User::factory()->create(['type' => UserType::SUPER_ADMIN]);

    $deal = Deal::factory()->create(['assigned_to' => $staff->id]);

    // Note: ActivityObserver notification logic is inside the Observer, which triggers on Activity creation.
    // We don't need a specific actingAs for the factory creation unless the observer relies on auth()->user() which it might.
    // The observer uses $activity->user (assigned in factory) but LogsActivity trait uses auth()->id().
    // The previous test logic for notification was checking ActivityObserver.

    // However, we are not making an HTTP request here, we are just creating models.
    $activity = Activity::factory()->create([
        'performed_by' => $staff->id,
        'activityable_type' => Deal::class,
        'activityable_id' => $deal->id,
        'description' => 'Did something',
    ]);

    // Check if notification was sent to Super Admin
    Notification::assertSentTo(
        [$superAdmin],
        ActivityNotification::class
    );

    // Check if notification was NOT sent to staff (optional, but good practice)
    Notification::assertNotSentTo(
        [$staff],
        ActivityNotification::class
    );
});

test('dashboard shows global stats for super admin', function () {
    $superAdmin = User::factory()->create(['type' => UserType::SUPER_ADMIN]);
    $staff = User::factory()->create(['type' => UserType::ADMIN]);

    // Deals with various statuses
    Deal::factory()->create(['status' => 'open', 'assigned_to' => $superAdmin->id]);
    Deal::factory()->create(['status' => 'active', 'assigned_to' => $staff->id]);
    Deal::factory()->create(['status' => 'won', 'assigned_to' => $superAdmin->id]); // Should be excluded

    // Tasks with various statuses
    Task::factory()->create(['status' => 'pending', 'assigned_to' => $superAdmin->id]);
    Task::factory()->create(['status' => 'in_progress', 'assigned_to' => $staff->id]);
    Task::factory()->create(['status' => 'completed', 'assigned_to' => $superAdmin->id]); // Should be excluded

    $response = $this->actingAs($superAdmin, 'admin')->get(route('admin.dashboard'));

    $response->assertStatus(200);
    $stats = $response->inertiaProps('stats');
    $dealCounts = $response->inertiaProps('deal_counts');
    $taskCounts = $response->inertiaProps('task_counts');

    expect($stats['deals']['total'])->toBe(2); // open + active
    expect($stats['tasks']['total'])->toBe(2); // pending + in_progress

    // Sidebar counts for Super Admin (Global)
    expect($dealCounts['open'])->toBe(2);
    expect($taskCounts['pending'])->toBe(2);
});

test('dashboard shows filtered stats and sidebar counts for staff', function () {
    $staff = User::factory()->create(['type' => UserType::ADMIN]);
    $otherUser = User::factory()->create(['type' => UserType::ADMIN]);

    // Deals: 1 assigned to staff (active), 1 unassigned (open), 1 assigned to other (active - should be excluded)
    Deal::factory()->create(['status' => 'active', 'assigned_to' => $staff->id, 'created_by' => $otherUser->id]);
    Deal::factory()->create(['status' => 'open', 'assigned_to' => null, 'created_by' => $otherUser->id]);
    Deal::factory()->create(['status' => 'active', 'assigned_to' => $otherUser->id, 'created_by' => $otherUser->id]);

    // Tasks: 1 assigned to staff (in_progress), 1 created by staff (pending), 1 assigned to other (pending - should be excluded)
    Task::factory()->create(['status' => 'in_progress', 'assigned_to' => $staff->id, 'created_by' => $otherUser->id]);
    Task::factory()->create(['status' => 'pending', 'created_by' => $staff->id, 'assigned_to' => $otherUser->id]);
    Task::factory()->create(['status' => 'pending', 'assigned_to' => $otherUser->id, 'created_by' => $otherUser->id]);

    $response = $this->actingAs($staff, 'admin')->get(route('admin.dashboard'));

    $response->assertStatus(200);
    $stats = $response->inertiaProps('stats');
    $dealCounts = $response->inertiaProps('deal_counts');
    $taskCounts = $response->inertiaProps('task_counts');

    expect($stats['deals']['total'])->toBe(2); // assigned + unassigned
    expect($stats['tasks']['total'])->toBe(2); // assigned + created_by

    // Sidebar counts for Staff (Filtered)
    expect($dealCounts['open'])->toBe(2);
    expect($taskCounts['pending'])->toBe(2);
});
