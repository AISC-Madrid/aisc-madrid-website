<?php

namespace Database\Seeders;

use App\Models\ContactEmailLog;
use Illuminate\Database\Seeder;

class ContactEmailLogSeeder extends Seeder
{
    public function run(): void
    {
        ContactEmailLog::factory()->count(10)->create();
    }
}
