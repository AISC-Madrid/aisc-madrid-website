<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Newsletter unsubscribe by token (legacy processing/unsubscribe.php + unsubscribe_confirm.php).
 *
 * Opening the link only shows a confirmation page: email security scanners open every link in
 * an email, so a GET must never unsubscribe anyone. The visitor confirms with a POST form, and
 * mail clients use the separate one-click endpoint from the List-Unsubscribe header (RFC 8058).
 */
class NewsletterUnsubscribeController extends Controller
{
    public function show(Request $request, string $locale, FormSubmission $submission): View
    {
        $state = match (true) {
            $request->session()->get('newsletter_unsubscribed') === $submission->id => 'done',
            $submission->newsletter => 'confirm',
            default => 'already',
        };

        return view('newsletter.unsubscribe', [
            'state' => $state,
            'submission' => $submission,
        ]);
    }

    public function store(string $locale, FormSubmission $submission): RedirectResponse
    {
        $submission->unsubscribe();

        // Post/Redirect/Get, so refreshing the result page does not resubmit the form.
        return to_route('newsletter.unsubscribe.show', ['locale' => $locale, 'submission' => $submission->unsubscribe_token])
            ->with('newsletter_unsubscribed', $submission->id);
    }

    /**
     * One-click unsubscribe from the mail client (List-Unsubscribe-Post). No CSRF token here:
     * the request comes from the mail provider, and the secret token in the URL authorises it.
     */
    public function oneClick(string $locale, FormSubmission $submission): Response
    {
        $submission->unsubscribe();

        return response()->noContent();
    }

    /**
     * Unsubscribe links in emails sent by the legacy site: /processing/unsubscribe.php?token=…
     */
    public function legacy(Request $request): RedirectResponse
    {
        $token = $request->query('token');

        if (! is_string($token) || $token === '') {
            return to_route('home', ['locale' => 'es']);
        }

        return redirect()->route('newsletter.unsubscribe.show', ['locale' => 'es', 'submission' => $token], 301);
    }

    /**
     * Shown (with a 404) when the token in the link does not match any subscriber.
     */
    public static function invalidLink(Request $request): Response
    {
        // Route-model binding fails before the "locale" middleware runs, so set the language here.
        $locale = $request->route('locale');
        app()->setLocale(in_array($locale, ['es', 'en'], true) ? $locale : 'es');

        return response()->view('newsletter.unsubscribe', ['state' => 'invalid', 'submission' => null], 404);
    }
}
