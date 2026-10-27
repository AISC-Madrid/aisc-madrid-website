<?php

use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {});

test('security settings page can be rendered', function () {
    $member = Member::factory()->create();

    $response = $this->actingAs($member)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'));

    $response->assertOk();
});

test('security settings page requires password confirmation when enabled', function () {
    $member = Member::factory()->create();

    $response = $this->actingAs($member)
        ->get(route('security.edit'));

    $response->assertRedirect(route('password.confirm'));
});

test('security settings page renders without two factor when feature is disabled', function () {
    config(['fortify.features' => []]);

    $member = Member::factory()->create();

    $this->actingAs($member)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertOk()
        ->assertSee('Update password')
        ->assertDontSee('Manage your passkeys for passwordless sign-in')
        ->assertDontSee('Add a passkey to sign in without a password')
        ->assertDontSee('Two-factor authentication');
});

test('two factor authentication disabled when confirmation abandoned between requests', function () {});

test('password can be updated', function () {
    $member = Member::factory()->create();

    $this->actingAs($member);

    $response = Livewire::test('pages::settings.security')
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasNoErrors();

    expect(Hash::check('new-password', $member->refresh()->password_hash))->toBeTrue();
});

test('updated password can be used to log in', function () {
    $member = Member::factory()->create();

    $this->actingAs($member);

    Livewire::test('pages::settings.security')
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    auth()->logout();

    $this->post(route('login.store'), [
        'email' => $member->mail,
        'password' => 'new-password',
    ])->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($member);
});

test('correct password must be provided to update password', function () {
    $member = Member::factory()->create();

    $this->actingAs($member);

    $response = Livewire::test('pages::settings.security')
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasErrors(['current_password']);

    expect(Hash::check('password', $member->refresh()->password_hash))->toBeTrue();
});
