<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventType = EventType::firstOrCreate(
            ['slug' => 'event'],
            ['name_es' => 'Evento', 'name_en' => 'Event']
        );

        $workshopType = EventType::firstOrCreate(
            ['slug' => 'workshop'],
            ['name_es' => 'Taller', 'name_en' => 'Workshop']
        );

        $events = [
            [
                'title_es' => 'Taller práctico: MCP y Supabase',
                'title_en' => 'Hands-on workshop: MCP and Supabase',
                'type_id' => $workshopType->id,
                'description_es' => 'Sesión práctica sobre Model Context Protocol y Supabase para construir aplicaciones con IA.',
                'description_en' => 'Hands-on session on the Model Context Protocol and Supabase to build AI-powered applications.',
                'location' => 'Edificio Sabatini, UC3M',
                'when' => '-6 weeks',
            ],
            [
                'title_es' => 'Charla de industria: Microsoft y GitHub Copilot',
                'title_en' => 'Industry talk: Microsoft and GitHub Copilot',
                'type_id' => $eventType->id,
                'description_es' => 'Profesionales de Microsoft y GitHub Copilot comparten su experiencia trabajando con IA en la industria.',
                'description_en' => 'Professionals from Microsoft and GitHub Copilot share their experience working with AI in industry.',
                'location' => 'Edificio Betancourt, UC3M',
                'when' => '-3 weeks',
            ],
            [
                'title_es' => 'BETA DASH: Lovable Buildathon',
                'title_en' => 'BETA DASH: Lovable Buildathon',
                'type_id' => $workshopType->id,
                'description_es' => 'Buildathon de un día para construir productos con IA usando Lovable.',
                'description_en' => 'A one-day buildathon to build AI-powered products using Lovable.',
                'location' => 'Edificio Sabatini, UC3M',
                'when' => '+2 weeks',
            ],
            [
                'title_es' => 'Presentación de BCG X',
                'title_en' => 'BCG X presentation',
                'type_id' => $eventType->id,
                'description_es' => 'BCG X presenta cómo aplican inteligencia artificial en proyectos reales de consultoría.',
                'description_en' => 'BCG X presents how they apply artificial intelligence in real consulting projects.',
                'location' => 'Edificio Padre Soler, UC3M',
                'when' => '+5 weeks',
            ],
        ];

        foreach ($events as $event) {
            $start = now()->modify($event['when'])->setTime(18, 0);

            Event::factory()->create([
                'title_es' => $event['title_es'],
                'title_en' => $event['title_en'],
                'type_id' => $event['type_id'],
                'description_es' => $event['description_es'],
                'description_en' => $event['description_en'],
                'location' => $event['location'],
                'start_datetime' => $start,
                'end_datetime' => $start->copy()->addHours(2),
                'requires_registration' => true,
            ]);
        }
    }
}
