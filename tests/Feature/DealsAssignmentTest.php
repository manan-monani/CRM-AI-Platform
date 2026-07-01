<?php

namespace Tests\Feature;

use App\Constants\Permissions;
use App\Enums\SalesRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DealsAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed permissions
        Permission::firstOrCreate(['name' => Permissions::DEAL_MANAGEMENT]);
        $role = Role::firstOrCreate(['name' => 'Admin'], ['slug' => 'admin']);
        $role->permissions()->sync(Permission::all());

        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_create_deal_page_filters_users_by_sales_role()
    {
        $admin = User::factory()->create(['type' => 'admin', 'sales_role' => SalesRole::ADMIN]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first());

        // Create users with different sales roles
        $rep = User::factory()->create(['name' => 'Sales Rep', 'sales_role' => SalesRole::REP, 'type' => 'admin']);
        $manager = User::factory()->create(['name' => 'Sales Manager', 'sales_role' => SalesRole::MANAGER, 'type' => 'admin']);
        $regularAdmin = User::factory()->create(['name' => 'Admin User', 'sales_role' => SalesRole::ADMIN, 'type' => 'admin']);
        $otherUser = User::factory()->create(['name' => 'Other User', 'sales_role' => null, 'type' => 'admin']); // Should be excluded

        $this->actingAs($admin, 'admin')
            ->get(route('admin.deals.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Deals/Create')
                ->has('users', 4) // admin (me), rep, manager, regularAdmin
                ->where('users.0.id', $admin->id) // Current user is admin, likely included if sales_role synced or separate check?
                // Wait, factory create doesn't auto-sync sales_role unless we used the service.
                // So $admin has null sales_role unless we set it.
                // The filters are: admin, manager, rep.
            );

        // Let's refine the test data to be explicit
    }

    public function test_create_deal_page_only_shows_sales_roles()
    {
        $admin = User::factory()->create(['type' => 'admin', 'sales_role' => SalesRole::ADMIN]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first());

        $rep = User::factory()->create(['type' => 'admin', 'sales_role' => SalesRole::REP]);
        $manager = User::factory()->create(['type' => 'admin', 'sales_role' => SalesRole::MANAGER]);
        $regularUser = User::factory()->create(['type' => 'admin', 'sales_role' => null]);
        $customer = User::factory()->create(['type' => 'customer']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.deals.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Deals/Create')
                ->has('users', 3) // admin, rep, manager
                ->where('users', function ($users) use ($rep, $manager, $admin) {
                    $ids = collect($users)->pluck('id');

                    return $ids->contains($rep->id) &&
                    $ids->contains($manager->id) &&
                    $ids->contains($admin->id);
                }
                )
            );
    }
}
