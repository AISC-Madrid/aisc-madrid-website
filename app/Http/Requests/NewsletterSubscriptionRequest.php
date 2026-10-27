<?php

namespace App\Http\Requests;

use App\Models\FormSubmission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Newsletter sign-up form at the bottom of the home page (legacy processing/phpmailer.php).
 */
class NewsletterSubscriptionRequest extends FormRequest
{
    /**
     * Where to send the visitor back on a validation error: the form itself.
     */
    protected function getRedirectUrl(): string
    {
        return route('home', ['locale' => app()->getLocale()]).'#newsletter';
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                // Only active subscribers are duplicates; people who unsubscribed can join again.
                Rule::unique(FormSubmission::class, 'email')->where('newsletter', 'yes'),
            ],
            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('site.home.newsletter.errors.name'),
            'email.required' => __('site.home.newsletter.errors.email'),
            'email.email' => __('site.home.newsletter.errors.email'),
            'email.unique' => __('site.home.newsletter.errors.duplicate'),
            'consent.accepted' => __('site.home.newsletter.errors.consent'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }
}
