<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'type' => UserType::SUPER_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        if ($superAdmin->type !== UserType::SUPER_ADMIN) {
            $superAdmin->update(['type' => UserType::SUPER_ADMIN]);
        }

        if (! $superAdmin->adminProfile) {
            $superAdmin->adminProfile()->create();
        }

        // 2. Manager (Admin with Role)
        $managerRole = Role::where('slug', 'manager')->first();
        if ($managerRole) {
            $manager = User::updateOrCreate(
                ['email' => 'manager@admin.com'],
                [
                    'name' => 'Manager User',
                    'password' => Hash::make('password'),
                    'type' => UserType::ADMIN,
                    'email_verified_at' => now(),
                ]
            );

            if ($manager->type !== UserType::ADMIN) {
                $manager->update(['type' => UserType::ADMIN]);
            }

            if (! $manager->adminProfile) {
                $manager->adminProfile()->create();
            }

            if (! $manager->roles()->where('role_id', $managerRole->id)->exists()) {
                $manager->roles()->attach($managerRole);
            }
        }
    }
}
