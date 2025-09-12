<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'closed']),
            'received_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'sender_email' => $this->faker->email(),
            'customer_id' => $this->faker->optional()->regexify('CUST[0-9]{3}'),
            'prefecture' => $this->faker->optional()->randomElement(['東京都', '大阪府', '神奈川県', '愛知県', '福岡県']),
            'user_attribute' => $this->faker->optional()->randomElement(['個人', '法人', '代理店']),
            'subject' => $this->faker->sentence(),
            'summary' => $this->faker->optional()->paragraph(),
            'content' => $this->faker->paragraphs(3, true),
            'response' => $this->faker->optional()->paragraphs(2, true),
            'priority' => $this->faker->numberBetween(1, 4),
            'response_deadline' => $this->faker->optional()->dateTimeBetween('now', '+7 days'),
            'first_response_at' => $this->faker->optional()->dateTimeBetween('-20 days', 'now'),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-10 days', 'now'),
            'email_sent_at' => $this->faker->optional()->dateTimeBetween('-5 days', 'now'),
        ];
    }
}
