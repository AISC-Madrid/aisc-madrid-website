<?php

use App\Models\Collaboration;

test('a collaboration can be created with factory', function () {
    $collaboration = Collaboration::factory()->create();

    expect($collaboration->exists)->toBeTrue();
});
