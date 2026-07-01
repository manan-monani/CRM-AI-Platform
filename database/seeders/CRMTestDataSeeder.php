<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadForm;
use App\Models\LeadFormField;
use App\Models\Notification;
use App\Models\Reminder;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CRMTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin and staff users
        $admin = User::firstOrCreate(
        ['email' => 'admin@crm.com'],
        [
            'name' => 'CRM Admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'type' => \App\Enums\UserType::ADMIN,
            'sales_role' => \App\Enums\SalesRole::MANAGER,
        ]
        );

        $staff1 = User::firstOrCreate(
        ['email' => 'staff1@crm.com'],
        [
            'name' => 'Sarah Johnson',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'type' => \App\Enums\UserType::ADMIN,
            'sales_role' => \App\Enums\SalesRole::REP,
        ]
        );

        $staff2 = User::firstOrCreate(
        ['email' => 'staff2@crm.com'],
        [
            'name' => 'Mike Wilson',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'type' => \App\Enums\UserType::ADMIN,
            'sales_role' => \App\Enums\SalesRole::REP,
        ]
        );

        $staff3 = User::firstOrCreate(
        ['email' => 'staff3@crm.com'],
        [
            'name' => 'Emma Davis',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'type' => \App\Enums\UserType::ADMIN,
            'sales_role' => \App\Enums\SalesRole::REP,
        ]
        );

        // Assign Roles
        $managerRole = \App\Models\Role::where('slug', 'sales-manager')->first();
        if ($managerRole && !$admin->roles()->where('role_id', $managerRole->id)->exists()) {
            $admin->roles()->attach($managerRole);
        }

        $repRole = \App\Models\Role::where('slug', 'sales-executive')->first();
        if ($repRole) {
            foreach ([$staff1, $staff2, $staff3] as $staff) {
                if (!$staff->roles()->where('role_id', $repRole->id)->exists()) {
                    $staff->roles()->attach($repRole);
                }
            }
        }

        $users = [$admin, $staff1, $staff2, $staff3];

        $this->command->info('✓ Created users with correct roles');

        // Create Leads first
        $leads = [];
        for ($i = 0; $i < 10; $i++) {
            $leads[] = Lead::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'company' => fake()->company(),
                'message' => fake()->sentence(10),
            ]);
        }

        $this->command->info('✓ Created 10 leads');

        // Create Deals
        $deals = [];
        for ($i = 0; $i < 15; $i++) {
            $lead = fake()->randomElement($leads);
            $assignedUser = fake()->randomElement($users);

            $deal = Deal::create([
                'title' => fake()->randomElement([
                    'Enterprise Software Deal',
                    'Cloud Migration Project',
                    'Digital Marketing Campaign',
                    'Website Redesign',
                    'Mobile App Development',
                    'Consulting Services',
                    'Annual Support Contract',
                ]) . ' - ' . fake()->company(),
                'dealable_type' => 'App\\Models\\Lead',
                'dealable_id' => $lead->id,
                'value' => fake()->numberBetween(5000, 500000),
                'stage' => fake()->randomElement(['lead', 'qualified', 'proposal', 'negotiation', 'won', 'lost']),
                'status' => fake()->randomElement(['active', 'won', 'lost']),
                'probability' => fake()->numberBetween(10, 90),
                'expected_close_date' => fake()->dateTimeBetween('now', '+90 days'),
                'description' => fake()->paragraph(3),
                'assigned_to' => $assignedUser->id,
                'created_by' => $admin->id,
            ]);

            // Add 1-2 collaborators to some deals
            if (fake()->boolean(60)) {
                $collaborators = fake()->randomElements(
                    array_filter($users, fn($u) => $u->id !== $assignedUser->id),
                    fake()->numberBetween(1, 2)
                );
                $deal->collaborators()->sync(array_map(fn($u) => $u->id, $collaborators));
            }

            $deals[] = $deal;
        }

        $this->command->info('✓ Created 15 deals with collaborators');

        // Create Tasks
        $tasks = [];
        for ($i = 0; $i < 20; $i++) {
            $taskable = fake()->randomElement([...$deals, ...$leads]);

            $task = Task::create([
                'title' => fake()->randomElement([
                    'Prepare proposal',
                    'Schedule demo call',
                    'Send follow-up email',
                    'Review contract',
                    'Update pricing',
                    'Create presentation',
                    'Set up meeting',
                    'Research competitor',
                    'Prepare quote',
                    'Send invoice',
                ]),
                'description' => fake()->sentence(10),
                'taskable_type' => get_class($taskable),
                'taskable_id' => $taskable->id,
                'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
                'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
                'due_date' => fake()->dateTimeBetween('-5 days', '+15 days'),
                'reminder_at' => fake()->dateTimeBetween('now', '+10 days'),
                'assigned_to' => fake()->randomElement($users)->id,
                'created_by' => $admin->id,
            ]);

            $tasks[] = $task;
        }

        $this->command->info('✓ Created 20 tasks');

        // Create Reminders for Deals
        foreach ($deals as $deal) {
            // Each deal gets 1-3 reminders
            $reminderCount = fake()->numberBetween(1, 3);

            for ($j = 0; $j < $reminderCount; $j++) {
                Reminder::create([
                    'remindable_type' => 'App\\Models\\Deal',
                    'remindable_id' => $deal->id,
                    'remind_at' => fake()->randomElement([
                        fake()->dateTimeBetween('-3 days', '-1 day'), // Overdue
                        fake()->dateTimeBetween('today', 'today 23:59:59'), // Today
                        fake()->dateTimeBetween('+1 day', '+7 days'), // Upcoming
                        fake()->dateTimeBetween('+8 days', '+30 days'), // Future
                    ]),
                    'type' => fake()->randomElement(['follow_up_call', 'meeting', 'proposal_submission', 'closing_date']),
                    'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
                    'note' => fake()->sentence(8),
                    'status' => fake()->randomElement(['pending', 'pending', 'pending', 'sent']), // More pending
                    'notified_at' => fake()->boolean(20) ? now() : null,
                    'assigned_to' => $deal->assigned_to,
                    'created_by' => $admin->id,
                ]);
            }
        }

        $this->command->info('✓ Created reminders for deals');

        // Create Reminders for Tasks
        foreach (array_slice($tasks, 0, 10) as $task) {
            Reminder::create([
                'remindable_type' => 'App\\Models\\Task',
                'remindable_id' => $task->id,
                'remind_at' => $task->due_date,
                'type' => 'task_due',
                'priority' => $task->priority,
                'note' => "Don't forget: {$task->title}",
                'status' => 'pending',
                'assigned_to' => $task->assigned_to,
                'created_by' => $admin->id,
            ]);
        }

        $this->command->info('✓ Created reminders for tasks');

        // Create Notifications for each user
        foreach ($users as $user) {
            // Recent notifications
            for ($i = 0; $i < 5; $i++) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => fake()->randomElement(['reminder', 'info', 'success', 'warning']),
                    'title' => fake()->randomElement([
                        '📞 Follow-up Call Reminder',
                        '📅 Meeting Reminder',
                        '✅ Task Completed',
                        '🎯 Deal Closing Soon',
                        '👤 New Lead Assigned',
                        '📝 Proposal Due',
                    ]),
                    'description' => fake()->sentence(12),
                    'action_url' => '/admin/deals/' . fake()->randomElement($deals)->id,
                    'data' => [
                        'priority' => fake()->randomElement(['low', 'medium', 'high']),
                    ],
                    'read_at' => fake()->boolean(40) ? now() : null,
                    'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
                ]);
            }
        }

        $this->command->info('✓ Created notifications for all users');

        // Summary
        $this->command->info("\n=== CRM Test Data Created ===");

        // Create Default Lead Form
        $defaultForm = LeadForm::updateOrCreate(
        ['slug' => 'default-lead-form'],
        [
            'name' => 'Default Lead Generation Form',
            'title' => 'Contact Us',
            'description' => 'Please fill out the form below and we will get back to you shortly.',
            'status' => 'active',
            'branding_settings' => [
                'primary_color' => '#4f46e5', // Indigo 600
                'background_color' => '#ffffff',
                'text_color' => '#1f2937', // Gray 800
                'submit_button_text' => 'Send Message',
                'success_message' => 'Thank you! We have received your message and will contact you soon.',
            ],
            'notification_emails' => ['admin@crm.com'],
            'is_default' => true,
        ]
        );

        // Clear existing fields to avoid duplicates
        $defaultForm->fields()->delete();

        // Create Form Fields
        $fields = [
            [
                'label' => 'Full Name',
                'name' => 'full-name',
                'type' => 'text',
                'placeholder' => 'John Doe',
                'is_required' => true,
                'order_index' => 1,
            ],
            [
                'label' => 'Email Address',
                'name' => 'email-address',
                'type' => 'email',
                'placeholder' => 'john@example.com',
                'is_required' => true,
                'order_index' => 2,
            ],
            [
                'label' => 'Phone Number',
                'name' => 'phone-number',
                'type' => 'tel',
                'placeholder' => '+1 (555) 000-0000',
                'is_required' => false,
                'order_index' => 3,
            ],
            [
                'label' => 'Company Name',
                'name' => 'company-name',
                'type' => 'text',
                'placeholder' => 'Acme Corp',
                'is_required' => false,
                'order_index' => 4,
            ],
            [
                'label' => 'Message',
                'name' => 'message',
                'type' => 'textarea',
                'placeholder' => 'How can we help you?',
                'is_required' => true,
                'order_index' => 5,
            ],
        ];

        foreach ($fields as $field) {
            LeadFormField::create([
                'lead_form_id' => $defaultForm->id,
                ...$field,
            ]);
        }

        $this->command->info('✓ Created default lead form and fields');

        $this->command->table(
        ['Type', 'Count'],
        [
            ['Users', count($users)],
            ['Leads', count($leads)],
            ['Deals', count($deals)],
            ['Tasks', count($tasks)],
            ['Reminders', Reminder::count()],
            ['Notifications', Notification::count()],
            ['Lead Forms', LeadForm::count()],
        ]
        );

        $this->command->info("\nTest Accounts:");
        $this->command->info('Admin: admin@crm.com / password');
        $this->command->info('Staff: staff1@crm.com / password');
        $this->command->info('Staff: staff2@crm.com / password');
        $this->command->info('Staff: staff3@crm.com / password');
    }
}