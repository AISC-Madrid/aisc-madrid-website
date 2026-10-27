<?php

namespace Database\Factories;

use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'newsletter' => true,
            'unsubscribe_token' => bin2hex(random_bytes(16)),
        ];
    }

    public function withoutNewsletter(): static
    {
        return $this->state(fn (array $attributes) => [
            'newsletter' => false,
        ]);
    }
}
