<?php

namespace Database\Seeders;

use App\Constants\Permissions as P;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class CRMRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create all granular permissions first
        $permissions = $this->createPermissions();

        // Define roles with their permission mappings
        $roles = $this->getRoleDefinitions();

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
            ['name' => $roleData['name']],
            [
                'slug' => \Illuminate\Support\Str::slug($roleData['name']),
                'description' => $roleData['description'],
            ]
            );

            // Sync permissions for this role
            $permissionIds = [];
            foreach ($roleData['permissions'] as $permissionName) {
                if (isset($permissions[$permissionName])) {
                    $permissionIds[] = $permissions[$permissionName]->id;
                }
            }

            $role->permissions()->sync($permissionIds);
        }
    }

    /**
     * Create all granular permissions
     */
    private function createPermissions(): array
    {
        $permissionConstants = [
            // Dashboard & Activity
            P::DASHBOARD_VIEW,
            P::ACTIVITY_LOG,

            // Module-level permissions (for sidebar menu access)
            P::CUSTOMER_MANAGEMENT,
            P::LEAD_MANAGEMENT,
            P::DEAL_MANAGEMENT,
            P::TASK_MANAGEMENT,

            // Customer CRUD
            P::CUSTOMER_VIEW,
            P::CUSTOMER_CREATE,
            P::CUSTOMER_UPDATE,
            P::CUSTOMER_DELETE,

            // Lead CRUD
            P::LEAD_VIEW,
            P::LEAD_CREATE,
            P::LEAD_UPDATE,
            P::LEAD_DELETE,

            // Deal CRUD
            P::DEAL_VIEW,
            P::DEAL_CREATE,
            P::DEAL_UPDATE,
            P::DEAL_DELETE,

            // Task CRUD
            P::TASK_VIEW,
            P::TASK_CREATE,
            P::TASK_UPDATE,
            P::TASK_DELETE,

            // Account & Settings
            P::MEMBER_DIRECTORY,
            P::ACCESS_ROLES,
            P::BUSINESS_BRANDING,
            P::BUSINESS_LOGIC,
            P::LEGAL_MANAGEMENT,
        ];

        $permissionMap = [];
        foreach ($permissionConstants as $permissionName) {
            $permissionMap[$permissionName] = Permission::firstOrCreate(['name' => $permissionName]);
        }

        return $permissionMap;
    }

    /**
     * Get role definitions with their permissions
     */
    private function getRoleDefinitions(): array
    {
        return [
            [
                'name' => 'Super Admin',
                'description' => 'Complete system access with all permissions',
                'permissions' => [
                    // All dashboard & activity
                    P::DASHBOARD_VIEW, P::ACTIVITY_LOG,
                    // All module access
                    P::CUSTOMER_MANAGEMENT, P::LEAD_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    // All CRM CRUD
                    P::CUSTOMER_VIEW, P::CUSTOMER_CREATE, P::CUSTOMER_UPDATE, P::CUSTOMER_DELETE,
                    P::LEAD_VIEW, P::LEAD_CREATE, P::LEAD_UPDATE, P::LEAD_DELETE,
                    P::DEAL_VIEW, P::DEAL_CREATE, P::DEAL_UPDATE, P::DEAL_DELETE,
                    P::TASK_VIEW, P::TASK_CREATE, P::TASK_UPDATE, P::TASK_DELETE,
                    // All settings
                    P::MEMBER_DIRECTORY, P::ACCESS_ROLES, P::BUSINESS_BRANDING, P::BUSINESS_LOGIC, P::LEGAL_MANAGEMENT,
                ],
            ],
            [
                'name' => 'Sales Manager',
                'description' => 'Manages sales team with full CRM access',
                'permissions' => [
                    P::DASHBOARD_VIEW, P::ACTIVITY_LOG,
                    P::CUSTOMER_MANAGEMENT, P::LEAD_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    P::CUSTOMER_VIEW, P::CUSTOMER_CREATE, P::CUSTOMER_UPDATE, P::CUSTOMER_DELETE,
                    P::LEAD_VIEW, P::LEAD_CREATE, P::LEAD_UPDATE, P::LEAD_DELETE,
                    P::DEAL_VIEW, P::DEAL_CREATE, P::DEAL_UPDATE, P::DEAL_DELETE,
                    P::TASK_VIEW, P::TASK_CREATE, P::TASK_UPDATE, P::TASK_DELETE,
                    P::MEMBER_DIRECTORY, // Can view team members
                ],
            ],
            [
                'name' => 'Sales Executive',
                'description' => 'Front-line sales, manages own deals and leads',
                'permissions' => [
                    P::DASHBOARD_VIEW,
                    P::CUSTOMER_MANAGEMENT, P::LEAD_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    P::CUSTOMER_VIEW, P::CUSTOMER_CREATE,
                    P::LEAD_VIEW, P::LEAD_UPDATE, // Can update own leads
                    P::DEAL_VIEW, P::DEAL_UPDATE, // Can update own deals
                    P::TASK_VIEW, P::TASK_CREATE, P::TASK_UPDATE, // Can manage own tasks
                ],
            ],
            [
                'name' => 'Business Development Manager',
                'description' => 'Focuses on lead generation and new business',
                'permissions' => [
                    P::DASHBOARD_VIEW,
                    P::CUSTOMER_MANAGEMENT, P::LEAD_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    P::CUSTOMER_VIEW, P::CUSTOMER_CREATE,
                    P::LEAD_VIEW, P::LEAD_CREATE, P::LEAD_UPDATE, P::LEAD_DELETE, // Full lead management
                    P::DEAL_VIEW, P::DEAL_CREATE,
                    P::TASK_VIEW, P::TASK_CREATE,
                ],
            ],
            [
                'name' => 'Pre-Sales / Consultant',
                'description' => 'Supports sales with technical expertise',
                'permissions' => [
                    P::DASHBOARD_VIEW,
                    P::CUSTOMER_MANAGEMENT, P::LEAD_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    P::CUSTOMER_VIEW,
                    P::LEAD_VIEW, P::LEAD_CREATE,
                    P::DEAL_VIEW,
                    P::TASK_VIEW, P::TASK_CREATE,
                ],
            ],
            [
                'name' => 'Accounts / Finance',
                'description' => 'Read-only access for financial reporting',
                'permissions' => [
                    P::DASHBOARD_VIEW, P::ACTIVITY_LOG,
                    P::CUSTOMER_MANAGEMENT, P::LEAD_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    P::CUSTOMER_VIEW,
                    P::LEAD_VIEW,
                    P::DEAL_VIEW,
                    P::TASK_VIEW,
                ],
            ],
            [
                'name' => 'Customer Success / Support',
                'description' => 'Manages customer relationships and support tasks',
                'permissions' => [
                    P::DASHBOARD_VIEW,
                    P::CUSTOMER_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    P::CUSTOMER_VIEW, P::CUSTOMER_UPDATE,
                    P::DEAL_VIEW,
                    P::TASK_VIEW, P::TASK_CREATE, P::TASK_UPDATE, P::TASK_DELETE, // Full task management
                ],
            ],
            [
                'name' => 'Marketing Manager',
                'description' => 'Manages campaigns and lead nurturing',
                'permissions' => [
                    P::DASHBOARD_VIEW,
                    P::CUSTOMER_MANAGEMENT, P::LEAD_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    P::CUSTOMER_VIEW,
                    P::LEAD_VIEW, P::LEAD_CREATE, P::LEAD_UPDATE, P::LEAD_DELETE, // Full lead management
                    P::DEAL_VIEW,
                    P::TASK_VIEW, P::TASK_CREATE, P::TASK_UPDATE, P::TASK_DELETE, // Full task management
                ],
            ],
            [
                'name' => 'Operations / Project Manager',
                'description' => 'Manages task execution and project delivery',
                'permissions' => [
                    P::DASHBOARD_VIEW,
                    P::CUSTOMER_MANAGEMENT, P::LEAD_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    P::CUSTOMER_VIEW,
                    P::LEAD_VIEW,
                    P::DEAL_VIEW,
                    P::TASK_VIEW, P::TASK_CREATE, P::TASK_UPDATE, P::TASK_DELETE, // Full task management
                ],
            ],
            [
                'name' => 'Legal / Compliance',
                'description' => 'Read-only access for compliance auditing',
                'permissions' => [
                    P::DASHBOARD_VIEW, P::ACTIVITY_LOG,
                    P::CUSTOMER_MANAGEMENT, P::LEAD_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    P::CUSTOMER_VIEW,
                    P::LEAD_VIEW,
                    P::DEAL_VIEW,
                    P::TASK_VIEW,
                ],
            ],
            [
                'name' => 'HR Manager',
                'description' => 'Limited access for team visibility and workload monitoring',
                'permissions' => [
                    P::DASHBOARD_VIEW,
                    P::TASK_MANAGEMENT,
                    P::TASK_VIEW, // Can view team workload
                    P::MEMBER_DIRECTORY, // Can view staff
                ],
            ],
            [
                'name' => 'Viewer / Management',
                'description' => 'Read-only oversight access for executives',
                'permissions' => [
                    P::DASHBOARD_VIEW, P::ACTIVITY_LOG,
                    P::CUSTOMER_MANAGEMENT, P::LEAD_MANAGEMENT, P::DEAL_MANAGEMENT, P::TASK_MANAGEMENT,
                    P::CUSTOMER_VIEW,
                    P::LEAD_VIEW,
                    P::DEAL_VIEW,
                    P::TASK_VIEW,
                ],
            ],
        ];
    }
}