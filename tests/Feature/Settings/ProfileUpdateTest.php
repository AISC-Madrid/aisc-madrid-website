<?php

use App\Models\AlumniHonor;
use App\Models\Member;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $member = Member::factory()->create(['full_name' => 'Ada Lovelace']);

    $this->actingAs($member)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertSee($member->mail)
        ->assertSee('Deactivate account');
});

test('profile information can be updated', function () {
    $member = Member::factory()->create();

    $this->actingAs($member);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $member->refresh();

    expect($member->full_name)->toEqual('Test User');
    expect($member->mail)->toEqual('test@example.com');
});

test('profile can be saved with an unchanged email', function () {
    $member = Member::factory()->create();

    $this->actingAs($member);

    Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $member->mail)
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($member->refresh()->full_name)->toEqual('Test User');
});

test('email must not belong to another member', function () {
    $otherMember = Member::factory()->create();
    $member = Member::factory()->create();

    $this->actingAs($member);

    Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $otherMember->mail)
        ->call('updateProfileInformation')
        ->assertHasErrors(['email' => 'unique']);
});

test('name and email must fit the members table columns', function () {
    $member = Member::factory()->create();

    $this->actingAs($member);

    Livewire::test('pages::settings.profile')
        ->set('name', str_repeat('a', 51))
        ->set('email', str_repeat('a', 40).'@example.com')
        ->call('updateProfileInformation')
        ->assertHasErrors(['name' => 'max', 'email' => 'max']);
});

test('member can deactivate their account', function () {
    $member = Member::factory()->create();
    AlumniHonor::factory()->create(['member_id' => $member->id]);

    $this->actingAs($member);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $member->refresh();

    expect($member->active)->toBeFalse();
    expect($member->alumniHonor)->not->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('deactivated member can not log in again', function () {
    $member = Member::factory()->create();

    $this->actingAs($member);

    Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $this->post(route('login.store'), [
        'email' => $member->mail,
        'password' => 'password',
    ])->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('correct password must be provided to deactivate account', function () {
    $member = Member::factory()->create();

    $this->actingAs($member);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($member->refresh()->active)->toBeTrue();
});
