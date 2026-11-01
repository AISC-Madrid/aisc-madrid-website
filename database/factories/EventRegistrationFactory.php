<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRegistration>
 */
class EventRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'attendance_status' => EventRegistration::STATUS_NOT_ATTENDED,
            'qr_email_sent' => false,
            'reminder_sent' => false,
        ];
    }

    public function attended(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance_status' => EventRegistration::STATUS_ATTENDED,
        ]);
    }
}
