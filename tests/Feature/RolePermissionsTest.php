<?php

use App\Constants\Permissions as P;
use App\Enums\UserType;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

// Seed roles and permissions before each test
beforeEach(function () {
    seed(\Database\Seeders\CRMRoleSeeder::class);
});

// Helper to create user with specific role
function createUserWithRole(string $roleName): User
{
    $user = User::factory()->create([
        'type' => UserType::ADMIN,
    ]);

    $user->adminProfile()->create();

    $role = Role::where('name', $roleName)->first();
    if ($role) {
        $user->roles()->attach($role);
    }

    return $user->fresh(['roles']);
}

// Test: Staff list shows only organization staff (excludes customers)
test('staff list shows only organization staff and excludes customers', function () {
    // Create test staff user
    $staffUser = User::factory()->create([
        'type' => UserType::ADMIN,
        'email' => 'staff_test@example.com',
    ]);
    $staffUser->adminProfile()->create();

    // Create test customer
    $customer = User::factory()->create([
        'type' => UserType::CUSTOMER,
        'email' => 'customer_test@example.com',
    ]);
    $customer->customerProfile()->create();

    // Login as Super Admin
    $superAdmin = createUserWithRole('Super Admin');
    actingAs($superAdmin, 'admin');

    // Visit staff list page
    $response = get(route('admin.users.index'));

    $response->assertOk();

    // Verify response contains only staff users
    $users = $response->viewData('users');

    // Check that all users are staff (ADMIN or SUPER_ADMIN type)
    foreach ($users as $user) {
        expect($user->type)->toBeIn([UserType::ADMIN, UserType::SUPER_ADMIN]);
        expect($user->type)->not->toBe(UserType::CUSTOMER);
    }
});

// Test: Super Admin has full access to all CRM modules
test('super admin can access all CRM modules', function () {
    $superAdmin = createUserWithRole('Super Admin');

    // Check all permissions
    expect($superAdmin->hasPermission(P::CUSTOMER_VIEW))->toBeTrue();
    expect($superAdmin->hasPermission(P::CUSTOMER_CREATE))->toBeTrue();
    expect($superAdmin->hasPermission(P::CUSTOMER_UPDATE))->toBeTrue();
    expect($superAdmin->hasPermission(P::CUSTOMER_DELETE))->toBeTrue();

    expect($superAdmin->hasPermission(P::LEAD_VIEW))->toBeTrue();
    expect($superAdmin->hasPermission(P::LEAD_CREATE))->toBeTrue();
    expect($superAdmin->hasPermission(P::LEAD_UPDATE))->toBeTrue();
    expect($superAdmin->hasPermission(P::LEAD_DELETE))->toBeTrue();

    expect($superAdmin->hasPermission(P::DEAL_VIEW))->toBeTrue();
    expect($superAdmin->hasPermission(P::DEAL_CREATE))->toBeTrue();
    expect($superAdmin->hasPermission(P::DEAL_UPDATE))->toBeTrue();
    expect($superAdmin->hasPermission(P::DEAL_DELETE))->toBeTrue();

    expect($superAdmin->hasPermission(P::TASK_VIEW))->toBeTrue();
    expect($superAdmin->hasPermission(P::TASK_CREATE))->toBeTrue();
    expect($superAdmin->hasPermission(P::TASK_UPDATE))->toBeTrue();
    expect($superAdmin->hasPermission(P::TASK_DELETE))->toBeTrue();
});

// Test: Sales Executive has limited permissions
test('sales executive has view and update permissions only', function () {
    $salesExec = createUserWithRole('Sales Executive');

    expect($salesExec->hasPermission(P::CUSTOMER_VIEW))->toBeTrue();
    expect($salesExec->hasPermission(P::CUSTOMER_CREATE))->toBeTrue();

    expect($salesExec->hasPermission(P::LEAD_VIEW))->toBeTrue();
    expect($salesExec->hasPermission(P::LEAD_UPDATE))->toBeTrue();
    expect($salesExec->hasPermission(P::LEAD_CREATE))->toBeFalse();
    expect($salesExec->hasPermission(P::LEAD_DELETE))->toBeFalse();

    expect($salesExec->hasPermission(P::DEAL_VIEW))->toBeTrue();
    expect($salesExec->hasPermission(P::DEAL_UPDATE))->toBeTrue();
    expect($salesExec->hasPermission(P::DEAL_CREATE))->toBeFalse();
    expect($salesExec->hasPermission(P::DEAL_DELETE))->toBeFalse();
});

// Test: Finance has read-only access
test('accounts finance has read-only access to all modules', function () {
    $finance = createUserWithRole('Accounts / Finance');

    expect($finance->hasPermission(P::CUSTOMER_VIEW))->toBeTrue();
    expect($finance->hasPermission(P::CUSTOMER_CREATE))->toBeFalse();
    expect($finance->hasPermission(P::CUSTOMER_UPDATE))->toBeFalse();
    expect($finance->hasPermission(P::CUSTOMER_DELETE))->toBeFalse();

    expect($finance->hasPermission(P::LEAD_VIEW))->toBeTrue();
    expect($finance->hasPermission(P::LEAD_CREATE))->toBeFalse();

    expect($finance->hasPermission(P::DEAL_VIEW))->toBeTrue();
    expect($finance->hasPermission(P::DEAL_CREATE))->toBeFalse();

    expect($finance->hasPermission(P::TASK_VIEW))->toBeTrue();
    expect($finance->hasPermission(P::TASK_CREATE))->toBeFalse();
});

// Test: Business Development Manager has full lead management
test('bdm has full lead management permissions', function () {
    $bdm = createUserWithRole('Business Development Manager');

    expect($bdm->hasPermission(P::LEAD_VIEW))->toBeTrue();
    expect($bdm->hasPermission(P::LEAD_CREATE))->toBeTrue();
    expect($bdm->hasPermission(P::LEAD_UPDATE))->toBeTrue();
    expect($bdm->hasPermission(P::LEAD_DELETE))->toBeTrue();

    // But limited deal permissions
    expect($bdm->hasPermission(P::DEAL_VIEW))->toBeTrue();
    expect($bdm->hasPermission(P::DEAL_CREATE))->toBeTrue();
    expect($bdm->hasPermission(P::DEAL_UPDATE))->toBeFalse();
    expect($bdm->hasPermission(P::DEAL_DELETE))->toBeFalse();
});

// Test: Customer Success has full task management
test('customer success has full task management permissions', function () {
    $support = createUserWithRole('Customer Success / Support');

    expect($support->hasPermission(P::TASK_VIEW))->toBeTrue();
    expect($support->hasPermission(P::TASK_CREATE))->toBeTrue();
    expect($support->hasPermission(P::TASK_UPDATE))->toBeTrue();
    expect($support->hasPermission(P::TASK_DELETE))->toBeTrue();

    // Can update customers
    expect($support->hasPermission(P::CUSTOMER_VIEW))->toBeTrue();
    expect($support->hasPermission(P::CUSTOMER_UPDATE))->toBeTrue();

    // But cannot manage deals
    expect($support->hasPermission(P::DEAL_VIEW))->toBeTrue();
    expect($support->hasPermission(P::DEAL_CREATE))->toBeFalse();
});

// Test: Marketing Manager has full lead and task management
test('marketing manager has full lead and task permissions', function () {
    $marketing = createUserWithRole('Marketing Manager');

    expect($marketing->hasPermission(P::LEAD_VIEW))->toBeTrue();
    expect($marketing->hasPermission(P::LEAD_CREATE))->toBeTrue();
    expect($marketing->hasPermission(P::LEAD_UPDATE))->toBeTrue();
    expect($marketing->hasPermission(P::LEAD_DELETE))->toBeTrue();

    expect($marketing->hasPermission(P::TASK_VIEW))->toBeTrue();
    expect($marketing->hasPermission(P::TASK_CREATE))->toBeTrue();
    expect($marketing->hasPermission(P::TASK_UPDATE))->toBeTrue();
    expect($marketing->hasPermission(P::TASK_DELETE))->toBeTrue();
});

// Test: HR Manager has limited access
test('hr manager has very limited permissions', function () {
    $hr = createUserWithRole('HR Manager');

    expect($hr->hasPermission(P::TASK_VIEW))->toBeTrue();
    expect($hr->hasPermission(P::TASK_CREATE))->toBeFalse();

    expect($hr->hasPermission(P::CUSTOMER_VIEW))->toBeFalse();
    expect($hr->hasPermission(P::LEAD_VIEW))->toBeFalse();
    expect($hr->hasPermission(P::DEAL_VIEW))->toBeFalse();

    expect($hr->hasPermission(P::MEMBER_DIRECTORY))->toBeTrue();
});

// Test: Viewer/Management has read-only access
test('viewer management has read-only access to all modules', function () {
    $viewer = createUserWithRole('Viewer / Management');

    expect($viewer->hasPermission(P::CUSTOMER_VIEW))->toBeTrue();
    expect($viewer->hasPermission(P::LEAD_VIEW))->toBeTrue();
    expect($viewer->hasPermission(P::DEAL_VIEW))->toBeTrue();
    expect($viewer->hasPermission(P::TASK_VIEW))->toBeTrue();

    expect($viewer->hasPermission(P::CUSTOMER_CREATE))->toBeFalse();
    expect($viewer->hasPermission(P::LEAD_CREATE))->toBeFalse();
    expect($viewer->hasPermission(P::DEAL_CREATE))->toBeFalse();
    expect($viewer->hasPermission(P::TASK_CREATE))->toBeFalse();
});

// Test: Sales Manager has full CRM access
test('sales manager has full crm permissions', function () {
    $salesManager = createUserWithRole('Sales Manager');

    // Full customer access
    expect($salesManager->hasPermission(P::CUSTOMER_VIEW))->toBeTrue();
    expect($salesManager->hasPermission(P::CUSTOMER_CREATE))->toBeTrue();
    expect($salesManager->hasPermission(P::CUSTOMER_UPDATE))->toBeTrue();
    expect($salesManager->hasPermission(P::CUSTOMER_DELETE))->toBeTrue();

    // Full lead access
    expect($salesManager->hasPermission(P::LEAD_VIEW))->toBeTrue();
    expect($salesManager->hasPermission(P::LEAD_CREATE))->toBeTrue();
    expect($salesManager->hasPermission(P::LEAD_UPDATE))->toBeTrue();
    expect($salesManager->hasPermission(P::LEAD_DELETE))->toBeTrue();

    // Full deal access
    expect($salesManager->hasPermission(P::DEAL_VIEW))->toBeTrue();
    expect($salesManager->hasPermission(P::DEAL_CREATE))->toBeTrue();
    expect($salesManager->hasPermission(P::DEAL_UPDATE))->toBeTrue();
    expect($salesManager->hasPermission(P::DEAL_DELETE))->toBeTrue();

    // Full task access
    expect($salesManager->hasPermission(P::TASK_VIEW))->toBeTrue();
    expect($salesManager->hasPermission(P::TASK_CREATE))->toBeTrue();
    expect($salesManager->hasPermission(P::TASK_UPDATE))->toBeTrue();
    expect($salesManager->hasPermission(P::TASK_DELETE))->toBeTrue();
});
