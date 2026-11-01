<?php

use App\Models\Contact;

test('a contact can be created with factory', function () {
    $contact = Contact::factory()->create();

    expect($contact->exists)->toBeTrue();
});

test('the greeting name falls back to the first word of the full name', function (?string $greetingName, string $fullName, string $expected) {
    $contact = Contact::factory()->make(['greeting_name' => $greetingName, 'full_name' => $fullName]);

    expect($contact->greetingName())->toBe($expected);
})->with([
    'greeting name set' => ['Ada', 'Augusta Ada King', 'Ada'],
    'first word' => [null, 'Grace Hopper', 'Grace'],
    'leading spaces' => [null, '  Alan Turing', 'Alan'],
    'single word' => ['', 'Hedy', 'Hedy'],
]);
