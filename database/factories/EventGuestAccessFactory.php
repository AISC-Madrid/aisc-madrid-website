<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventGuestAccess;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<EventGuestAccess>
 */
class EventGuestAccessFactory extends Factory
{
    public function definition(): array
    {
        $guestId = DB::table('guests')->value('id');

        if (! $guestId) {
            $guestId = DB::table('guests')->insertGetId([
                'username' => fake()->unique()->userName(),
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password_hash' => bcrypt('password'),
                'created_at' => now(),
            ]);
        }

        return [
            'guest_id' => $guestId,
            'event_id' => Event::factory(),
        ];
    }
}
