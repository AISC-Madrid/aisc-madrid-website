<?php

use App\Actions\StoreWebpImage;
use App\Enums\MemberRole;
use App\Models\Event;
use App\Models\Member;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToWriteFile;

/**
 * The legacy event_registrations table has no Laravel migration yet; create it with the
 * columns the old site uses so the list counts and the PDF can read it.
 */
function createEventRegistrationsTable(): void
{
    Schema::create('event_registrations', function (Blueprint $table) {
        $table->id();
        $table->integer('event_id');
        $table->string('name');
        $table->string('email');
        $table->string('attendance_status')->default('not_attended');
    });
}

/**
 * @return array<string, mixed>
 */
function validEventInput(array $overrides = []): array
{
    return [
        'title_es' => 'Taller de IA',
        'title_en' => 'AI Workshop',
        'type_es' => 'Taller',
        'type_en' => 'Workshop',
        'speaker' => 'Ada Lovelace',
        'company' => 'Analytical Engines',
        'description_es' => "Primer párrafo.\n<br>\nSegundo párrafo.",
        'description_en' => 'Description.',
        'location' => 'Aula 2.3.A03',
        'start_datetime' => '2026-10-15T12:00',
        'end_datetime' => '2026-10-15T14:00',
        'youtube_url' => '',
        'requires_registration' => '1',
        'reminder_days_before' => '2',
        ...$overrides,
    ];
}

function eventsAdmin(): Member
{
    return Member::factory()->role(MemberRole::Events)->create();
}

beforeEach(function () {
    Storage::fake('s3');
});

test('guests are redirected to login', function () {
    $this->get(route('dashboard.events.index'))->assertRedirect(route('login'));
});

test('every member can see the events list', function () {
    Event::factory()->upcoming()->create(['title_es' => 'Taller de IA', 'speaker' => 'Ada Lovelace']);

    $this->actingAs(Member::factory()->role(MemberRole::Marketing)->create())
        ->get(route('dashboard.events.index'))
        ->assertOk()
        ->assertSee('Taller de IA')
        ->assertSee('Ada Lovelace')
        ->assertDontSee('Crear nuevo evento')
        ->assertDontSee('attendees.pdf', false);
});

test('admins and events members see the actions', function (MemberRole $role) {
    $event = Event::factory()->create();

    $this->actingAs(Member::factory()->role($role)->create())
        ->get(route('dashboard.events.index'))
        ->assertOk()
        ->assertSee('Crear nuevo evento')
        ->assertSee(route('dashboard.events.edit', $event), false)
        ->assertSee(route('dashboard.events.attendees-pdf', $event), false)
        ->assertSee(route('dashboard.events.destroy', $event), false);
})->with([MemberRole::Admin, MemberRole::Events]);

test('the list shows dates in Madrid time, newest first, with registration counts', function () {
    createEventRegistrationsTable();
    $older = Event::factory()->create(['title_es' => 'Evento antiguo', 'start_datetime' => '2025-01-10 10:00:00', 'end_datetime' => '2025-01-10 12:00:00']);
    $newer = Event::factory()->create(['title_es' => 'Evento nuevo', 'start_datetime' => '2026-10-15 10:00:00', 'end_datetime' => '2026-10-15 12:00:00']);
    DB::table('event_registrations')->insert([
        ['event_id' => $newer->id, 'name' => 'Ada', 'email' => 'ada@example.com', 'attendance_status' => 'attended'],
        ['event_id' => $newer->id, 'name' => 'Alan', 'email' => 'alan@example.com', 'attendance_status' => 'not_attended'],
    ]);

    $this->actingAs(eventsAdmin())
        ->get(route('dashboard.events.index'))
        ->assertOk()
        ->assertSeeInOrder(['Evento nuevo', '15/10/2026 12:00', 'Evento antiguo', '10/01/2025 11:00'])
        ->assertViewHas('registrationCounts', fn ($counts) => $counts->get($newer->id) === 2 && ! $counts->has($older->id));
});

test('events can be searched by title or speaker', function () {
    Event::factory()->create(['title_es' => 'Taller de IA', 'speaker' => 'Ada Lovelace']);
    Event::factory()->create(['title_es' => 'Charla de robótica', 'speaker' => 'Alan Turing']);

    $this->actingAs(eventsAdmin())
        ->get(route('dashboard.events.index', ['search' => 'turing']))
        ->assertSee('Charla de robótica')
        ->assertDontSee('Taller de IA');
});

test('members without permission cannot manage events', function (string $method, string $route) {
    $event = Event::factory()->create();
    $member = Member::factory()->role(MemberRole::Web)->create();

    $this->actingAs($member)
        ->call($method, route($route, $route === 'dashboard.events.create' || $route === 'dashboard.events.store' ? [] : $event))
        ->assertForbidden();

    expect(Event::find($event->id))->not->toBeNull();
})->with([
    ['GET', 'dashboard.events.create'],
    ['POST', 'dashboard.events.store'],
    ['GET', 'dashboard.events.edit'],
    ['PUT', 'dashboard.events.update'],
    ['DELETE', 'dashboard.events.destroy'],
    ['GET', 'dashboard.events.attendees-pdf'],
]);

test('the create form can be rendered', function () {
    $this->actingAs(eventsAdmin())
        ->get(route('dashboard.events.create'))
        ->assertOk()
        ->assertSee('Crear nuevo evento')
        ->assertSee('name="gallery[]"', false);
});

test('events can be created with a main image and gallery stored as WebP in the bucket', function () {
    $this->actingAs(eventsAdmin())
        ->post(route('dashboard.events.store'), validEventInput([
            'image' => UploadedFile::fake()->image('cover.jpg', 800, 800),
            'gallery' => [
                UploadedFile::fake()->image('one.png', 400, 300),
                UploadedFile::fake()->image('two.jpg', 400, 300),
            ],
        ]))
        ->assertRedirect(route('dashboard.events.index'))
        ->assertSessionHas('status');

    $event = Event::sole();

    expect($event)
        ->title_es->toBe('Taller de IA')
        ->speaker->toBe('Ada Lovelace')
        ->requires_registration->toBeTrue()
        ->reminder_enabled->toBeFalse();

    expect($event->start_datetime->utc()->format('Y-m-d H:i'))->toBe('2026-10-15 10:00');
    expect($event->end_datetime->utc()->format('Y-m-d H:i'))->toBe('2026-10-15 12:00');

    expect($event->image_path)->toStartWith("events-workshops/{$event->id}/img_")->toEndWith('.webp');
    Storage::disk('s3')->assertExists($event->image_path);
    expect(Storage::disk('s3')->mimeType($event->image_path))->toBe('image/webp');

    expect($event->gallery_paths)->toHaveCount(2);
    foreach ($event->gallery_paths as $path) {
        expect($path)->toStartWith("events-workshops/{$event->id}/gallery/img_");
        Storage::disk('s3')->assertExists($path);
    }
});

test('an empty speaker defaults to AISC Madrid', function () {
    $this->actingAs(eventsAdmin())
        ->post(route('dashboard.events.store'), validEventInput([
            'speaker' => '',
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ]))
        ->assertSessionHasNoErrors();

    expect(Event::sole()->speaker)->toBe('AISC Madrid');
});

test('creating an event validates the input', function () {
    $this->actingAs(eventsAdmin())
        ->post(route('dashboard.events.store'), validEventInput([
            'title_es' => '',
            'end_datetime' => '2026-10-15T10:00',
            'youtube_url' => 'not a url',
            'gallery' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
        ]))
        ->assertSessionHasErrors(['title_es', 'end_datetime', 'image', 'youtube_url', 'gallery.0']);

    expect(Event::count())->toBe(0);
});

test('the edit form shows the current values in Madrid time', function () {
    $event = Event::factory()->create([
        'title_es' => 'Taller de IA',
        'start_datetime' => '2026-10-15 10:00:00',
        'image_path' => 'events-workshops/5/cover.webp',
        'gallery_paths' => ['events-workshops/5/gallery/01.webp'],
    ]);

    $this->actingAs(eventsAdmin())
        ->get(route('dashboard.events.edit', $event))
        ->assertOk()
        ->assertSee('Editar evento')
        ->assertSee('value="Taller de IA"', false)
        ->assertSee('value="2026-10-15T12:00"', false)
        ->assertSee('https://s3.aiscmadrid.com/aisc-public/events-workshops/5/cover.webp', false)
        ->assertSee('https://s3.aiscmadrid.com/aisc-public/events-workshops/5/gallery/01.webp', false);
});

test('updating without new images keeps the current ones', function () {
    $event = Event::factory()->create([
        'image_path' => 'events-workshops/5/cover.webp',
        'gallery_paths' => ['events-workshops/5/gallery/01.webp'],
    ]);

    $this->actingAs(eventsAdmin())
        ->put(route('dashboard.events.update', $event), validEventInput(['title_es' => 'Título nuevo']))
        ->assertRedirect(route('dashboard.events.index'));

    expect($event->refresh())
        ->title_es->toBe('Título nuevo')
        ->image_path->toBe('events-workshops/5/cover.webp')
        ->gallery_paths->toBe(['events-workshops/5/gallery/01.webp']);
});

test('new gallery photos replace the whole gallery and a new image replaces the cover', function () {
    $event = Event::factory()->create([
        'image_path' => 'events-workshops/5/cover.webp',
        'gallery_paths' => ['events-workshops/5/gallery/01.webp', 'events-workshops/5/gallery/02.webp'],
    ]);

    $this->actingAs(eventsAdmin())
        ->put(route('dashboard.events.update', $event), validEventInput([
            'image' => UploadedFile::fake()->image('new-cover.webp'),
            'gallery' => [UploadedFile::fake()->image('new.jpg')],
        ]))
        ->assertSessionHasNoErrors();

    $event->refresh();

    expect($event->image_path)->not->toBe('events-workshops/5/cover.webp')->toStartWith("events-workshops/{$event->id}/img_");
    expect($event->gallery_paths)->toHaveCount(1);
    Storage::disk('s3')->assertExists($event->gallery_paths[0]);
});

test('events can be deleted', function () {
    $event = Event::factory()->create(['title_es' => 'Evento a borrar']);

    $this->actingAs(eventsAdmin())
        ->delete(route('dashboard.events.destroy', $event))
        ->assertRedirect(route('dashboard.events.index'))
        ->assertSessionHas('status', 'Evento «Evento a borrar» eliminado.');

    expect(Event::find($event->id))->toBeNull();
});

test('the attendee list downloads as a PDF', function () {
    createEventRegistrationsTable();
    $event = Event::factory()->create(['title_es' => 'Sergio Ayala, Co-Founder | AISC x TRAZA', 'location' => 'Aula 2.3.A03, Edificio Sabatini']);
    DB::table('event_registrations')->insert([
        ['event_id' => $event->id, 'name' => 'José Miguel Medina', 'email' => 'jose@example.com', 'attendance_status' => 'attended'],
        ['event_id' => $event->id, 'name' => 'Albert Franquet', 'email' => '100495469@alumnos.uc3m.es', 'attendance_status' => 'not_attended'],
    ]);

    $response = $this->actingAs(eventsAdmin())
        ->get(route('dashboard.events.attendees-pdf', $event))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->headers->get('Content-Disposition'))
        ->toContain('inline')
        ->toContain("asistentes-{$event->id}-sergio-ayala-co-founder-aisc-x-traza.pdf");
    expect($response->getContent())->toStartWith('%PDF-');
});

test('the PDF works when the registrations table does not exist', function () {
    $event = Event::factory()->create();

    $this->actingAs(eventsAdmin())
        ->get(route('dashboard.events.attendees-pdf', $event))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('the sidebar links to the events tab', function () {
    $this->actingAs(Member::factory()->role(MemberRole::Marketing)->create())
        ->get(route('dashboard'))
        ->assertSee(route('dashboard.events.index'), false);
});

test('a failed upload sends the member back to the form instead of a 500', function () {
    $this->mock(StoreWebpImage::class, function ($mock) {
        $mock->shouldReceive('__invoke')->andThrow(UnableToWriteFile::atLocation('events-workshops/1/img.webp', 'no credentials'));
    });

    $this->actingAs(eventsAdmin())
        ->from(route('dashboard.events.create'))
        ->post(route('dashboard.events.store'), validEventInput([
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ]))
        ->assertRedirect(route('dashboard.events.create'))
        ->assertSessionHasErrors('image')
        ->assertSessionHasInput('title_es', 'Taller de IA');

    expect(Event::count())->toBe(0);
});

test('a failed upload while editing keeps the event unchanged', function () {
    $event = Event::factory()->create(['title_es' => 'Título original', 'image_path' => 'events-workshops/5/cover.webp']);

    $this->mock(StoreWebpImage::class, function ($mock) {
        $mock->shouldReceive('__invoke')->andThrow(UnableToWriteFile::atLocation('events-workshops/5/img.webp', 'no credentials'));
    });

    $this->actingAs(eventsAdmin())
        ->from(route('dashboard.events.edit', $event))
        ->put(route('dashboard.events.update', $event), validEventInput([
            'title_es' => 'Título nuevo',
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ]))
        ->assertRedirect(route('dashboard.events.edit', $event))
        ->assertSessionHasErrors('image');

    expect($event->refresh())
        ->title_es->toBe('Título original')
        ->image_path->toBe('events-workshops/5/cover.webp');
});

test('the s3 disk accepts the legacy S3_* variable names and always uses path-style URLs', function () {
    $legacy = [
        'S3_ACCESS_KEY' => 'legacy-key',
        'S3_SECRET_KEY' => 'legacy-secret',
        'AWS_ACCESS_KEY_ID' => '',
        'AWS_SECRET_ACCESS_KEY' => '',
        'AWS_USE_PATH_STYLE_ENDPOINT' => 'false',
    ];
    $previous = [];

    foreach ($legacy as $name => $value) {
        $previous[$name] = getenv($name);
        putenv("{$name}={$value}");
        $_ENV[$name] = $_SERVER[$name] = $value;
    }

    try {
        $s3 = (require config_path('filesystems.php'))['disks']['s3'];
    } finally {
        foreach ($previous as $name => $value) {
            putenv($value === false ? $name : "{$name}={$value}");
            unset($_ENV[$name], $_SERVER[$name]);
        }
    }

    expect($s3)
        ->key->toBe('legacy-key')
        ->secret->toBe('legacy-secret')
        ->use_path_style_endpoint->toBeTrue();
});

test('a PHP error while processing images also goes back to the form', function () {
    $this->mock(StoreWebpImage::class, function ($mock) {
        $mock->shouldReceive('__invoke')->andThrow(new Error('Call to undefined function imagewebp()'));
    });

    $this->actingAs(eventsAdmin())
        ->from(route('dashboard.events.create'))
        ->post(route('dashboard.events.store'), validEventInput([
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ]))
        ->assertRedirect(route('dashboard.events.create'))
        ->assertSessionHasErrors('image');

    expect(session('errors')->first('image'))
        ->toContain('No se pudo guardar el evento')
        ->toContain('Error: Call to undefined function imagewebp()');
    expect(Event::count())->toBe(0);
});

test('large photos are scaled down to at most 2000px before being stored', function () {
    $key = app(StoreWebpImage::class)(UploadedFile::fake()->image('phone-photo.jpg', 2400, 1800), 'events-workshops/9');

    $size = getimagesizefromstring(Storage::disk('s3')->get($key));

    expect($key)->toEndWith('.webp');
    expect([$size[0], $size[1]])->toBe([2000, 1500]);
    expect(Storage::disk('s3')->size($key))->toBeLessThanOrEqual(1_048_576);
});

test('the s3 disk only sends checksums when required, for MinIO compatibility', function () {
    expect(config('filesystems.disks.s3'))
        ->request_checksum_calculation->toBe('when_required')
        ->response_checksum_validation->toBe('when_required');
});
