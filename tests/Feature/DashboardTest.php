<?php

use App\Enums\UserType;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'CRMRoleSeeder']);

    $this->superAdmin = User::factory()->create([
        'type' => UserType::SUPER_ADMIN,
    ]);

    $this->admin = User::factory()->create([
        'type' => UserType::ADMIN,
    ]);
});

test('super admin can access dashboard with monitoring', function () {
    $this->actingAs($this->superAdmin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->has('monitoring.tasks')
            ->has('monitoring.deals')
            ->has('stats.staff')
        );
});

test('regular admin sees only their data on dashboard', function () {
    // Create task for superAdmin overdue
    Task::factory()->create([
        'assigned_to' => $this->superAdmin->id,
        'due_date' => now()->subDay(),
        'status' => 'pending',
    ]);
    // Create task for admin overdue
    Task::factory()->create([
        'assigned_to' => $this->admin->id,
        'due_date' => now()->subDay(),
        'status' => 'pending',
    ]);

    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->has('monitoring.tasks.overdue', 1)
        );
});

test('super admin sees staff performance on dashboard', function () {
    $this->actingAs($this->superAdmin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertInertia(fn ($page) => $page
            ->has('stats.staff')
        );
});

test('super admin sees lead and customer data on dashboard', function () {
    // Create some leads and customers
    \App\Models\Lead::factory()->count(3)->create();
    \App\Models\Lead::factory()->create(['status' => 'new', 'created_at' => now()]);
    User::factory()->count(2)->create(['type' => UserType::CUSTOMER]);

    $this->actingAs($this->superAdmin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertInertia(fn ($page) => $page
            ->has('stats.leads')
            ->has('stats.customers')
            ->where('stats.leads.total', 4)
            ->where('stats.leads.new', 4)
            ->where('stats.customers.total', 2)
        );
});
