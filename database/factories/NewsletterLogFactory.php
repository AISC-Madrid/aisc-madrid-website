<?php

namespace Database\Factories;

use App\Models\NewsletterLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterLog>
 */
class NewsletterLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->safeEmail(),
            'template_name' => 'emails.newsletter',
            'sent_at' => now(),
        ];
    }
}
