<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FAQ;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FAQ>
 */
class FAQFactory extends Factory
{
    protected $model = FAQ::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'question' => $this->faker->sentence(),
            'answer' => $this->faker->paragraphs(3, true),
            'user_id' => User::factory(),
            'count' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
            'tags' => implode(',', $this->faker->words(3, false)),
            'search_keywords' => $this->faker->sentence(),
            'priority' => $this->faker->numberBetween(1, 3),
            'difficulty' => $this->faker->numberBetween(1, 3),
        ];
    }

    /**
     * Indicate that the FAQ is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the FAQ has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => 3,
        ]);
    }

    /**
     * Indicate that the FAQ has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => 1,
        ]);
    }
}
