<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Todo>
 */
class TodoFactory extends Factory
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
            'description' => $this->faker->paragraph(),
            'due_date' => $this->faker->optional(0.7)->dateTimeBetween('now', '+30 days'),
            'priority' => $this->faker->randomElement(['high', 'medium', 'low']),
            'is_completed' => $this->faker->boolean(20), // 20%の確率で完了済み
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
