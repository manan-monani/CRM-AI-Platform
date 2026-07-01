<?php

use App\Models\User;
use App\Enums\UserType;

test('customer can login and redirect to dashboard', function () {
    $user = User::factory()->create([
        'type' => UserType::CUSTOMER,
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('customer.dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('admin cannot login via guest login page', function () {
    $admin = User::factory()->create([
        'type' => UserType::ADMIN,
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Admins must login via the Admin Portal.');
    $this->assertGuest();
});

test('guest cannot access customer dashboard', function () {
    $response = $this->get(route('customer.dashboard'));

    $response->assertRedirect(route('login'));
});

test('customer can view dashboard', function () {
    $user = User::factory()->create(['type' => UserType::CUSTOMER]);

    $response = $this->actingAs($user)
        ->get(route('customer.dashboard'));

    $response->assertStatus(200);
});