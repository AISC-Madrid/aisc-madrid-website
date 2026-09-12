<?php

namespace Database\Factories;

use App\Models\AlumniHonor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumniHonor>
 */
class AlumniHonorFactory extends Factory
{
    protected $model = AlumniHonor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'graduation_year' => fake()->numberBetween(2020, 2025),
            'honor_quote' => fake()->sentence(),
        ];
    }
}
