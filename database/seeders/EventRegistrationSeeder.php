<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Database\Seeder;

class EventRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::query()->each(function (Event $event): void {
            EventRegistration::factory()->count(5)->for($event)->create();
            EventRegistration::factory()->count(3)->attended()->for($event)->create();
        });
    }
}
