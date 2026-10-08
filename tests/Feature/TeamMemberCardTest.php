<?php

use App\Models\Member;

test('team member card is a square photo that links to the member linkedin', function () {
    $member = Member::factory()->create([
        'full_name' => 'Ada Lovelace',
        'active' => 'yes',
        'socials' => 'https://www.linkedin.com/in/ada-lovelace/',
        'image_path' => 'https://s3.aiscmadrid.com/aisc-public/members/ada.webp',
    ]);

    $this->get(route('team.index', ['locale' => 'es']))
        ->assertOk()
        ->assertSee('href="https://www.linkedin.com/in/ada-lovelace/"', false)
        ->assertSee('src="https://s3.aiscmadrid.com/aisc-public/members/ada.webp"', false)
        ->assertSee('aspect-square', false)
        ->assertSee('hover:scale-105', false)
        ->assertSee($member->full_name)
        ->assertDontSee('rounded-full ring-2', false);
});

test('team member card without a valid linkedin url is not a link', function () {
    Member::factory()->create([
        'full_name' => 'Grace Hopper',
        'active' => 'yes',
        'socials' => null,
    ]);

    $this->get(route('team.index', ['locale' => 'es']))
        ->assertOk()
        ->assertSee('Grace Hopper')
        ->assertDontSee('href="#"', false);
});
