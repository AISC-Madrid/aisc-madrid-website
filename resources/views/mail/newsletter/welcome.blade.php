<x-mail::message>
# {{ __('mailing.welcome.greeting', ['name' => $firstName]) }}

{{ __('mailing.welcome.thanks') }}

{{ __('mailing.welcome.what_next') }}

{{ __('mailing.welcome.whatsapp_intro') }}

<x-mail::button :url="$whatsappUrl" color="whatsapp">
{{ __('mailing.welcome.whatsapp_button') }}
</x-mail::button>

{{ __('mailing.welcome.social', ['instagram' => config('aisc.social.instagram'), 'linkedin' => config('aisc.social.linkedin')]) }}

{{ __('mailing.welcome.sign_off') }}<br>
{{ __('mailing.welcome.team') }}

<x-mail::subcopy>
{{ __('mailing.welcome.unsubscribe', ['url' => $unsubscribeUrl]) }}
</x-mail::subcopy>
</x-mail::message>
