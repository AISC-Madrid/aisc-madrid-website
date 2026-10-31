<?php

namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Welcome email sent when someone joins the newsletter (legacy processing/phpmailer.php).
 *
 * Queued so the sign-up page never waits for SMTP, sent only after the subscription is committed,
 * and carries List-Unsubscribe headers so mail clients can offer one-click unsubscribe (RFC 8058).
 */
class NewsletterWelcome extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Retry a few times if the SMTP server is temporarily unavailable.
     */
    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300];

    public function __construct(public FormSubmission $submission)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('mail.reply_to.address'), config('mail.reply_to.name'))],
            subject: __('mailing.welcome.subject'),
            tags: ['newsletter-welcome'],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.newsletter.welcome',
            with: [
                'firstName' => Str::before(trim($this->submission->full_name), ' ') ?: $this->submission->full_name,
                'unsubscribeUrl' => $this->unsubscribeUrl(),
                'whatsappUrl' => config('aisc.whatsapp_community_url'),
            ],
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.route('newsletter.unsubscribe.one-click', ['locale' => $this->currentLocale(), 'submission' => $this->submission->unsubscribe_token]).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    private function unsubscribeUrl(): string
    {
        return route('newsletter.unsubscribe.show', ['locale' => $this->currentLocale(), 'submission' => $this->submission->unsubscribe_token]);
    }

    /**
     * The mail is rendered inside its own locale (set with ->locale() when queued).
     */
    private function currentLocale(): string
    {
        return in_array(app()->getLocale(), ['es', 'en'], true) ? app()->getLocale() : 'es';
    }
}
