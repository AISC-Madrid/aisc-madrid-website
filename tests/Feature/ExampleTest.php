<?php

test('returns a successful response', function () {
    $response = $this->get(route('home', ['locale' => 'es']));

    $response->assertOk();
});

test('public pages identify their active language', function (string $locale) {
    $this->get(route('home', ['locale' => $locale]))
        ->assertOk()
        ->assertSee('<html lang="'.$locale.'">', false);
})->with(['en', 'es']);
