<?php

use App\Enums\MemberRole;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'role:admin,web'])
        ->get('/_test/role-protected', fn () => 'ok');
});

test('guests are redirected to login', function () {
    $this->get('/_test/role-protected')->assertRedirect(route('login'));
});

test('members with an allowed role can access the route', function (MemberRole $role) {
    $member = Member::factory()->role($role)->create();

    $this->actingAs($member)->get('/_test/role-protected')->assertOk();
})->with([MemberRole::Admin, MemberRole::Web]);

test('members without an allowed role are forbidden', function (MemberRole $role) {
    $member = Member::factory()->role($role)->create();

    $this->actingAs($member)->get('/_test/role-protected')->assertForbidden();
})->with([MemberRole::Events, MemberRole::Finance, MemberRole::Marketing]);

test('authenticated users that are not members are forbidden', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/_test/role-protected')->assertForbidden();
});
