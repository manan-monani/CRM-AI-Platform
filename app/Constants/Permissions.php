<?php

namespace App\Constants;

class Permissions
{
    // Section: System
    public const SECTION_SYSTEM = 'system_section';

    public const DASHBOARD_VIEW = 'dashboard_view';

    public const ACTIVITY_LOG = 'activity_log';

    // Section: Account
    public const SECTION_ACCOUNT = 'account_section';

    public const MEMBER_DIRECTORY = 'member_directory';

    public const ACCESS_ROLES = 'access_roles';

    // Section: Business
    public const SECTION_BUSINESS = 'business_settings';

    public const BUSINESS_BRANDING = 'business_branding';

    public const LEAD_GENERATION_FORM_MANAGEMENT = 'lead_generation_form_management';

    public const LEAD_GENERATION_PAGE = 'lead_generation_page';

    public const HOME_PAGE_BUILDER = 'home_page_builder';

    public const LEGAL_MANAGEMENT = 'legal_management';

    public const BUSINESS_LOGIC = 'business_logic';

    // Section: CRM
    public const SECTION_CRM = 'crm_section';

    // CRM Module: Customers - Granular Permissions
    public const CUSTOMER_MANAGEMENT = 'customer_management';

    public const CUSTOMER_VIEW = 'customer_view';

    public const CUSTOMER_CREATE = 'customer_create';

    public const CUSTOMER_UPDATE = 'customer_update';

    public const CUSTOMER_DELETE = 'customer_delete';

    // CRM Module: Leads - Granular Permissions
    public const LEAD_MANAGEMENT = 'lead_management';

    public const LEAD_VIEW = 'lead_view';

    public const LEAD_CREATE = 'lead_create';

    public const LEAD_UPDATE = 'lead_update';

    public const LEAD_DELETE = 'lead_delete';

    // CRM Module: Deals - Granular Permissions
    public const DEAL_MANAGEMENT = 'deal_management';

    public const DEAL_VIEW = 'deal_view';

    public const DEAL_CREATE = 'deal_create';

    public const DEAL_UPDATE = 'deal_update';

    public const DEAL_DELETE = 'deal_delete';


    // CRM Module: Tasks - Granular Permissions
    public const TASK_MANAGEMENT = 'task_management';

    public const TASK_VIEW = 'task_view';

    public const TASK_CREATE = 'task_create';

    public const TASK_UPDATE = 'task_update';

    public const TASK_DELETE = 'task_delete';

    // CRM Module: Reminders - Granular Permissions
    public const REMINDER_MANAGEMENT = 'reminder_management';

    public const REMINDER_VIEW = 'reminder_view';

    public const REMINDER_CREATE = 'reminder_create';

    public const REMINDER_UPDATE = 'reminder_update';

    public const REMINDER_DELETE = 'reminder_delete';

    // Notifications
    public const NOTIFICATION_VIEW = 'notification_view';

    public static function getAll(): array
    {
        return [
            // Section 1: CRM Management
            self::SECTION_CRM => [
                'label' => 'crm_section',
                'icon' => 'LayoutDashboard',
                'sub_modules' => [
                    self::DASHBOARD_VIEW => [
                        'label' => 'dashboard',
                        'description' => 'View system health and metrics',
                        'route' => 'admin.dashboard',
                        'icon' => 'Activity',
                    ],
                    self::DEAL_MANAGEMENT => [
                        'label' => 'deal_management',
                        'description' => 'Manage deals and pipeline',
                        'route' => 'admin.deals.index',
                        'icon' => 'Briefcase',
                    ],
                    self::TASK_MANAGEMENT => [
                        'label' => 'task_management',
                        'description' => 'Manage tasks',
                        'route' => 'admin.tasks.index',
                        'icon' => 'CheckSquare',
                    ],
                    self::CUSTOMER_MANAGEMENT => [
                        'label' => 'customer_management',
                        'description' => 'Manage customer accounts and profiles',
                        'route' => 'admin.customers.index',
                        'icon' => 'UserCheck',
                    ],
                    self::LEAD_MANAGEMENT => [
                        'label' => 'lead_management',
                        'description' => 'Manage leads and convert to customers',
                        'route' => 'admin.leads.index',
                        'icon' => 'UserPlus',
                    ],
                    self::REMINDER_MANAGEMENT => [
                        'label' => 'reminder_management',
                        'description' => 'View and manage reminders',
                        'route' => 'admin.reminders.index',
                        'icon' => 'Bell',
                    ],
                ],
            ],
            // Section 2: Role & Staff Management
            self::SECTION_ACCOUNT => [
                'label' => 'account',
                'icon' => 'Users',
                'sub_modules' => [
                    self::ACCESS_ROLES => [
                        'label' => 'access_roles',
                        'description' => 'Configure roles and security boundaries',
                        'route' => 'admin.roles.index',
                        'icon' => 'ShieldCheck',
                    ],
                    self::MEMBER_DIRECTORY => [
                        'label' => 'member_directory',
                        'description' => 'Manage member identities and access levels',
                        'route' => 'admin.users.index',
                        'icon' => 'Users',
                    ],
                ],
            ],
            // Section 3: Settings
            self::SECTION_BUSINESS => [
                'label' => 'business_settings',
                'icon' => 'Settings',
                'sub_modules' => [
                    self::BUSINESS_BRANDING => [
                        'label' => 'business_branding',
                        'description' => 'Manage organization visual identity',
                        'route' => 'admin.business.branding',
                        'icon' => 'Palette',
                    ],
                    self::LEAD_GENERATION_FORM_MANAGEMENT => [
                        'label' => 'lead_generation_form',
                        'description' => 'Create and manage lead capture forms',
                        'route' => 'admin.lead-generation-form.index',
                        'icon' => 'FileText',
                    ],
                    self::LEAD_GENERATION_PAGE => [
                        'label' => 'lead_generation_page',
                        'description' => 'Customize Lead Generation Page',
                        'route' => 'admin.business.lead-generation-page',
                        'icon' => 'LayoutTemplate',
                    ],
                    self::HOME_PAGE_BUILDER => [
                        'label' => 'home_page_builder',
                        'description' => 'Dynamic Home Page Builder',
                        'route' => 'admin.business.home-page-builder',
                        'icon' => 'LayoutTemplate',
                    ],
                    self::BUSINESS_LOGIC => [
                        'label' => 'business_logic',
                        'description' => 'Manage business settings (Currency, Timezone, Country)',
                        'route' => 'admin.business.settings.index',
                        'icon' => 'Settings',
                    ],
                    self::LEGAL_MANAGEMENT => [
                        'label' => 'legal_management',
                        'description' => 'Manage legal documents (Privacy, Terms, etc.)',
                        'route' => 'admin.legal.index',
                        'icon' => 'Scale',
                    ],
                    self::ACTIVITY_LOG => [
                        'label' => 'activity_log',
                        'description' => 'View system activity logs and audit trail',
                        'route' => 'admin.activity_logs.index',
                        'icon' => 'History',
                    ],
                ],
            ],
        ];
    }
}