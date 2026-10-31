<?php

namespace App\Http\Controllers;

use App\Actions\SubscribeToNewsletter;
use App\Http\Requests\NewsletterSubscriptionRequest;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    /**
     * Newsletter sign-up form at the bottom of the home page.
     */
    public function store(NewsletterSubscriptionRequest $request, string $locale, SubscribeToNewsletter $subscribeToNewsletter): RedirectResponse
    {
        $subscribeToNewsletter(
            name: $request->validated('name'),
            email: $request->validated('email'),
            locale: $locale,
        );

        return redirect()
            ->to(route('home', ['locale' => $locale]).'#newsletter')
            ->with('newsletter_subscribed', true);
    }
}
