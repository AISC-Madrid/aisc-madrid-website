<?php

use App\Actions\StoreWebpImage;
use App\Enums\MemberRole;
use App\Models\AlumniHonor;
use App\Models\Member;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * @return array<string, mixed>
 */
function validTeamMemberInput(array $overrides = []): array
{
    return [
        'full_name' => 'Ada Lovelace',
        'mail' => 'ada@example.com',
        'password' => 'secret123',
        'position_es' => 'Presidenta',
        'position_en' => 'President',
        'phone' => '600123123',
        'dni' => '12345678Z',
        'socials' => 'https://www.linkedin.com/in/ada',
        'role' => 'web',
        'active' => '1',
        'board' => '1',
        ...$overrides,
    ];
}

function teamAdmin(): Member
{
    return Member::factory()->role(MemberRole::Finance)->create();
}

beforeEach(function () {
    Storage::fake('s3');
});

test('visitors are redirected to login', function () {
    $this->get(route('dashboard.team-members.index'))->assertRedirect(route('login'));
});

test('admins and finance members can list the team', function (MemberRole $role) {
    $member = Member::factory()->create(['full_name' => 'Alan Turing', 'position_es' => 'Tesorero', 'dni' => '87654321X']);
    Member::factory()->honorMember()->create(['full_name' => 'Grace Hopper']);

    $this->actingAs(Member::factory()->role($role)->create())
        ->get(route('dashboard.team-members.index'))
        ->assertOk()
        ->assertSee('Alan Turing')
        ->assertSee('Tesorero')
        ->assertSee('87654321X')
        ->assertSee('Grace Hopper')
        ->assertSee('Honor')
        ->assertSee(route('dashboard.team-members.edit', $member), false)
        ->assertSee(route('dashboard.team-members.destroy', $member), false);
})->with([MemberRole::Admin, MemberRole::Finance]);

test('members without permission get a 403', function (MemberRole $role) {
    $teamMember = Member::factory()->create();
    $member = Member::factory()->role($role)->create();

    $this->actingAs($member)->get(route('dashboard.team-members.index'))->assertForbidden();
    $this->actingAs($member)->post(route('dashboard.team-members.store'), validTeamMemberInput())->assertForbidden();
    $this->actingAs($member)->delete(route('dashboard.team-members.destroy', $teamMember))->assertForbidden();

    expect(Member::find($teamMember->id))->not->toBeNull();
})->with([MemberRole::Events, MemberRole::Web, MemberRole::Marketing]);

test('a member can be created', function () {
    $this->actingAs(teamAdmin())
        ->post(route('dashboard.team-members.store'), validTeamMemberInput())
        ->assertRedirect(route('dashboard.team-members.index'))
        ->assertSessionHas('status');

    $member = Member::where('mail', 'ada@example.com')->firstOrFail();

    expect($member->full_name)->toBe('Ada Lovelace')
        ->and($member->role)->toBe(MemberRole::Web)
        ->and($member->board)->toBeTrue()
        ->and($member->active)->toBeTrue()
        ->and($member->alumniHonor)->toBeNull()
        ->and(Hash::check('secret123', $member->password_hash))->toBeTrue();
});

test('a member can be created as honor member with a photo', function () {
    $this->mock(StoreWebpImage::class)
        ->shouldReceive('__invoke')
        ->once()
        ->withArgs(fn (UploadedFile $file, string $folder) => str_starts_with($folder, 'members/'))
        ->andReturn('members/1/img_test.webp');

    $this->actingAs(teamAdmin())
        ->post(route('dashboard.team-members.store'), validTeamMemberInput([
            'active' => '0',
            'is_honor' => '1',
            'graduation_year' => '26',
            'honor_quote' => 'Imagination is the discovering faculty.',
            'image' => UploadedFile::fake()->image('ada.jpg'),
        ]))
        ->assertSessionHasNoErrors();

    $member = Member::where('mail', 'ada@example.com')->firstOrFail();

    expect($member->image_path)->toBe('members/1/img_test.webp')
        ->and($member->active)->toBeFalse()
        ->and($member->alumniHonor->graduation_year)->toBe(26)
        ->and($member->alumniHonor->honor_quote)->toBe('Imagination is the discovering faculty.');
});

test('creating a member validates the input', function () {
    Member::factory()->create(['mail' => 'taken@example.com']);

    $this->actingAs(teamAdmin())
        ->post(route('dashboard.team-members.store'), validTeamMemberInput([
            'full_name' => '',
            'mail' => 'taken@example.com',
            'password' => '',
            'role' => 'boss',
            'dni' => '1234567890',
        ]))
        ->assertSessionHasErrors(['full_name', 'mail', 'password', 'role', 'dni']);
});

test('a member can be edited keeping the password and removing the honor', function () {
    $member = Member::factory()->honorMember()->create(['mail' => 'ada@example.com']);
    $originalHash = $member->password_hash;

    $this->actingAs(teamAdmin())
        ->get(route('dashboard.team-members.edit', $member))
        ->assertOk()
        ->assertSee('ada@example.com');

    $this->actingAs(teamAdmin())
        ->put(route('dashboard.team-members.update', $member), validTeamMemberInput([
            'full_name' => 'Ada King',
            'password' => '',
            'role' => 'admin',
            'board' => '0',
        ]))
        ->assertRedirect(route('dashboard.team-members.index'));

    $member->refresh();

    expect($member->full_name)->toBe('Ada King')
        ->and($member->role)->toBe(MemberRole::Admin)
        ->and($member->board)->toBeFalse()
        ->and($member->password_hash)->toBe($originalHash)
        ->and(AlumniHonor::where('member_id', $member->id)->exists())->toBeFalse();
});

test('a member can be deleted', function () {
    $member = Member::factory()->honorMember()->create();

    $this->actingAs(teamAdmin())
        ->delete(route('dashboard.team-members.destroy', $member))
        ->assertRedirect(route('dashboard.team-members.index'));

    expect(Member::find($member->id))->toBeNull()
        ->and(AlumniHonor::where('member_id', $member->id)->exists())->toBeFalse();
});

test('members cannot delete themselves', function () {
    $admin = teamAdmin();

    $this->actingAs($admin)
        ->delete(route('dashboard.team-members.destroy', $admin))
        ->assertRedirect(route('dashboard.team-members.index'))
        ->assertSessionHasErrors('member');

    expect(Member::find($admin->id))->not->toBeNull();
});

test('the sidebar shows the new tabs only to allowed roles', function () {
    $this->actingAs(Member::factory()->role(MemberRole::Admin)->create())
        ->get(route('dashboard'))
        ->assertSee(route('dashboard.guests.index'), false)
        ->assertSee(route('dashboard.team-members.index'), false);

    $this->actingAs(Member::factory()->role(MemberRole::Marketing)->create())
        ->get(route('dashboard'))
        ->assertDontSee(route('dashboard.guests.index'), false)
        ->assertDontSee(route('dashboard.team-members.index'), false);
});
