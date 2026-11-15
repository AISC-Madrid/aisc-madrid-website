<?php

use App\Enums\MemberRole;
use App\Models\Contact;
use App\Models\ContactEmailLog;
use App\Models\Member;

/**
 * @return array<string, mixed>
 */
function validContactInput(array $overrides = []): array
{
    return [
        'full_name' => 'Ada Lovelace',
        'greeting_name' => 'Ada',
        'email' => 'ada@example.com',
        'category' => 'Empresa',
        'organization' => 'Analytical Engines',
        'notes' => 'Contactar en septiembre.',
        'active' => '1',
        ...$overrides,
    ];
}

function contactsManager(): Member
{
    return Member::factory()->role(MemberRole::Marketing)->create();
}

test('guests are redirected to login', function () {
    $this->get(route('dashboard.contacts.index'))->assertRedirect(route('login'));
});

test('contacts are listed with name, email, category, organization and active flag', function () {
    Contact::factory()->create([
        'full_name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'category' => 'Empresa',
        'organization' => 'Analytical Engines',
        'active' => true,
    ]);
    Contact::factory()->create(['full_name' => 'Alan Turing', 'email' => 'alan@example.com', 'active' => false]);

    $this->actingAs(contactsManager())
        ->get(route('dashboard.contacts.index'))
        ->assertOk()
        ->assertSeeInOrder(['Ada Lovelace', 'ada@example.com', 'Empresa', 'Analytical Engines', 'Sí'])
        ->assertSee('Alan Turing')
        ->assertSee('No');
});

test('contacts can be searched by name, email or organization', function () {
    Contact::factory()->create(['full_name' => 'Ada Lovelace', 'email' => 'ada@example.com', 'organization' => 'Analytical Engines']);
    Contact::factory()->create(['full_name' => 'Alan Turing', 'email' => 'alan@example.com', 'organization' => 'Bletchley']);

    $this->actingAs(contactsManager())
        ->get(route('dashboard.contacts.index', ['search' => 'bletchley']))
        ->assertSee('Alan Turing')
        ->assertDontSee('Ada Lovelace');
});

test('contacts can be filtered by category', function () {
    Contact::factory()->create(['full_name' => 'Ada Lovelace', 'category' => 'Empresa']);
    Contact::factory()->create(['full_name' => 'Alan Turing', 'category' => 'Universidad']);

    $this->actingAs(contactsManager())
        ->get(route('dashboard.contacts.index', ['category' => 'Universidad']))
        ->assertSee('Alan Turing')
        ->assertDontSee('Ada Lovelace');
});

test('the create form can be rendered', function () {
    $this->actingAs(contactsManager())
        ->get(route('dashboard.contacts.create'))
        ->assertOk()
        ->assertSee('Crear nuevo contacto');
});

test('contacts can be created', function () {
    $this->actingAs(contactsManager())
        ->post(route('dashboard.contacts.store'), validContactInput())
        ->assertRedirect(route('dashboard.contacts.index'))
        ->assertSessionHas('status');

    expect(Contact::sole())
        ->full_name->toBe('Ada Lovelace')
        ->email->toBe('ada@example.com')
        ->category->toBe('Empresa')
        ->active->toBeTrue();
});

test('creating a contact validates the input and requires a unique email', function () {
    Contact::factory()->create(['email' => 'ada@example.com']);

    $this->actingAs(contactsManager())
        ->post(route('dashboard.contacts.store'), validContactInput(['full_name' => '', 'email' => 'ada@example.com']))
        ->assertSessionHasErrors(['full_name', 'email']);

    $this->actingAs(contactsManager())
        ->post(route('dashboard.contacts.store'), validContactInput(['email' => 'no-es-un-email']))
        ->assertSessionHasErrors(['email']);

    expect(Contact::count())->toBe(1);
});

test('the edit form shows the current values', function () {
    $contact = Contact::factory()->create(['full_name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    $this->actingAs(contactsManager())
        ->get(route('dashboard.contacts.edit', $contact))
        ->assertOk()
        ->assertSee('Editar contacto')
        ->assertSee('value="Ada Lovelace"', false)
        ->assertSee('value="ada@example.com"', false);
});

test('contacts can be updated and keep their own email', function () {
    $contact = Contact::factory()->create(['email' => 'ada@example.com', 'active' => true]);

    $this->actingAs(contactsManager())
        ->put(route('dashboard.contacts.update', $contact), validContactInput(['full_name' => 'Ada King', 'active' => '0']))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard.contacts.index'));

    expect($contact->refresh())
        ->full_name->toBe('Ada King')
        ->email->toBe('ada@example.com')
        ->active->toBeFalse();
});

test('updating a contact cannot use another contact email', function () {
    Contact::factory()->create(['email' => 'alan@example.com']);
    $contact = Contact::factory()->create(['email' => 'ada@example.com']);

    $this->actingAs(contactsManager())
        ->put(route('dashboard.contacts.update', $contact), validContactInput(['email' => 'alan@example.com']))
        ->assertSessionHasErrors('email');
});

test('contacts can be deleted together with their email logs', function () {
    $contact = Contact::factory()->create(['full_name' => 'Contacto a borrar']);
    ContactEmailLog::factory()->create(['contact_id' => $contact->id, 'template_name' => 'welcome', 'sent_at' => now()]);

    $this->actingAs(contactsManager())
        ->delete(route('dashboard.contacts.destroy', $contact))
        ->assertRedirect(route('dashboard.contacts.index'))
        ->assertSessionHas('status', 'Contacto «Contacto a borrar» eliminado.');

    expect(Contact::find($contact->id))->toBeNull();
    expect(ContactEmailLog::where('contact_id', $contact->id)->count())->toBe(0);
});

test('the contact page shows the emails sent to the contact', function () {
    $contact = Contact::factory()->create(['full_name' => 'Ada Lovelace']);
    ContactEmailLog::factory()->create(['contact_id' => $contact->id, 'template_name' => 'plantilla_patrocinio', 'sent_at' => '2026-10-15 12:30:00']);

    $this->actingAs(contactsManager())
        ->get(route('dashboard.contacts.show', $contact))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertSee('plantilla_patrocinio')
        ->assertSee('15/10/2026 12:30');
});

test('the contact page says when no email has been sent', function () {
    $contact = Contact::factory()->create();

    $this->actingAs(contactsManager())
        ->get(route('dashboard.contacts.show', $contact))
        ->assertOk()
        ->assertSee('Todavía no se ha enviado ningún email');
});

test('admins can also manage contacts', function () {
    $this->actingAs(Member::factory()->role(MemberRole::Admin)->create())
        ->get(route('dashboard.contacts.index'))
        ->assertOk();
});

test('members without permission get a 403', function (string $method, string $route) {
    $contact = Contact::factory()->create();
    $member = Member::factory()->role(MemberRole::Events)->create();
    $global = in_array($route, ['dashboard.contacts.index', 'dashboard.contacts.create', 'dashboard.contacts.store']);

    $this->actingAs($member)
        ->call($method, route($route, $global ? [] : $contact))
        ->assertForbidden();

    expect(Contact::find($contact->id))->not->toBeNull();
})->with([
    ['GET', 'dashboard.contacts.index'],
    ['GET', 'dashboard.contacts.create'],
    ['POST', 'dashboard.contacts.store'],
    ['GET', 'dashboard.contacts.show'],
    ['GET', 'dashboard.contacts.edit'],
    ['PUT', 'dashboard.contacts.update'],
    ['DELETE', 'dashboard.contacts.destroy'],
]);
