<?php

namespace Tests\Feature;

use App\Enums\SalesRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles if they don't exist (seeders might not run in all test configurations)
        Role::firstOrCreate(['name' => 'Manager'], ['slug' => 'manager']);
        Role::firstOrCreate(['name' => 'Sales Rep'], ['slug' => 'sales-rep']);
    }

    public function test_assigning_manager_role_updates_sales_role_column()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $managerRole = Role::where('slug', 'manager')->first();

        // Simulate update via UserService
        $data = [
            'name' => 'New Manager',
            'email' => 'newmanager@example.com',
            'roles' => [$managerRole->id],
        ];

        // Access UserService via container to test the logic
        $userService = app(\App\Services\UserService::class);
        $userService->update($admin, $data);

        $this->assertEquals(SalesRole::MANAGER, $admin->fresh()->sales_role);
        $this->assertTrue($admin->fresh()->isSalesManager());
    }

    public function test_assigning_sales_rep_role_updates_sales_role_column()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $repRole = Role::where('slug', 'sales-rep')->first();

        $data = [
            'name' => 'New Rep',
            'email' => 'newrep@example.com',
            'roles' => [$repRole->id],
        ];

        $userService = app(\App\Services\UserService::class);
        $userService->update($admin, $data);

        $this->assertEquals(SalesRole::REP, $admin->fresh()->sales_role);
        $this->assertTrue($admin->fresh()->isSalesRep());
    }

    public function test_creating_user_with_sales_rep_role_sets_column()
    {
        $repRole = Role::where('slug', 'sales-rep')->first();

        $data = [
            'name' => 'Created Rep',
            'email' => 'createdrep@example.com',
            'password' => 'password',
            'type' => 'admin',
            'roles' => [$repRole->id],
        ];

        $userService = app(\App\Services\UserService::class);
        $user = $userService->create($data);

        $this->assertEquals(SalesRole::REP, $user->fresh()->sales_role);
        $this->assertTrue($user->fresh()->isSalesRep());
    }

    public function test_removing_role_clears_sales_role_column()
    {
        $admin = User::factory()->create(['type' => 'admin', 'sales_role' => SalesRole::REP]);
        $repRole = Role::where('slug', 'sales-rep')->first();
        $admin->roles()->attach($repRole);

        // Update with NO roles
        $data = [
            'name' => 'No Role User',
            'roles' => [],
        ];

        $userService = app(\App\Services\UserService::class);
        $userService->update($admin, $data);

        $this->assertNull($admin->fresh()->sales_role);
    }
}
