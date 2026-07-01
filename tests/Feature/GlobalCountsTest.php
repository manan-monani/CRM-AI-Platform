<?php

use App\Enums\UserType;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Deal;
use App\Models\Reminder;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

test('super admin sees global counts while regular admin sees assigned counts', function () {
    // Create a Super Admin
    $superAdmin = User::factory()->create(['type' => UserType::SUPER_ADMIN]);

    // Create a regular Admin
    $admin = User::factory()->create(['type' => UserType::ADMIN]);

    // Create another Admin
    $otherAdmin = User::factory()->create(['type' => UserType::ADMIN]);

    // Create some data
    // 2 tasks for $admin, 1 for $otherAdmin. Total 3.
    Task::factory()->count(2)->create(['assigned_to' => $admin->id, 'status' => 'pending']);
    Task::factory()->create(['assigned_to' => $otherAdmin->id, 'status' => 'pending']);

    // 1 deal for $admin, 2 for $otherAdmin. Total 3.
    Deal::factory()->create(['assigned_to' => $admin->id, 'status' => 'open']);
    Deal::factory()->count(2)->create(['assigned_to' => $otherAdmin->id, 'status' => 'open']);

    // 1 reminder for $admin, 1 for $otherAdmin. Total 2.
    Reminder::factory()->create(['assigned_to' => $admin->id, 'status' => 'pending']);
    Reminder::factory()->create(['assigned_to' => $otherAdmin->id, 'status' => 'pending']);

    $middleware = new HandleInertiaRequests;

    // Verify for Regular Admin
    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $admin);

    $sharedData = $middleware->share($request);

    expect($sharedData['task_counts']()['pending'])->toBe(2);
    expect($sharedData['deal_counts']()['open'])->toBe(1);
    expect($sharedData['reminder_counts']()['pending'])->toBe(1);

    // Verify for Super Admin
    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $superAdmin);

    $sharedData = $middleware->share($request);

    expect($sharedData['task_counts']()['pending'])->toBe(3);
    expect($sharedData['deal_counts']()['open'])->toBe(3);
    expect($sharedData['reminder_counts']()['pending'])->toBe(2);
});
