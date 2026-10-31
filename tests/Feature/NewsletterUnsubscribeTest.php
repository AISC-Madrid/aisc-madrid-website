<?php

use App\Mail\NewsletterWelcome;
use App\Models\FormSubmission;

function unsubscribeUrl(FormSubmission $submission, string $locale = 'es', string $route = 'show'): string
{
    return route("newsletter.unsubscribe.{$route}", ['locale' => $locale, 'submission' => $submission->unsubscribe_token]);
}

test('opening the unsubscribe link only asks for confirmation', function () {
    $submission = FormSubmission::factory()->create(['email' => 'ada@example.com']);

    $this->get(unsubscribeUrl($submission))
        ->assertOk()
        ->assertSee('data-test="unsubscribe-confirm"', false)
        ->assertSee('¿Cancelar la suscripción?')
        ->assertSee('ada@example.com')
        ->assertSee('action="'.unsubscribeUrl($submission, route: 'store').'"', false);

    expect($submission->refresh()->newsletter)->toBeTrue();
});

test('confirming unsubscribes and shows the result', function () {
    $submission = FormSubmission::factory()->create(['email' => 'ada@example.com']);

    $this->post(unsubscribeUrl($submission, route: 'store'))
        ->assertRedirect(unsubscribeUrl($submission));

    expect($submission->refresh()->newsletter)->toBeFalse();

    $this->followingRedirects()
        ->post(unsubscribeUrl($submission, route: 'store'))
        ->assertOk()
        ->assertSee('data-test="unsubscribe-done"', false)
        ->assertSee('Suscripción cancelada');
});

test('the unsubscribe page is translated', function () {
    $submission = FormSubmission::factory()->create();

    $this->get(unsubscribeUrl($submission, 'en'))
        ->assertOk()
        ->assertSee('Unsubscribe from the newsletter?')
        ->assertSee('Yes, unsubscribe');
});

test('someone already unsubscribed is told so and can join again', function () {
    $submission = FormSubmission::factory()->withoutNewsletter()->create(['email' => 'ada@example.com']);

    $this->get(unsubscribeUrl($submission))
        ->assertOk()
        ->assertSee('data-test="unsubscribe-already"', false)
        ->assertSee('Ya no estás suscrito')
        ->assertSee(route('home', ['locale' => 'es']).'#newsletter', false);
});

test('an unknown token shows a friendly invalid link page in the right language', function (string $locale, string $heading) {
    $this->get(route('newsletter.unsubscribe.show', ['locale' => $locale, 'submission' => 'not-a-real-token']))
        ->assertNotFound()
        ->assertSee('data-test="unsubscribe-invalid"', false)
        ->assertSee($heading);
})->with([
    ['es', 'Enlace no válido'],
    ['en', 'Invalid link'],
]);

test('mail clients can unsubscribe with one click without a CSRF token', function () {
    $submission = FormSubmission::factory()->create();

    $this->withMiddleware()
        ->post(unsubscribeUrl($submission, route: 'one-click'), ['List-Unsubscribe' => 'One-Click'])
        ->assertNoContent();

    expect($submission->refresh()->newsletter)->toBeFalse();
});

test('one-click with an unknown token does nothing', function () {
    $this->post(route('newsletter.unsubscribe.one-click', ['locale' => 'es', 'submission' => 'not-a-real-token']))
        ->assertNoContent();
});

test('unsubscribing twice is harmless', function () {
    $submission = FormSubmission::factory()->withoutNewsletter()->create();

    $this->post(unsubscribeUrl($submission, route: 'store'))->assertRedirect();

    expect($submission->refresh()->newsletter)->toBeFalse();
});

test('unsubscribe links from emails sent by the legacy site still work', function () {
    $submission = FormSubmission::factory()->create();

    $this->get('/processing/unsubscribe.php?token='.$submission->unsubscribe_token)
        ->assertStatus(301)
        ->assertRedirect(unsubscribeUrl($submission));
});

test('a legacy link without a token goes to the home page', function () {
    $this->get('/processing/unsubscribe.php')
        ->assertRedirect(route('home', ['locale' => 'es']));
});

test('the unsubscribe link in the welcome email works', function () {
    $submission = FormSubmission::factory()->create();

    $html = (new NewsletterWelcome($submission))->locale('es')->render();

    preg_match('#href="([^"]*/newsletter/unsubscribe/[^"]+)"#', $html, $match);

    expect($match[1] ?? null)->toBe(unsubscribeUrl($submission));

    $this->get($match[1])->assertOk()->assertSee('data-test="unsubscribe-confirm"', false);
});
