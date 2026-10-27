<?php

use App\Models\FormSubmission;
use App\Models\Member;

/**
 * @return array<string, string>
 */
function newsletterInput(array $overrides = []): array
{
    return [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'consent' => '1',
        ...$overrides,
    ];
}

test('the home page shows the newsletter form below the team section', function (string $locale, string $title) {
    $this->get(route('home', ['locale' => $locale]))
        ->assertOk()
        ->assertSeeInOrder(['id="team"', 'id="newsletter"', $title], false)
        ->assertSee('action="'.route('newsletter.store', ['locale' => $locale]).'"', false)
        ->assertSee('name="consent"', false)
        ->assertSee(route('terms', ['locale' => $locale]), false);
})->with([
    ['es', '¡Mantente al tanto!'],
    ['en', 'Don&#039;t miss a thing!'],
]);

test('visitors can join the newsletter', function () {
    $this->post(route('newsletter.store', ['locale' => 'es']), newsletterInput(['email' => '  Ada@Example.com ']))
        ->assertRedirect(route('home', ['locale' => 'es']).'#newsletter')
        ->assertSessionHas('newsletter_subscribed', true);

    $submission = FormSubmission::sole();

    expect($submission)
        ->full_name->toBe('Ada Lovelace')
        ->email->toBe('ada@example.com')
        ->newsletter->toBeTrue();
    expect($submission->unsubscribe_token)->toHaveLength(32);
});

test('after joining, the home page shows the success message', function () {
    $this->followingRedirects()
        ->post(route('newsletter.store', ['locale' => 'es']), newsletterInput())
        ->assertOk()
        ->assertSee('data-test="newsletter-success"', false)
        ->assertSee('¡Ya eres parte de AISC Madrid!')
        ->assertSee('chat.whatsapp.com', false)
        ->assertDontSee('data-test="newsletter-form"', false);
});

test('the form validates name, email and consent', function () {
    $this->from(route('home', ['locale' => 'es']))
        ->post(route('newsletter.store', ['locale' => 'es']), [
            'name' => '',
            'email' => 'not-an-email',
        ])
        ->assertRedirect(route('home', ['locale' => 'es']).'#newsletter')
        ->assertSessionHasErrors([
            'name' => 'Por favor, introduce tu nombre y apellido(s).',
            'email' => 'Por favor, introduce un correo válido.',
            'consent' => 'Debes dar tu consentimiento para continuar.',
        ]);

    expect(FormSubmission::count())->toBe(0);
});

test('validation errors are shown in english on the english site', function () {
    $this->post(route('newsletter.store', ['locale' => 'en']), newsletterInput(['consent' => null]))
        ->assertSessionHasErrors(['consent' => 'You must give your consent to continue.']);
});

test('an email that is already subscribed is rejected', function () {
    FormSubmission::factory()->create(['email' => 'ada@example.com', 'newsletter' => true]);

    $this->post(route('newsletter.store', ['locale' => 'es']), newsletterInput())
        ->assertSessionHasErrors(['email' => 'Este correo ya está suscrito a la newsletter.']);

    expect(FormSubmission::count())->toBe(1);
});

test('someone who had unsubscribed can join again', function () {
    $submission = FormSubmission::factory()->withoutNewsletter()->create(['email' => 'ada@example.com']);

    $this->post(route('newsletter.store', ['locale' => 'es']), newsletterInput())
        ->assertSessionHasNoErrors()
        ->assertSessionHas('newsletter_subscribed', true);

    expect(FormSubmission::count())->toBe(1);
    expect($submission->refresh()->newsletter)->toBeTrue();
});

test('the navbar has a join button to the newsletter form instead of the login button', function () {
    $this->get(route('events.index', ['locale' => 'es']))
        ->assertOk()
        ->assertSee('data-test="nav-join"', false)
        ->assertSee(route('home', ['locale' => 'es']).'#newsletter', false)
        ->assertSee('Unirme')
        ->assertDontSee('Inicia sesión');
});

test('the footer has the discreet members area link', function () {
    $this->get(route('home', ['locale' => 'es']))
        ->assertSee('data-test="footer-members-area"', false)
        ->assertSee('href="'.route('login').'"', false)
        ->assertSee('Acceso miembros');

    $this->actingAs(Member::factory()->create())
        ->get(route('home', ['locale' => 'es']))
        ->assertSee('href="'.route('dashboard').'"', false)
        ->assertSee('Panel de control');
});

test('the sign-up form is rate limited', function () {
    foreach (range(1, 6) as $attempt) {
        $this->post(route('newsletter.store', ['locale' => 'es']), newsletterInput(['email' => "person{$attempt}@example.com"]));
    }

    $this->post(route('newsletter.store', ['locale' => 'es']), newsletterInput(['email' => 'one-too-many@example.com']))
        ->assertTooManyRequests();
});
