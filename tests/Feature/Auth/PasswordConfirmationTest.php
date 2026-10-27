<?php

use App\Models\Member;

test('confirm password screen can be rendered', function () {
    $member = Member::factory()->create();

    $response = $this->actingAs($member)->get(route('password.confirm'));

    $response->assertOk();
});

test('password can be confirmed', function () {
    $member = Member::factory()->create();

    $response = $this->actingAs($member)->post(route('password.confirm.store'), [
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('auth.password_confirmed_at');
});

test('password is not confirmed with invalid password', function () {
    $member = Member::factory()->create();

    $response = $this->actingAs($member)->post(route('password.confirm.store'), [
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors();
    $response->assertSessionMissing('auth.password_confirmed_at');
});
