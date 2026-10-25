<?php

namespace Database\Seeders;

use App\Models\Collaboration;
use Illuminate\Database\Seeder;

class CollaborationSeeder extends Seeder
{
    public function run(): void
    {
        Collaboration::factory()->count(5)->create();
    }
}
