<?php

use App\Enums\MemberRole;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function guestsAdmin(): Member
{
    return Member::factory()->role(MemberRole::Events)->create();
}

test('visitors are redirected to login', function () {
    $this->get(route('dashboard.guests.index'))->assertRedirect(route('login'));
});

test('admins and events members can list the guests with their events', function (MemberRole $role) {
    $event = Event::factory()->create(['title_es' => 'Charla de Spotify']);
    $guest = Guest::factory()->create(['username' => 'spotify_talk', 'name' => 'Ada Lovelace']);
    $guest->events()->attach($event);
    Guest::factory()->inactive()->create(['username' => 'old_guest']);

    $this->actingAs(Member::factory()->role($role)->create())
        ->get(route('dashboard.guests.index'))
        ->assertOk()
        ->assertSee('spotify_talk')
        ->assertSee('Ada Lovelace')
        ->assertSee('Charla de Spotify')
        ->assertSee('old_guest')
        ->assertSee('Inactivo')
        ->assertSee(route('dashboard.guests.edit', $guest), false)
        ->assertSee(route('dashboard.guests.destroy', $guest), false);
})->with([MemberRole::Admin, MemberRole::Events]);

test('the list can be searched', function () {
    Guest::factory()->create(['username' => 'spotify_talk']);
    Guest::factory()->create(['username' => 'google_talk']);

    $this->actingAs(guestsAdmin())
        ->get(route('dashboard.guests.index', ['search' => 'spotify']))
        ->assertOk()
        ->assertSee('spotify_talk')
        ->assertDontSee('google_talk');
});

test('members without permission get a 403', function (MemberRole $role) {
    $guest = Guest::factory()->create();
    $member = Member::factory()->role($role)->create();

    $this->actingAs($member)->get(route('dashboard.guests.index'))->assertForbidden();
    $this->actingAs($member)->get(route('dashboard.guests.create'))->assertForbidden();
    $this->actingAs($member)->delete(route('dashboard.guests.destroy', $guest))->assertForbidden();

    expect(Guest::find($guest->id))->not->toBeNull();
})->with([MemberRole::Marketing, MemberRole::Web, MemberRole::Finance]);

test('the create form lists the events', function () {
    Event::factory()->create(['title_es' => 'Taller de IA']);

    $this->actingAs(guestsAdmin())
        ->get(route('dashboard.guests.create'))
        ->assertOk()
        ->assertSee('Taller de IA')
        ->assertSee('name="events[]"', false);
});

test('a guest can be created with access to events', function () {
    $events = Event::factory()->count(2)->create();

    $this->actingAs(guestsAdmin())
        ->post(route('dashboard.guests.store'), [
            'username' => 'spotify_talk',
            'password' => 'secret123',
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'is_active' => '1',
            'events' => [$events[0]->id],
        ])
        ->assertRedirect(route('dashboard.guests.index'))
        ->assertSessionHas('status');

    $guest = Guest::where('username', 'spotify_talk')->firstOrFail();

    expect($guest->name)->toBe('Ada Lovelace')
        ->and($guest->is_active)->toBeTrue()
        ->and(Hash::check('secret123', $guest->password_hash))->toBeTrue()
        ->and($guest->events()->pluck('events.id')->all())->toBe([$events[0]->id]);
});

test('creating a guest validates the input', function () {
    Guest::factory()->create(['username' => 'taken']);

    $this->actingAs(guestsAdmin())
        ->post(route('dashboard.guests.store'), [
            'username' => 'taken',
            'password' => '123',
            'email' => 'not-an-email',
            'events' => [999999],
        ])
        ->assertSessionHasErrors(['username', 'password', 'email', 'events.0']);

    expect(Guest::count())->toBe(1);
});

test('a guest can be edited keeping its password when left empty', function () {
    [$oldEvent, $newEvent] = Event::factory()->count(2)->create();
    $guest = Guest::factory()->create(['username' => 'spotify_talk']);
    $guest->events()->attach($oldEvent);
    $originalHash = $guest->password_hash;

    $this->actingAs(guestsAdmin())
        ->get(route('dashboard.guests.edit', $guest))
        ->assertOk()
        ->assertSee('spotify_talk');

    $this->actingAs(guestsAdmin())
        ->put(route('dashboard.guests.update', $guest), [
            'username' => 'spotify_talk',
            'password' => '',
            'name' => 'Nuevo nombre',
            'events' => [$newEvent->id],
        ])
        ->assertRedirect(route('dashboard.guests.index'));

    $guest->refresh();

    expect($guest->name)->toBe('Nuevo nombre')
        ->and($guest->is_active)->toBeFalse()
        ->and($guest->password_hash)->toBe($originalHash)
        ->and($guest->events()->pluck('events.id')->all())->toBe([$newEvent->id]);
});

test('editing a guest can change its password', function () {
    $guest = Guest::factory()->create(['username' => 'spotify_talk']);

    $this->actingAs(guestsAdmin())
        ->put(route('dashboard.guests.update', $guest), [
            'username' => 'spotify_talk',
            'password' => 'brand-new-pass',
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors();

    expect(Hash::check('brand-new-pass', $guest->refresh()->password_hash))->toBeTrue();
});

test('a guest can be deleted with its event access', function () {
    $event = Event::factory()->create();
    $guest = Guest::factory()->create();
    $guest->events()->attach($event);

    $this->actingAs(guestsAdmin())
        ->delete(route('dashboard.guests.destroy', $guest))
        ->assertRedirect(route('dashboard.guests.index'));

    expect(Guest::find($guest->id))->toBeNull()
        ->and(DB::table('event_guest_access')->where('guest_id', $guest->id)->exists())->toBeFalse()
        ->and(Event::find($event->id))->not->toBeNull();
});
