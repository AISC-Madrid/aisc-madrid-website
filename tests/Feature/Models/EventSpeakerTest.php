<?php

use App\Models\Event;
use App\Models\EventSpeaker;
use App\Models\Speaker;

test('event speakers use the pivot model with role and order', function () {
    $event = Event::factory()->create();
    $moderator = Speaker::create(['full_name' => 'Grace Hopper']);
    $speaker = Speaker::create(['full_name' => 'Ada Lovelace']);

    $event->speakers()->attach([
        $moderator->id => ['role' => 'moderador', 'sort_order' => 2],
        $speaker->id => ['role' => 'ponente', 'sort_order' => 1],
    ]);

    $speakers = $event->speakers()->get();

    expect($speakers->pluck('full_name')->all())->toBe(['Ada Lovelace', 'Grace Hopper'])
        ->and($speakers->first()->pivot)->toBeInstanceOf(EventSpeaker::class)
        ->and($speakers->first()->pivot->role)->toBe('ponente')
        ->and($speakers->first()->pivot->sort_order)->toBe(1)
        ->and($speaker->events()->first()->pivot)->toBeInstanceOf(EventSpeaker::class);
});
