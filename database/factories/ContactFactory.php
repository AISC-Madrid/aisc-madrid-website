<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'greeting_name' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'category' => fake()->randomElement(['degree_director', 'professor', 'staff']),
            'organization' => fake()->company(),
            'notes' => null,
            'active' => true,
        ];
    }
}
