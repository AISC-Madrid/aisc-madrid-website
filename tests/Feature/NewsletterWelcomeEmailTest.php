<?php

use App\Actions\SubscribeToNewsletter;
use App\Mail\NewsletterWelcome;
use App\Models\FormSubmission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

test('joining the newsletter queues the welcome email in the visitor language', function (string $locale) {
    $this->post(route('newsletter.store', ['locale' => $locale]), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'consent' => '1',
    ])->assertSessionHasNoErrors();

    $submission = FormSubmission::sole();

    Mail::assertQueuedCount(1);

    $mail = Mail::queued(NewsletterWelcome::class)->sole();

    expect($mail->hasTo('ada@example.com', 'Ada Lovelace'))->toBeTrue();
    expect($mail->locale)->toBe($locale);
    expect($mail->submission->is($submission))->toBeTrue();
})->with(['es', 'en']);

test('someone who unsubscribed and joins again gets the welcome email again', function () {
    FormSubmission::factory()->withoutNewsletter()->create(['email' => 'ada@example.com']);

    $this->post(route('newsletter.store', ['locale' => 'es']), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'consent' => '1',
    ])->assertSessionHasNoErrors();

    Mail::assertQueued(NewsletterWelcome::class, fn (NewsletterWelcome $mail) => $mail->hasTo('ada@example.com'));
});

test('a rejected sign-up sends no email', function () {
    FormSubmission::factory()->create(['email' => 'ada@example.com']);

    $this->post(route('newsletter.store', ['locale' => 'es']), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'consent' => '1',
    ])->assertSessionHasErrors('email');

    Mail::assertNothingQueued();
});

test('subscribing someone who is already subscribed does not send a second email', function () {
    $existing = FormSubmission::factory()->create(['email' => 'ada@example.com']);

    $submission = app(SubscribeToNewsletter::class)('Ada Lovelace', 'ada@example.com', 'es');

    expect($submission->is($existing))->toBeTrue();
    expect(FormSubmission::count())->toBe(1);
    Mail::assertNothingQueued();
});

test('the welcome email is queued and only sent after the subscription is saved', function () {
    $mail = new NewsletterWelcome(FormSubmission::factory()->create());

    expect($mail)->toBeInstanceOf(ShouldQueue::class);
    expect($mail->afterCommit)->toBeTrue();
    expect($mail->tries)->toBe(3);
});

test('the welcome email content in spanish', function () {
    $submission = FormSubmission::factory()->create(['full_name' => 'Ada Lovelace']);

    $mail = (new NewsletterWelcome($submission))->locale('es');

    $mail->assertHasSubject('¡Bienvenid@ a la comunidad AISC Madrid!');
    $mail->assertHasReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
    $mail->assertSeeInHtml('¡Hola, Ada!');
    $mail->assertSeeInHtml('Gracias por unirte a la newsletter');
    $mail->assertSeeInHtml(config('aisc.whatsapp_community_url'), false);
    $mail->assertSeeInHtml(config('aisc.social.instagram'), false);
    $mail->assertSeeInHtml(asset('images/logos/aisc-logo-color.png'), false);
    $mail->assertSeeInHtml(route('newsletter.unsubscribe.show', ['locale' => 'es', 'submission' => $submission->unsubscribe_token]), false);
    $mail->assertDontSeeInHtml('Laravel');

    $mail->assertSeeInText('¡Hola, Ada!');
    $mail->assertSeeInText('Cancela tu suscripción');
});

test('the welcome email content in english', function () {
    $submission = FormSubmission::factory()->create(['full_name' => 'Ada Lovelace']);

    $mail = (new NewsletterWelcome($submission))->locale('en');

    $mail->assertHasSubject('Welcome to the AISC Madrid community!');
    $mail->assertSeeInHtml('Hi Ada!');
    $mail->assertSeeInHtml(route('newsletter.unsubscribe.show', ['locale' => 'en', 'submission' => $submission->unsubscribe_token]), false);
});

test('the welcome email offers one-click unsubscribe to mail clients', function () {
    $submission = FormSubmission::factory()->create();

    app()->setLocale('es');
    $headers = (new NewsletterWelcome($submission))->headers()->text;

    expect($headers)
        ->toHaveKey('List-Unsubscribe', '<'.route('newsletter.unsubscribe.one-click', ['locale' => 'es', 'submission' => $submission->unsubscribe_token]).'>')
        ->toHaveKey('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
});

test('mail settings fall back to the legacy SMTP_* variable names', function () {
    $variables = [
        'MAIL_MAILER' => '',
        'MAIL_HOST' => '',
        'MAIL_PORT' => '',
        'MAIL_USERNAME' => '',
        'MAIL_PASSWORD' => '',
        'SMTP_HOST' => 'smtp.legacy.test',
        'SMTP_PORT' => '465',
        'SMTP_USER' => 'legacy-user',
        'SMTP_PASS' => 'legacy-pass',
    ];
    $previous = [];

    foreach ($variables as $name => $value) {
        $previous[$name] = getenv($name);
        putenv("{$name}={$value}");
        $_ENV[$name] = $_SERVER[$name] = $value;
    }

    try {
        $mail = require config_path('mail.php');
    } finally {
        foreach ($previous as $name => $value) {
            putenv($value === false ? $name : "{$name}={$value}");
            unset($_ENV[$name], $_SERVER[$name]);
        }
    }

    expect($mail['default'])->toBe('smtp');
    expect($mail['mailers']['smtp'])
        ->host->toBe('smtp.legacy.test')
        ->port->toBe('465')
        ->username->toBe('legacy-user')
        ->password->toBe('legacy-pass');
});
