<?php

use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('events index renders with upcoming and past events', function () {
    Event::factory()->upcoming()->create(['title_es' => 'Taller de IA']);
    Event::factory()->past()->create(['title_es' => 'Charla pasada']);

    $this->get(route('events.index', ['locale' => 'es']))
        ->assertOk()
        ->assertSee('Taller de IA')
        ->assertSee('Charla pasada');
});

test('events index uses the english type for the filter', function () {
    Event::factory()->upcoming()->create(['type_es' => 'Taller', 'type_en' => 'Workshop']);

    $this->get(route('events.index', ['locale' => 'es']))
        ->assertOk()
        ->assertSee('data-type="workshop"', false);
});

test('events without a type still render', function () {
    $event = Event::factory()->upcoming()->create([
        'title_es' => 'Evento sin tipo',
        'type_es' => null,
        'type_en' => null,
    ]);

    $this->get(route('events.index', ['locale' => 'es']))->assertOk()->assertSee('Evento sin tipo');
    $this->get(route('events.show', ['locale' => 'es', 'event' => $event]))->assertOk()->assertSee('Evento sin tipo');
});

test('event page shows the type and speaker in each language', function (string $locale, string $typeName) {
    $event = Event::factory()->upcoming()->create([
        'type_es' => 'Taller',
        'type_en' => 'Workshop',
        'speaker' => 'Ada Lovelace',
        'company' => 'Analytical Engines',
    ]);

    $this->get(route('events.show', ['locale' => $locale, 'event' => $event]))
        ->assertOk()
        ->assertSee($typeName)
        ->assertSee('Ada Lovelace (Analytical Engines)');
})->with([
    ['es', 'Taller'],
    ['en', 'Workshop'],
]);

test('the events table matches the production schema', function () {
    expect(Schema::getColumnListing('events'))->toEqualCanonicalizing([
        'id', 'title_es', 'title_en', 'type_es', 'type_en', 'speaker', 'company',
        'description_es', 'description_en', 'start_datetime', 'end_datetime', 'location',
        'image_path', 'gallery_paths', 'google_calendar_url', 'youtube_url',
        'created_at', 'updated_at', 'requires_registration', 'reminder_enabled', 'reminder_days_before',
    ]);

    $id = DB::table('events')->insertGetId([
        'title_es' => 'Mínimo',
        'title_en' => 'Minimal',
        'start_datetime' => now(),
        'end_datetime' => now()->addHour(),
    ]);

    expect(Event::find($id))
        ->speaker->toBe('AISC Madrid')
        ->requires_registration->toBeFalse()
        ->reminder_days_before->toBe(2);
});

test('event images stored as bucket keys are served from S3', function () {
    $event = Event::factory()->upcoming()->create(['image_path' => 'events-workshops/10/cover.webp']);

    $this->get(route('events.index', ['locale' => 'es']))
        ->assertOk()
        ->assertSee('https://s3.aiscmadrid.com/aisc-public/events-workshops/10/cover.webp', false);

    $this->get(route('events.show', ['locale' => 'es', 'event' => $event]))
        ->assertOk()
        ->assertSee('https://s3.aiscmadrid.com/aisc-public/events-workshops/10/cover.webp', false);
});
