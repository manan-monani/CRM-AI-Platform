<?php

namespace Database\Factories;

use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['call', 'email', 'meeting', 'note']),
            'subject' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'activityable_id' => Deal::factory(),
            'activityable_type' => (new Deal)->getMorphClass(),
            'performed_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'outcome' => $this->faker->optional()->randomElement(['connected', 'no_answer', 'interested', 'not_interested']),
            'duration_minutes' => $this->faker->optional()->numberBetween(5, 60),
            'scheduled_at' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
