<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadForm;
use App\Models\LeadFormField;
use App\Models\Notification;
use App\Models\Reminder;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use App\Enums\UserType;
use App\Enums\SalesRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Truncate Tables
        Schema::disableForeignKeyConstraints();
        $tables = [
            'users', 'leads', 'deals', 'tasks', 'reminders', 'notifications',
            'lead_forms', 'lead_form_fields', 'activity_logs', 'deal_collaborators',
            'model_has_roles', 'role_has_permissions', 'user_roles'
        ];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();

        $this->command->info('✓ Database truncated');

        // 2. Ensure Roles Exist (DatabaseSeeder calls RoleSeeder first)

        $password = Hash::make('12345678');

        // 3. Create Users

        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => $password,
            'type' => UserType::SUPER_ADMIN,
            'email_verified_at' => now(),
            'profile_image' => 'profile-photos/admin.jpg', // Placeholder
        ]);

        // Sales Manager (Admin Type)
        $manager = User::create([
            'name' => 'Sales Manager',
            'email' => 'manager@admin.com',
            'password' => $password,
            'type' => UserType::ADMIN,
            'sales_role' => SalesRole::MANAGER,
            'email_verified_at' => now(),
            'profile_image' => 'profile-photos/manager.jpg', // Placeholder
        ]);
        $managerRole = Role::where('slug', 'manager')->orWhere('slug', 'sales-manager')->first();
        if ($managerRole)
            $manager->roles()->attach($managerRole);

        // Sales Rep (Employee)
        $employee = User::create([
            'name' => 'John Doe',
            'email' => 'employee@crm.com', // Matching Login.vue
            'password' => $password,
            'type' => UserType::ADMIN,
            'sales_role' => SalesRole::REP,
            'email_verified_at' => now(),
            'profile_image' => 'profile-photos/employee.jpg', // Placeholder
        ]);
        $repRole = Role::where('slug', 'sales-executive')->orWhere('slug', 'employee')->first();
        if ($repRole)
            $employee->roles()->attach($repRole);

        // Customer
        $customer = User::create([
            'name' => 'Jane Smith',
            'email' => 'customer@crm.com', // Matching Login.vue
            'password' => $password,
            'type' => UserType::CUSTOMER,
            'email_verified_at' => now(),
            'profile_image' => 'profile-photos/customer.jpg', // Placeholder
        ]);

        $this->command->info('✓ Demo Users Created');

        // 4. Create Meaningful Data (Leads, Deals, Tasks)
        $users = [$superAdmin, $manager, $employee];

        // 4a. Create Lead Sources
        $sources = [
            ['name' => 'Website', 'slug' => 'website'],
            ['name' => 'LinkedIn', 'slug' => 'linkedin'],
            ['name' => 'Referral', 'slug' => 'referral'],
            ['name' => 'Cold Call', 'slug' => 'cold-call'],
            ['name' => 'Conference', 'slug' => 'conference'],
        ];

        foreach ($sources as $source) {
            \App\Models\LeadSource::firstOrCreate(
            ['slug' => $source['slug']],
            ['name' => $source['name'], 'is_active' => true]
            );
        }
        $sourceSlugs = collect($sources)->pluck('slug')->toArray();

        // 4b. Create Customers (Goal: 30)
        // We already created 1 customer above. Need 29 more.
        User::factory()->count(29)->create([
            'type' => UserType::CUSTOMER,
            'password' => $password,
        ]);
        $this->command->info('✓ 30 Customers Created');

        // 4c. Create Leads (Goal: 50)
        // We will create 50 leads.
        $leads = [];
        for ($i = 0; $i < 50; $i++) {
            $leads[] = Lead::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'company' => fake()->company(),
                'message' => fake()->sentence(),
                // Ensure we have a good mix of active statuses as requested
                'status' => fake()->randomElement(['new', 'contacted', 'qualified', 'converted', 'lost']),
                'source' => fake()->randomElement($sourceSlugs),
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
        $this->command->info('✓ 50 Leads Created');

        // 4d. Create Deals (Goal: ~20 for variety, derived from leads)
        $deals = [];
        foreach ($leads as $index => $lead) {
            // Create a deal for roughly 40% of leads
            if ($index % 2.5 === 0) {
                $deal = Deal::create([
                    'title' => 'Deal for ' . $lead->company,
                    'dealable_type' => Lead::class ,
                    'dealable_id' => $lead->id,
                    'value' => fake()->numberBetween(5000, 100000),
                    'stage' => fake()->randomElement(['lead', 'new', 'qualified', 'proposal', 'negotiation', 'won', 'lost']),
                    'status' => 'active',
                    'probability' => fake()->numberBetween(10, 90),
                    'expected_close_date' => fake()->dateTimeBetween('now', '+60 days'),
                    'assigned_to' => fake()->randomElement($users)->id,
                    'created_by' => $superAdmin->id,
                ]);
                $deals[] = $deal;
            }
        }
        $this->command->info('✓ Deals Created');

        // 4e. Create Tasks (Goal: 50)
        for ($i = 0; $i < 50; $i++) {
            Task::create([
                'title' => fake()->sentence(4),
                'description' => fake()->paragraph(),
                'taskable_type' => Deal::class ,
                'taskable_id' => fake()->randomElement($deals)->id,
                'priority' => fake()->randomElement(['low', 'medium', 'high']),
                'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
                'due_date' => fake()->dateTimeBetween('-5 days', '+10 days'),
                'assigned_to' => fake()->randomElement($users)->id,
                'created_by' => $manager->id,
            ]);
        }
        $this->command->info('✓ 50 Tasks Created');

        // Notifications
        Notification::create([
            'user_id' => $manager->id,
            'type' => 'info',
            'title' => 'New Deal Created',
            'description' => 'Enterprise License - TechCorp has been created.',
            'read_at' => null,
        ]);

        $this->command->info('✓ Notifications Created');

        // 5. Create Default Lead Form
        $defaultForm = LeadForm::create([
            'slug' => 'default-lead-form',
            'name' => 'Default Lead Generation Form',
            'title' => 'Contact Us',
            'description' => 'Please fill out the form below and we will get back to you shortly.',
            'status' => 'active',
            'branding_settings' => [
                'primary_color' => '#2563eb', // Blue 600
                'background_color' => '#ffffff',
                'text_color' => '#1f2937', // Gray 800
                'submit_button_text' => 'Send Message',
                'success_message' => 'Thank you! We have received your message and will contact you soon.',
            ],
            'notification_emails' => ['admin@crm.com'],
            'is_default' => true,
        ]);

        // Create Form Fields
        $fields = [
            ['label' => 'Full Name', 'name' => 'full-name', 'type' => 'text', 'placeholder' => 'John Doe', 'is_required' => true, 'order_index' => 1],
            ['label' => 'Email Address', 'name' => 'email-address', 'type' => 'email', 'placeholder' => 'john@example.com', 'is_required' => true, 'order_index' => 2],
            ['label' => 'Phone Number', 'name' => 'phone-number', 'type' => 'tel', 'placeholder' => '+1 (555) 000-0000', 'is_required' => false, 'order_index' => 3],
            ['label' => 'Company Name', 'name' => 'company-name', 'type' => 'text', 'placeholder' => 'Acme Corp', 'is_required' => false, 'order_index' => 4],
            ['label' => 'Message', 'name' => 'message', 'type' => 'textarea', 'placeholder' => 'How can we help you?', 'is_required' => true, 'order_index' => 5],
        ];

        foreach ($fields as $field) {
            LeadFormField::create(['lead_form_id' => $defaultForm->id, ...$field]);
        }

        $this->command->info('✓ Lead Form Created');
    }
}