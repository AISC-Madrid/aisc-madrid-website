<?php

use App\Models\Contact;

test('a contact can be created with factory', function () {
    $contact = Contact::factory()->create();

    expect($contact->exists)->toBeTrue();
});
