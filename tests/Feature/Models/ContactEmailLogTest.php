<?php

use App\Models\ContactEmailLog;

test('a contact email log can be created with factory', function () {
    $log = ContactEmailLog::factory()->create();

    expect($log->exists)->toBeTrue();
});
