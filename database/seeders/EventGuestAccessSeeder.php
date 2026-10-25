<?php

namespace Database\Seeders;

use App\Models\EventGuestAccess;
use Illuminate\Database\Seeder;

class EventGuestAccessSeeder extends Seeder
{
    public function run(): void
    {
        EventGuestAccess::factory()->count(5)->create();
    }
}
