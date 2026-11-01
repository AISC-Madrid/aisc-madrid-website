<?php

namespace Database\Factories;

use App\Models\RecruitingApplicant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecruitingApplicant>
 */
class RecruitingApplicantFactory extends Factory
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
            'campus' => fake()->randomElement(['Leganés', 'Getafe', 'Colmenarejo']),
            'position' => fake()->randomElement(['events', 'web', 'marketing', 'finance']),
            'interest' => fake()->paragraph(),
            'cohort' => (string) config('aisc.recruiting_cohort'),
        ];
    }

    public function forCohort(string $cohort): static
    {
        return $this->state(fn (array $attributes) => [
            'cohort' => $cohort,
        ]);
    }
}
