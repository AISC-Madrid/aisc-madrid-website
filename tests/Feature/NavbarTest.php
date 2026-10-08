<?php

test('navbar logo uses the same size as the footer logo', function () {
    $this->get(route('home', ['locale' => 'es']))
        ->assertOk()
        ->assertSee('src="'.asset('images/logos/aisc-logo-color.svg').'"', false)
        ->assertSee('class="h-12 w-auto"', false)
        ->assertDontSee('class="h-15 w-auto"', false);
});
