<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all defined permissions from Constants
        $definedPermissions = \App\Constants\Permissions::getAll();
        $definedPermissionNames = [];
        $permissionIds = [];

        // Extract permission names from the structure
        foreach ($definedPermissions as $module) {
            if (isset($module['sub_modules'])) {
                foreach ($module['sub_modules'] as $key => $details) {
                    $definedPermissionNames[] = $key;
                }
            }
        }

        // Delete permissions that are NOT in the defined list
        Permission::whereNotIn('name', $definedPermissionNames)->delete();

        // Create or update defined permissions
        foreach ($definedPermissionNames as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $permissionIds[] = $permission->id;
        }

        // Create Manager Role
        $managerRole = Role::firstOrCreate(
            ['name' => 'Manager'],
            ['slug' => 'manager', 'description' => 'General Manager with full administrative access']
        );
        
        // Sync all defined permissions to Manager role
        $managerRole->permissions()->sync($permissionIds);
    }
}
