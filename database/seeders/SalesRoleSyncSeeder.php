<?php

namespace Database\Seeders;

use App\Enums\SalesRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalesRoleSyncSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::with('roles')->get();

        foreach ($users as $user) {
            $role = $user->roles->first();
            $salesRole = null;

            if ($role) {
                $roleSlug = Str::slug($role->name);
                if (in_array($roleSlug, ['manager', 'sales-manager'])) {
                    $salesRole = SalesRole::MANAGER;
                } elseif (in_array($roleSlug, ['rep', 'sales-rep', 'sales-representative'])) {
                    $salesRole = SalesRole::REP;
                } elseif (in_array($roleSlug, ['admin', 'sales-admin'])) {
                    $salesRole = SalesRole::ADMIN;
                }
            }

            // Fallback for Super Admin
            if ($user->type === \App\Enums\UserType::SUPER_ADMIN) {
                $salesRole = SalesRole::ADMIN;
            }

            if ($user->sales_role !== $salesRole) {
                $user->forceFill(['sales_role' => $salesRole])->save();
                $this->command->info("Synced sales_role for {$user->email}: ".($salesRole ? $salesRole->value : 'null'));
            }
        }
    }
}
