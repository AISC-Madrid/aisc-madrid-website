<?php

use App\Models\Event;
use App\Models\EventRegistration;

test('an event registration can be created with factory', function () {
    $registration = EventRegistration::factory()->create();

    expect($registration->exists)->toBeTrue()
        ->and($registration->registration_date)->not->toBeNull()
        ->and($registration->hasAttended())->toBeFalse();
});

test('an event has many registrations', function () {
    $event = Event::factory()->create();
    EventRegistration::factory()->count(2)->for($event)->create();
    EventRegistration::factory()->attended()->for($event)->create();

    expect($event->registrations)->toHaveCount(3)
        ->and($event->registrations()->attended()->count())->toBe(1)
        ->and($event->registrations->first()->event->is($event))->toBeTrue();
});

test('registrations are counted per event', function () {
    $busy = Event::factory()->create();
    $quiet = Event::factory()->create();
    $empty = Event::factory()->create();
    EventRegistration::factory()->count(3)->for($busy)->create();
    EventRegistration::factory()->for($quiet)->create();

    $counts = EventRegistration::countsByEvent(collect([$busy->id, $quiet->id, $empty->id]));

    expect($counts->get($busy->id))->toBe(3)
        ->and($counts->get($quiet->id))->toBe(1)
        ->and($counts->has($empty->id))->toBeFalse()
        ->and(EventRegistration::countsByEvent(collect()))->toBeEmpty();
});

test('registrations are deleted with their event', function () {
    $registration = EventRegistration::factory()->create();

    $registration->event->delete();

    expect(EventRegistration::find($registration->id))->toBeNull();
});
