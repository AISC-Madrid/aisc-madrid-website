<?php

namespace Database\Seeders;

use App\Models\RecruitingApplicant;
use Illuminate\Database\Seeder;

class RecruitingApplicantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RecruitingApplicant::factory()->count(10)->create();
    }
}
