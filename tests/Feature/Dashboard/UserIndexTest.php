<?php

use App\Enums\MemberRole;
use App\Models\FormSubmission;
use App\Models\Member;

test('guests are redirected to login', function () {
    $this->get(route('dashboard.users.index'))->assertRedirect(route('login'));
});

test('admins and events members can list the users', function (MemberRole $role) {
    $member = Member::factory()->role($role)->create();
    $subscribed = FormSubmission::factory()->create([
        'full_name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'submitted_at' => '2026-03-14 10:30:00',
    ]);
    $unsubscribed = FormSubmission::factory()->withoutNewsletter()->create([
        'full_name' => 'Alan Turing',
    ]);

    $this->actingAs($member)
        ->get(route('dashboard.users.index'))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertSee('ada@example.com')
        ->assertSee('14/03/2026 10:30')
        ->assertSee('Alan Turing')
        ->assertSee('Sí')
        ->assertSee('No')
        ->assertDontSee($subscribed->unsubscribe_token)
        ->assertDontSee($unsubscribed->unsubscribe_token);
})->with([MemberRole::Admin, MemberRole::Events]);

test('members without permission get a 403', function (MemberRole $role) {
    $member = Member::factory()->role($role)->create();

    $this->actingAs($member)
        ->get(route('dashboard.users.index'))
        ->assertForbidden();
})->with([MemberRole::Web, MemberRole::Finance, MemberRole::Marketing]);

test('users are paginated 25 per page, newest first', function () {
    $member = Member::factory()->role(MemberRole::Admin)->create();

    foreach (range(1, 26) as $number) {
        FormSubmission::factory()->create([
            'full_name' => sprintf('Person %02d', $number),
            'submitted_at' => now()->subDays(30 - $number),
        ]);
    }

    $this->actingAs($member)
        ->get(route('dashboard.users.index'))
        ->assertOk()
        ->assertViewHas('users', fn ($users) => $users->count() === 25 && $users->total() === 26)
        ->assertSee('Person 26')
        ->assertDontSee('Person 01');

    $this->actingAs($member)
        ->get(route('dashboard.users.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Person 01')
        ->assertDontSee('Person 26');
});

test('users can be searched by name or email', function () {
    $member = Member::factory()->role(MemberRole::Admin)->create();
    FormSubmission::factory()->create(['full_name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    FormSubmission::factory()->create(['full_name' => 'Alan Turing', 'email' => 'turing@example.org']);

    $this->actingAs($member)
        ->get(route('dashboard.users.index', ['search' => 'lovelace']))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertDontSee('Alan Turing');

    $this->actingAs($member)
        ->get(route('dashboard.users.index', ['search' => 'turing@example']))
        ->assertOk()
        ->assertSee('Alan Turing')
        ->assertDontSee('Ada Lovelace');
});

test('the search is kept in the pagination links', function () {
    $member = Member::factory()->role(MemberRole::Admin)->create();
    FormSubmission::factory()->count(26)->create(['full_name' => 'Ada Lovelace']);

    $this->actingAs($member)
        ->get(route('dashboard.users.index', ['search' => 'ada']))
        ->assertOk()
        ->assertSee('search=ada', false);
});

test('an empty search result shows a message', function () {
    $member = Member::factory()->role(MemberRole::Admin)->create();
    FormSubmission::factory()->create(['full_name' => 'Ada Lovelace']);

    $this->actingAs($member)
        ->get(route('dashboard.users.index', ['search' => 'nobody']))
        ->assertOk()
        ->assertSee('No hay usuarios que coincidan con la búsqueda.');
});

test('the sidebar shows the users link only to members who can open it', function () {
    $admin = Member::factory()->role(MemberRole::Admin)->create();
    $marketing = Member::factory()->role(MemberRole::Marketing)->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSee(route('dashboard.users.index'));

    $this->actingAs($marketing)
        ->get(route('dashboard'))
        ->assertDontSee(route('dashboard.users.index'));
});
