<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deal>
 */
class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'value' => $this->faker->randomFloat(2, 1000, 50000),
            'stage' => $this->faker->randomElement(['new', 'proposal', 'negotiation', 'won', 'lost']),
            'status' => 'open',
            'probability' => $this->faker->numberBetween(10, 90),
            'expected_close_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'description' => $this->faker->paragraph(),
            'dealable_id' => Lead::factory(),
            'dealable_type' => (new Lead)->getMorphClass(),
            'assigned_to' => User::inRandomOrder()->first()->id ?? User::factory(),
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}
