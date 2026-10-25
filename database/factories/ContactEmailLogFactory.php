<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\ContactEmailLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactEmailLog>
 */
class ContactEmailLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'template_name' => 'emails.newsletter',
            'sent_at' => now(),
        ];
    }
}
