<?php

namespace Database\Seeders;

use App\Models\FormSubmission;
use Illuminate\Database\Seeder;

class FormSubmissionSeeder extends Seeder
{
    /**
     * Seed sign-ups spread over the last year, most of them subscribed to the newsletter.
     */
    public function run(): void
    {
        FormSubmission::factory()
            ->count(120)
            ->state(fn () => [
                'submitted_at' => fake()->dateTimeBetween('-12 months', 'now'),
                'newsletter' => fake()->boolean(80),
            ])
            ->create();
    }
}
