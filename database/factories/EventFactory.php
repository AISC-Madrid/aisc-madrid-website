<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-2 months', '+2 months');
        $end = (clone $start)->modify('+2 hours');

        return [
            'title_es' => fake()->sentence(4),
            'title_en' => fake()->sentence(4),
            'type_id' => EventType::factory(),
            'description_es' => fake()->paragraph(),
            'description_en' => fake()->paragraph(),
            'start_datetime' => $start,
            'end_datetime' => $end,
            'location' => fake()->city(),
            'image_path' => null,
            'requires_registration' => false,
            'reminder_enabled' => false,
        ];
    }

    public function past(): static
    {
        return $this->state(function () {
            $start = fake()->dateTimeBetween('-6 months', '-1 week');
            $end = (clone $start)->modify('+2 hours');

            return [
                'start_datetime' => $start,
                'end_datetime' => $end,
            ];
        });
    }

    public function upcoming(): static
    {
        return $this->state(function () {
            $start = fake()->dateTimeBetween('+1 week', '+2 months');
            $end = (clone $start)->modify('+2 hours');

            return [
                'start_datetime' => $start,
                'end_datetime' => $end,
            ];
        });
    }
}
