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
                                    @if ($event->image_path)
                                        <div class="aspect-square w-full overflow-hidden">
                                            <img
                                                src="{{ asset($event->image_path) }}"
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

    </div>
</x-layouts.public>