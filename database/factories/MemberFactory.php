<?php

namespace Database\Factories;

use App\Models\AlumniHonor;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
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
            'password_hash' => bcrypt('password'),
            'mail' => fake()->unique()->safeEmail(),
            'position_es' => fake()->jobTitle(),
            'position_en' => fake()->jobTitle(),
            'socials' => fake()->url(),
            'board' => false,
            'active' => true,
            'image_path' => 'https://ui-avatars.com/api/?name='.urlencode(fake()->name()),
        ];
    }

    public function honorMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ])->afterCreating(function (Member $member) {
            AlumniHonor::factory()->create([
                'member_id' => $member->id,
            ]);
        });
    }
}
