<?php

use App\Models\Event;
use App\Models\EventGuestAccess;
use Illuminate\Support\Facades\DB;

test('an event guest access can be created with factory', function () {
    $guestId = DB::table('guests')->insertGetId([
        'username' => 'guesttest',
        'name' => 'Guest Test',
        'email' => 'guest@test.com',
        'password_hash' => bcrypt('password'),
        'created_at' => now(),
    ]);

    $event = Event::factory()->create();

    $access = EventGuestAccess::factory()->create([
        'guest_id' => $guestId,
        'event_id' => $event->id,
    ]);

    expect($access->exists)->toBeTrue();
});
