{{--
    Newsletter unsubscribe page. $state is one of:
    confirm (ask before unsubscribing), done (just unsubscribed), already (was not subscribed), invalid (unknown token).
--}}
@php
    $locale = app()->getLocale();
    $email = $submission?->email;
@endphp

<x-layouts.public :title="__('mailing.unsubscribe.title').' - AISC Madrid'">
    <section class="flex min-h-[70vh] items-center justify-center px-6 py-16">
        <div class="w-full max-w-md rounded-2xl border border-border bg-muted/40 p-8 text-center shadow-sm" data-test="unsubscribe-{{ $state }}">
            <div @class([
                'mx-auto flex size-14 items-center justify-center rounded-full',
                'bg-primary/10 text-primary' => in_array($state, ['confirm', 'done'], true),
                'bg-zinc-500/10 text-muted-foreground' => in_array($state, ['already', 'invalid'], true),
            ])>
                @switch($state)
                    @case('confirm')
                        <flux:icon.envelope class="size-7" />
                        @break
                    @case('done')
                        <flux:icon.check class="size-7" />
                        @break
                    @default
                        <flux:icon.information-circle class="size-7" />
                @endswitch
            </div>

            @switch($state)
                @case('confirm')
                    <h1 class="mt-5 text-2xl font-bold">{{ __('mailing.unsubscribe.confirm_heading') }}</h1>
                    <p class="mt-3 text-muted-foreground">
                        {!! __('mailing.unsubscribe.confirm_body', ['email' => '<strong class="text-foreground">'.e($email).'</strong>']) !!}
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                        <form method="POST" action="{{ route('newsletter.unsubscribe.store', ['locale' => $locale, 'submission' => $submission->unsubscribe_token]) }}">
                            @csrf
                            <flux:button type="submit" variant="danger" class="w-full sm:w-auto">
                                {{ __('mailing.unsubscribe.confirm_button') }}
                            </flux:button>
                        </form>

                        <flux:button :href="route('home', ['locale' => $locale])">
                            {{ __('mailing.unsubscribe.keep_button') }}
                        </flux:button>
                    </div>
                    @break

                @case('done')
                    <h1 class="mt-5 text-2xl font-bold">{{ __('mailing.unsubscribe.done_heading') }}</h1>
                    <p class="mt-3 text-muted-foreground">
                        {!! __('mailing.unsubscribe.done_body', ['email' => '<strong class="text-foreground">'.e($email).'</strong>']) !!}
                    </p>
                    @break

                @case('already')
                    <h1 class="mt-5 text-2xl font-bold">{{ __('mailing.unsubscribe.already_heading') }}</h1>
                    <p class="mt-3 text-muted-foreground">
                        {!! __('mailing.unsubscribe.already_body', ['email' => '<strong class="text-foreground">'.e($email).'</strong>']) !!}
                    </p>
                    @break

                @default
                    <h1 class="mt-5 text-2xl font-bold">{{ __('mailing.unsubscribe.invalid_heading') }}</h1>
                    <p class="mt-3 text-muted-foreground">{{ __('mailing.unsubscribe.invalid_body') }}</p>
            @endswitch

            @if ($state !== 'confirm')
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    @if (in_array($state, ['done', 'already'], true))
                        <flux:button variant="primary" :href="route('home', ['locale' => $locale]).'#newsletter'">
                            {{ __('mailing.unsubscribe.join_again') }}
                        </flux:button>
                    @endif

                    <flux:button :href="route('home', ['locale' => $locale])">
                        {{ __('mailing.unsubscribe.home') }}
                    </flux:button>
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
