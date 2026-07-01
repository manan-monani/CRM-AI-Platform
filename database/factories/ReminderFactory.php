<?php

namespace Database\Factories;

use App\Models\Reminder;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    public function definition(): array
    {
        $types = ['follow_up_call', 'meeting', 'proposal_submission', 'closing_date', 'task_due', 'customer_follow_up', 'custom'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['pending', 'sent', 'dismissed'];

        return [
            'remindable_type' => fake()->randomElement(['App\\Models\\Deal', 'App\\Models\\Task']),
            'remindable_id' => 1, // Will be set in seeder
            'remind_at' => fake()->dateTimeBetween('now', '+30 days'),
            'type' => fake()->randomElement($types),
            'priority' => fake()->randomElement($priorities),
            'note' => fake()->sentence(10),
            'status' => 'pending',
            'notified_at' => null,
            'snoozed_until' => null,
            'assigned_to' => 1, // Will be set in seeder
            'created_by' => 1, // Will be set in seeder
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'remind_at' => fake()->dateTimeBetween('-7 days', '-1 day'),
            'status' => 'pending',
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'remind_at' => fake()->dateTimeBetween('today', 'today 23:59:59'),
            'status' => 'pending',
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'notified_at' => now(),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }
}
