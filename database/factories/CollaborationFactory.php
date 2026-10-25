<?php

namespace Database\Factories;

use App\Models\Collaboration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Collaboration>
 */
class CollaborationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'event_name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'language' => fake()->randomElement(['es', 'en']),
        ];
    }
}
