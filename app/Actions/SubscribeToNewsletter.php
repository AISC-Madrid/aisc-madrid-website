<?php

namespace App\Actions;

use App\Mail\NewsletterWelcome;
use App\Models\FormSubmission;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Subscribe someone to the newsletter (form_submissions) and queue the welcome email.
 *
 * Someone who had unsubscribed is subscribed again (and welcomed again). Someone already
 * subscribed is left as is and gets no second email.
 */
class SubscribeToNewsletter
{
    public function __invoke(string $name, string $email, string $locale): FormSubmission
    {
        return DB::transaction(function () use ($name, $email, $locale): FormSubmission {
            $submission = FormSubmission::query()->lockForUpdate()->firstWhere('email', $email);

            if ($submission?->newsletter) {
                return $submission;
            }

            if ($submission) {
                $submission->update(['newsletter' => true]);
            } else {
                $submission = FormSubmission::create([
                    'full_name' => $name,
                    'email' => $email,
                    'newsletter' => true,
                    'unsubscribe_token' => FormSubmission::newUnsubscribeToken(),
                ]);
            }

            // Queued and only dispatched once this transaction commits (see NewsletterWelcome).
            Mail::to(new Address($submission->email, $submission->full_name))
                ->locale($locale)
                ->queue(new NewsletterWelcome($submission));

            return $submission;
        });
    }
}
