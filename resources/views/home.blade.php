<x-layouts.public title="AISC Madrid">
    <div class="flex min-h-screen flex-col items-center">
        {{-- Landing --}}
        <header
            class="mx-auto grid min-h-[85vh] w-[90vw] max-w-7xl grid-cols-1 items-center gap-10 py-12 md:grid-cols-12"
        >
            <div class="order-2 flex flex-col items-start justify-center md:order-1 md:col-span-7">
                <h1 class="text-5xl font-bold leading-tight md:text-7xl">
                    <span class="text-primary">AI</span>
                    <br>
                    Student Collective
                    <br>
                    <span class="text-secondary">Madrid</span>
                </h1>

                <p class="mt-8 max-w-2xl text-base leading-8 text-muted-foreground md:text-lg">
                    {{ __('site.home.hero.description') }}
                </p>
            </div>

            <div class="order-1 flex items-center justify-center md:order-2 md:col-span-5 md:justify-end">
                <img
                    src="{{ asset('images/logos/aisc-logo-color.svg') }}"
                    alt="{{ __('site.home.hero.logo_alt') }}"
                    width="400"
                    height="400"
                    class="w-2/3 max-w-sm md:w-3/4"
                >
            </div>
        </header>

        {{-- Companies we have organised events with --}}
        <x-company-logos />

        {{-- Events --}}
        <section
            id="events"
            class="w-full scroll-mt-24 px-6 py-20 md:px-12"
        >
            <div class="mx-auto max-w-7xl">
                <h2 class="text-center text-3xl font-bold md:text-4xl">
                    {{ __('site.home.events.title') }}
                </h2>

                <div class="mx-auto mt-4 h-[3px] w-16 rounded-full bg-primary"></div>

                @if ($upcomingEvents->isNotEmpty())
                    <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                        @foreach ($upcomingEvents as $event)
                            @php
                                $eventTitle = app()->getLocale() === 'en' ? $event->title_en : $event->title_es;

                                $start = $event->start_datetime->copy()->setTimezone('Europe/Madrid');
                            @endphp

                            <a
                                href="{{ route('events.show', ['locale' => app()->getLocale(), 'event' => $event->id]) }}"
                                class="block h-full"
                            >
                                <flux:card class="flex h-full flex-col overflow-hidden">
                                    @if ($event->image_url)
                                        <div class="aspect-square w-full overflow-hidden">
                                            <img
                                                src="{{ $event->image_url }}"
                                                alt="{{ $eventTitle }}"
                                                class="h-full w-full object-cover"
                                            >
                                        </div>
                                    @endif

                                    <div class="flex flex-1 flex-col p-5">
                                        <flux:heading size="lg">
                                            {{ $eventTitle }}
                                        </flux:heading>

                                        <flux:text class="mt-4">
                                            <strong>{{ $start->format('d/m/Y') }}</strong>
                                        </flux:text>

                                        @if ($event->location)
                                            <flux:text class="mt-2">
                                                {{ $event->location }}
                                            </flux:text>
                                        @endif
                                    </div>
                                </flux:card>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-10 text-center">
                        <flux:button href="{{ route('events.index', ['locale' => app()->getLocale()]) }}" variant="primary">
                            {{ __('site.home.events.view_all') }}
                        </flux:button>
                    </div>
                @else
                    <div class="mt-12">
                        <p class="text-center text-muted-foreground">
                            {{ __('site.home.events.empty') }}
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Team --}}
        <section
            id="team"
            class="w-full scroll-mt-24 px-6 py-20 md:px-12"
        >
            <div class="mx-auto max-w-7xl">
                <h2 class="text-center text-3xl font-bold md:text-4xl">
                    {{ __('site.home.team.title') }}
                </h2>

                <div class="mx-auto mt-4 h-[3px] w-16 rounded-full bg-primary"></div>

                @if ($featuredMembers->isNotEmpty())
                    <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($featuredMembers as $member)
                            <x-team-member-card :member="$member" />
                        @endforeach
                    </div>

                    <div class="mt-10 text-center">
                        <flux:button href="{{ route('team.index', ['locale' => app()->getLocale()]) }}" variant="primary">
                            {{ __('site.home.team.view_all') }}
                        </flux:button>
                    </div>
                @else
                    <div class="mt-12">
                        <p class="text-center text-muted-foreground">
                            {{ __('site.home.team.empty') }}
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Newsletter (the navbar "Unirme" button and the footer link point here) --}}
        <section
            id="newsletter"
            class="w-full scroll-mt-24 px-6 pt-4 pb-20 md:px-12"
        >
            <div class="mx-auto max-w-xl rounded-2xl border border-border bg-muted/40 p-6 shadow-sm sm:p-8">
                @if (session('newsletter_subscribed'))
                    <div class="text-center" role="status" data-test="newsletter-success">
                        <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <flux:icon.check class="size-7" />
                        </div>

                        <h2 class="mt-5 text-2xl font-bold">{{ __('site.home.newsletter.success_title') }}</h2>
                        <div class="mx-auto mt-3 h-[3px] w-12 rounded-full bg-primary"></div>
                        <p class="mt-4 text-muted-foreground">{{ __('site.home.newsletter.success_body') }}</p>

                        <a
                            href="{{ config('aisc.whatsapp_community_url') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-6 inline-flex items-center justify-center gap-2 rounded-full bg-[#25D366] px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:brightness-110"
                        >
                            <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.8h-.01a9.8 9.8 0 0 1-4.99-1.37l-.36-.21-3.71.97.99-3.62-.23-.37a9.78 9.78 0 0 1-1.5-5.2c0-5.41 4.4-9.82 9.82-9.82 2.62 0 5.09 1.02 6.94 2.88a9.75 9.75 0 0 1 2.87 6.94c0 5.42-4.4 9.82-9.82 9.82zm8.35-18.17A11.76 11.76 0 0 0 12.04 0C5.5 0 .17 5.33.17 11.88c0 2.1.55 4.14 1.59 5.94L0 24l6.33-1.66a11.86 11.86 0 0 0 5.71 1.46h.01c6.55 0 11.88-5.33 11.88-11.88 0-3.17-1.24-6.16-3.48-8.4z" />
                            </svg>
                            {{ __('site.home.newsletter.whatsapp') }}
                        </a>
                    </div>
                @else
                    <h2 class="text-center text-2xl font-bold md:text-3xl">{{ __('site.home.newsletter.title') }}</h2>
                    <div class="mx-auto mt-3 h-[3px] w-12 rounded-full bg-primary"></div>
                    <p class="mt-4 text-center text-muted-foreground">{{ __('site.home.newsletter.description') }}</p>

                    <form
                        method="POST"
                        action="{{ route('newsletter.store', ['locale' => app()->getLocale()]) }}"
                        class="mt-6 flex flex-col gap-5"
                        data-test="newsletter-form"
                    >
                        @csrf

                        <flux:input
                            name="name"
                            :label="__('site.home.newsletter.name')"
                            :value="old('name')"
                            :placeholder="__('site.home.newsletter.name_placeholder')"
                            autocomplete="name"
                            required
                        />

                        <flux:input
                            type="email"
                            name="email"
                            :label="__('site.home.newsletter.email')"
                            :value="old('email')"
                            :placeholder="__('site.home.newsletter.email_placeholder')"
                            autocomplete="email"
                            required
                        />

                        <flux:field variant="inline">
                            <flux:checkbox name="consent" value="1" :checked="(bool) old('consent')" required />
                            <flux:label class="text-sm font-normal!">
                                <span>
                                    {{ __('site.home.newsletter.consent') }}
                                    <a href="{{ route('terms', ['locale' => app()->getLocale()]) }}" target="_blank" class="text-primary underline-offset-2 hover:underline">
                                        ({{ __('site.home.newsletter.terms') }})
                                    </a>
                                </span>
                            </flux:label>
                            <flux:error name="consent" />
                        </flux:field>

                        <flux:button type="submit" variant="primary" class="w-full">
                            {{ __('site.home.newsletter.submit') }}
                        </flux:button>
                    </form>
                @endif
            </div>
        </section>

    </div>
</x-layouts.public>