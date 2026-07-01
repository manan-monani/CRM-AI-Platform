<?php

use App\Models\User;
use App\Models\Lead;
use App\Models\Role;
use App\Models\Permission;
use App\Enums\UserType;
use App\Constants\Permissions;
use Illuminate\Support\Facades\Gate;

test('admin can convert lead to customer', function () {
    // 1. Setup Permissions and Roles
    $permission = Permission::firstOrCreate(['name' => Permissions::LEAD_MANAGEMENT]);
    $role = Role::firstOrCreate(['name' => 'Admin Test', 'slug' => 'admin-test']);
    $role->permissions()->syncWithoutDetaching([$permission->id]);

    $admin = User::factory()->create(['type' => UserType::ADMIN]);
    $admin->roles()->attach($role);

    // FIX: Manually define gate
    Gate::define(Permissions::LEAD_MANAGEMENT, function ($user) {
            return $user->hasPermission(Permissions::LEAD_MANAGEMENT);
        }
        );

        // 2. Create Lead
        $lead = Lead::factory()->create([
            'status' => 'qualified',
            'email' => 'newcustomer@example.com',
            'name' => 'Future Customer',
        ]);

        // 3. Act
        $response = $this->actingAs($admin)
            ->post(route('admin.leads.convert', $lead));

        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('error');

        $this->assertDatabaseHas('users', [
            'email' => 'newcustomer@example.com',
            'type' => UserType::CUSTOMER,
            'name' => 'Future Customer',
        ]);

        $customer = User::where('email', 'newcustomer@example.com')->first();
        $response->assertRedirect(route('admin.customers.show', $customer));

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'converted',
            'converted_to_customer_id' => $customer->id,
        ]);
    });

test('cannot convert already converted lead', function () {
    $permission = Permission::firstOrCreate(['name' => Permissions::LEAD_MANAGEMENT]);
    $role = Role::firstOrCreate(['name' => 'Admin Test', 'slug' => 'admin-test']);
    $role->permissions()->syncWithoutDetaching([$permission->id]);

    $admin = User::factory()->create(['type' => UserType::ADMIN]);
    $admin->roles()->attach($role);

    Gate::define(Permissions::LEAD_MANAGEMENT, function ($user) {
            return $user->hasPermission(Permissions::LEAD_MANAGEMENT);
        }
        );

        // Create a dummy customer to link to
        $existingCustomer = User::factory()->create(['type' => UserType::CUSTOMER]);

        // FIX: Set converted_to_customer_id to satisfy isConverted() check
        $lead = Lead::factory()->create([
            'status' => 'converted',
            'converted_to_customer_id' => $existingCustomer->id
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.leads.convert', $lead));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });