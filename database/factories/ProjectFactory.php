<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'title_es' => fake()->sentence(3),
            'title_en' => fake()->sentence(3),
            'category' => fake()->randomElement(['ai', 'vision', 'desarrollo-web']),
            'description_es' => fake()->paragraph(),
            'description_en' => fake()->paragraph(),
            'tags' => fake()->randomElements(['Python', 'JavaScript', 'PHP', 'PyTorch', 'React'], 2),
            'team_credit' => fake()->name(),
            'start_date' => $start,
        ];
    }
}
