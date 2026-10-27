<?php

use App\Models\Member;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('members can authenticate using the login screen', function () {
    $member = Member::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $member->mail,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($member);
});

test('members with a legacy php password_hash can authenticate', function () {
    $member = Member::factory()->create([
        'password_hash' => password_hash('legacy-secret', PASSWORD_DEFAULT),
    ]);

    $this->post(route('login.store'), [
        'email' => $member->mail,
        'password' => 'legacy-secret',
    ])->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($member);
});

test('members can not authenticate with invalid password', function () {
    $member = Member::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $member->mail,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('inactive members can not authenticate', function () {
    $member = Member::factory()->inactive()->create();

    $response = $this->post(route('login.store'), [
        'email' => $member->mail,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('requesting remember me does not break login', function () {
    $member = Member::factory()->create();

    $this->post(route('login.store'), [
        'email' => $member->mail,
        'password' => 'password',
        'remember' => 'on',
    ])->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($member);
});

test('members with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
});

test('members can logout', function () {
    $member = Member::factory()->create();

    $response = $this->actingAs($member)->post(route('logout'));

    $response->assertRedirect('/');

    $this->assertGuest();
});
