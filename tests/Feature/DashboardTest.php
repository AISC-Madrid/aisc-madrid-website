<?php

use App\Enums\MemberRole;
use App\Models\Event;
use App\Models\FormSubmission;
use App\Models\Member;
use App\Models\Project;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The legacy event_registrations and recruiting tables have no Laravel migration yet;
 * create them with the columns the old site writes so the dashboard can count them.
 */
function createLegacyDashboardTables(): void
{
    Schema::create('event_registrations', function (Blueprint $table) {
        $table->id();
        $table->integer('event_id');
        $table->string('name');
        $table->string('email');
    });

    Schema::create('recruiting', function (Blueprint $table) {
        $table->id();
        $table->string('full_name');
        $table->string('email');
        $table->string('cohort');
    });
}

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated members can visit the dashboard', function () {
    $member = Member::factory()->create(['full_name' => 'Ada Lovelace']);
    $this->actingAs($member);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Hola, Ada');
    $response->assertSee('Ada Lovelace');
    $response->assertSee($member->mail);
});

test('dashboard shows user and newsletter statistics', function () {
    FormSubmission::factory()->count(3)->create(['submitted_at' => now()->subDays(2)]);
    FormSubmission::factory()->withoutNewsletter()->create(['submitted_at' => now()->subDays(60)]);

    $this->actingAs(Member::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('users', [
            'total' => 4,
            'newLast30Days' => 3,
            'newsletter' => 3,
            'unsubscribed' => 1,
            'newsletterRate' => 75,
        ])
        ->assertSee('Usuarios registrados')
        ->assertSee('+3')
        ->assertSee('75% del total · 1 bajas');
});

test('dashboard shows event and community statistics', function () {
    Event::factory()->upcoming()->count(2)->create();
    Event::factory()->past()->count(3)->create();
    Member::factory()->count(2)->create(['board' => true]);
    Member::factory()->inactive()->create();
    Project::factory()->count(4)->create();

    $this->actingAs(Member::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('events', fn (array $events) => $events['upcoming'] === 2 && $events['past'] === 3)
        ->assertViewHas('community', [
            'activeMembers' => 3,
            'boardMembers' => 2,
            'projects' => 4,
        ]);
});

test('registrations and recruiting are hidden when the legacy tables do not exist', function () {
    $this->actingAs(Member::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('events', fn (array $events) => $events['totalRegistrations'] === null)
        ->assertViewHas('recruiting', fn (array $recruiting) => $recruiting['applicants'] === null)
        ->assertSee('Proceso 2026-27-1C');
});

test('dashboard counts registrations and applicants from the legacy tables', function () {
    createLegacyDashboardTables();
    config(['aisc.recruiting_cohort' => '2026-27-1C']);

    $event = Event::factory()->upcoming()->create(['title_es' => 'Taller de IA']);
    DB::table('event_registrations')->insert([
        ['event_id' => $event->id, 'name' => 'Ada', 'email' => 'ada@example.com'],
        ['event_id' => $event->id, 'name' => 'Alan', 'email' => 'alan@example.com'],
    ]);
    DB::table('recruiting')->insert([
        ['full_name' => 'Ada', 'email' => 'ada@example.com', 'cohort' => '2026-27-1C'],
        ['full_name' => 'Grace', 'email' => 'grace@example.com', 'cohort' => '2025-26-2C'],
    ]);

    $this->actingAs(Member::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('events', fn (array $events) => $events['totalRegistrations'] === 2)
        ->assertViewHas('recruiting', fn (array $recruiting) => $recruiting['applicants'] === 1)
        ->assertViewHas('registrationsByEvent', fn ($registrations) => $registrations->get($event->id) === 2)
        ->assertSee('2 inscripciones en total')
        ->assertSee('Taller de IA');
});

test('sign-ups are grouped by month for the last 12 months', function () {
    FormSubmission::factory()->count(2)->create(['submitted_at' => now()->startOfMonth()->addHour()]);
    FormSubmission::factory()->create(['submitted_at' => now()->startOfMonth()->subMonths(3)->addHour()]);
    FormSubmission::factory()->create(['submitted_at' => now()->subMonths(13)]);

    $this->actingAs(Member::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('signupsByMonth', function ($months) {
            return $months->count() === 12
                && $months->last()['total'] === 2
                && $months->get(8)['total'] === 1
                && $months->sum('total') === 3;
        });
});

test('dashboard lists the 5 latest subscribers without their unsubscribe token', function () {
    $oldest = FormSubmission::factory()->create(['full_name' => 'Oldest Person', 'submitted_at' => now()->subYear()]);
    $latest = FormSubmission::factory()->count(5)->sequence(
        fn ($sequence) => ['full_name' => "Recent {$sequence->index}", 'submitted_at' => now()->subDays($sequence->index)],
    )->create();

    $response = $this->actingAs(Member::factory()->create())->get(route('dashboard'))->assertOk();

    $response->assertSeeInOrder(['Recent 0', 'Recent 1', 'Recent 2', 'Recent 3', 'Recent 4'])
        ->assertDontSee('Oldest Person')
        ->assertDontSee($oldest->unsubscribe_token);

    foreach ($latest as $subscriber) {
        $response->assertDontSee($subscriber->unsubscribe_token);
    }
});

test('the link to all users is only shown to members who can open it', function () {
    $this->actingAs(Member::factory()->role(MemberRole::Admin)->create())
        ->get(route('dashboard'))
        ->assertSee('Ver todos');

    $this->actingAs(Member::factory()->role(MemberRole::Finance)->create())
        ->get(route('dashboard'))
        ->assertDontSee('Ver todos');
});

test('the sidebar shows the AISC logo', function () {
    $this->actingAs(Member::factory()->create())
        ->get(route('dashboard'))
        ->assertSee('images/logos/aisc-logo-color.svg', false)
        ->assertSee('AISC Madrid')
        ->assertSee('https://github.com/AISC-Madrid/aisc-madrid-website', false)
        ->assertDontSee('laravel/livewire-starter-kit', false);
});

test('members with a picture use it as their profile image', function () {
    $member = Member::factory()->create(['image_path' => 'members/ada.webp']);

    $this->actingAs($member)
        ->get(route('dashboard'))
        ->assertSee('src="https://s3.aiscmadrid.com/aisc-public/members/ada.webp"', false);
});

test('members without a picture show their initials', function () {
    $member = Member::factory()->create(['full_name' => 'Ada Lovelace', 'image_path' => null]);

    $this->actingAs($member)
        ->get(route('dashboard'))
        ->assertSee('AL')
        ->assertDontSee('aisc-public/members', false);
});
