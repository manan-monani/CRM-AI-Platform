<?php

use App\Enums\UserType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a Super Admin (the target)
    $this->superAdmin = User::factory()->create([
        'type' => UserType::SUPER_ADMIN,
        'name' => 'Target Super Admin',
    ]);

    // Create another Super Admin to perform actions (to bypass Gate checks)
    $this->actingSuperAdmin = User::factory()->create([
        'type' => UserType::SUPER_ADMIN,
        'name' => 'Acting Super Admin',
    ]);

    // Ensure Super Admin role exists and assign to target
    $this->superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
    $this->superAdmin->roles()->attach($this->superAdminRole);
});

test('super admin has full power and bypasses permissions', function () {
    expect($this->superAdmin->hasPermission('some-non-existent-permission'))->toBeTrue();
    expect($this->superAdmin->hasRole('some-non-existent-role'))->toBeTrue();
});

test('super admin cannot be deleted', function () {
    $this->actingAs($this->actingSuperAdmin, 'admin');

    $response = $this->delete(route('admin.users.destroy', $this->superAdmin));

    $response->assertSessionHas('error', 'Super Admin users cannot be deleted.');
    $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
});

test('super admin roles cannot be changed', function () {
    $this->actingAs($this->actingSuperAdmin, 'admin');

    // Try to change roles to something else
    $newRole = Role::factory()->create(['name' => 'Manager']);

    $response = $this->put(route('admin.users.update', $this->superAdmin), [
        'name' => 'Updated Name',
        'email' => $this->superAdmin->email,
        'role_id' => $newRole->id,
    ]);

    $response->assertSessionHas('error', 'Super Admin roles cannot be changed.');

    // Name should NOT update if the request was blocked by the role check
    $this->assertDatabaseHas('users', [
        'id' => $this->superAdmin->id,
        'name' => 'Target Super Admin',
    ]);
});

test('super admin can update profile if role is not changed', function () {
    $this->actingAs($this->actingSuperAdmin, 'admin');

    $response = $this->put(route('admin.users.update', $this->superAdmin), [
        'name' => 'Updated Name',
        'email' => $this->superAdmin->email,
        'role_id' => $this->superAdminRole->id, // Same role
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success', 'User updated successfully.');

    $this->assertDatabaseHas('users', [
        'id' => $this->superAdmin->id,
        'name' => 'Updated Name',
    ]);
});

test('super admin role cannot be deleted', function () {
    $this->actingAs($this->actingSuperAdmin, 'admin');

    $response = $this->delete(route('admin.roles.destroy', $this->superAdminRole));

    $response->assertSessionHas('error', 'The Super Admin role cannot be deleted.');
    $this->assertDatabaseHas('roles', ['id' => $this->superAdminRole->id]);
});

test('super admin role cannot be modified', function () {
    $this->actingAs($this->actingSuperAdmin, 'admin');

    $response = $this->put(route('admin.roles.update', $this->superAdminRole), [
        'name' => 'Bricked Role',
        'permissions' => ['some-permission'],
    ]);

    $response->assertSessionHas('error', 'The Super Admin role cannot be modified.');
    $this->assertDatabaseHas('roles', [
        'id' => $this->superAdminRole->id,
        'name' => 'Super Admin',
    ]);
});
