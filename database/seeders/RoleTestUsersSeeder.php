<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleTestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define test users for each role
        $testUsers = [
            [
                'name' => 'Alice Admin',
                'email' => 'superadmin@test.com',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Bob Manager',
                'email' => 'salesmanager@test.com',
                'role' => 'Sales Manager',
            ],
            [
                'name' => 'Charlie Executive',
                'email' => 'salesexec@test.com',
                'role' => 'Sales Executive',
            ],
            [
                'name' => 'Diana BDM',
                'email' => 'bdm@test.com',
                'role' => 'Business Development Manager',
            ],
            [
                'name' => 'Eve Consultant',
                'email' => 'presales@test.com',
                'role' => 'Pre-Sales / Consultant',
            ],
            [
                'name' => 'Frank Finance',
                'email' => 'finance@test.com',
                'role' => 'Accounts / Finance',
            ],
            [
                'name' => 'Grace Support',
                'email' => 'support@test.com',
                'role' => 'Customer Success / Support',
            ],
            [
                'name' => 'Henry Marketing',
                'email' => 'marketing@test.com',
                'role' => 'Marketing Manager',
            ],
            [
                'name' => 'Iris Operations',
                'email' => 'operations@test.com',
                'role' => 'Operations / Project Manager',
            ],
            [
                'name' => 'Jack Legal',
                'email' => 'legal@test.com',
                'role' => 'Legal / Compliance',
            ],
            [
                'name' => 'Karen HR',
                'email' => 'hr@test.com',
                'role' => 'HR Manager',
            ],
            [
                'name' => 'Leo Viewer',
                'email' => 'viewer@test.com',
                'role' => 'Viewer / Management',
            ],
        ];

        // Password for all test users
        $password = Hash::make('password');

        foreach ($testUsers as $userData) {
            // Create user
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $password,
                    'type' => UserType::ADMIN, // All staff are ADMIN type
                ]
            );

            // Create admin profile
            if (! $user->adminProfile) {
                $user->adminProfile()->create();
            }

            // Find and attach role
            $role = Role::where('name', $userData['role'])->first();
            if ($role && ! $user->roles->contains($role->id)) {
                $user->roles()->attach($role);
            }
        }

        // Also create a test customer to verify they don't appear in staff list
        $customer = User::firstOrCreate(
            ['email' => 'customer@test.com'],
            [
                'name' => 'Test Customer',
                'password' => $password,
                'type' => UserType::CUSTOMER,
            ]
        );

        if (! $customer->customerProfile) {
            $customer->customerProfile()->create();
        }

        $this->command->info('✓ Created 12 test staff users (one per role)');
        $this->command->info('✓ Created 1 test customer (to verify filtering)');
        $this->command->info('📧 All passwords: password');
    }
}
