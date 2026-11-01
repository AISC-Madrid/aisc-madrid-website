<?php

namespace Database\Seeders;

use App\Models\FormSubmission;
use App\Models\NewsletterLog;
use Illuminate\Database\Seeder;

class NewsletterLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FormSubmission::query()->where('newsletter', 'yes')->each(function (FormSubmission $subscriber): void {
            NewsletterLog::factory()->create(['email' => $subscriber->email]);
        });
    }
}
