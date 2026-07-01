<?php

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('can login as both admin and customer simultaneously', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

    $admin = User::factory()->create(['type' => UserType::ADMIN]);
    $customer = User::factory()->create(['type' => UserType::CUSTOMER]);

    // Login as customer on web guard
    $this->post(route('login'), [
        'email' => $customer->email,
        'password' => 'password',
    ])->assertRedirect(route('customer.dashboard'));

    // Verify customer is logged in
    expect(Auth::guard('web')->check())->toBeTrue();
    expect(Auth::guard('web')->id())->toBe($customer->id);

    // Now login as admin on admin guard
    $this->post(route('admin.login'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    // Verify both are logged in simultaneously
    expect(Auth::guard('web')->check())->toBeTrue();
    expect(Auth::guard('web')->id())->toBe($customer->id);
    expect(Auth::guard('admin')->check())->toBeTrue();
    expect(Auth::guard('admin')->id())->toBe($admin->id);
});
