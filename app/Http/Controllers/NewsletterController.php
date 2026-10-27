<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscriptionRequest;
use App\Models\FormSubmission;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    /**
     * Subscribe someone to the newsletter (stored in form_submissions, like the legacy site).
     * Someone who had unsubscribed is subscribed again instead of getting a duplicate error.
     */
    public function store(NewsletterSubscriptionRequest $request, string $locale): RedirectResponse
    {
        $submission = FormSubmission::firstWhere('email', $request->validated('email'));

        if ($submission) {
            $submission->update(['newsletter' => true]);
        } else {
            FormSubmission::create([
                'full_name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'newsletter' => true,
                'unsubscribe_token' => bin2hex(random_bytes(16)),
            ]);
        }

        return redirect()
            ->to(route('home', ['locale' => $locale]).'#newsletter')
            ->with('newsletter_subscribed', true);
    }
}
