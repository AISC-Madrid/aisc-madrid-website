<x-layouts.public :title="__('site.about.title') . ' - AISC Madrid'">

    @php
        // Same images as the legacy about_us.php (cdn('images/...') on Cloudinary).
        $teamPhotoUrl = 'https://res.cloudinary.com/dpchpoort/image/upload/aisc_madrid/resources/equipoAISC.jpg';
        $missionImageUrl = 'https://res.cloudinary.com/dpchpoort/image/upload/aisc_madrid/logos/PNG/Understand%20AI%20Unlock%20Potential.png';

        $chapterLink = fn (string $name, string $url): string => sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" class="font-medium text-primary underline-offset-4 hover:underline">%s</a>',
            e($url),
            e($name),
        );

        $introBody = __('site.about.intro_body', [
            'davis' => $chapterLink('University of California Davis', 'https://www.instagram.com/aiscdavis/'),
            'washington' => $chapterLink('University of Washington', 'https://www.instagram.com/aiscuw/'),
            'riverside' => $chapterLink('University of California Riverside', 'https://www.instagram.com/aisc_riv/'),
        ]);

        $values = __('site.about.values_items');
    @endphp

    {{-- Page header --}}
    <div class="mx-auto w-full max-w-7xl px-6 pt-10 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <flux:heading size="xl" level="1">
                {{ __('site.about.title') }}
            </flux:heading>

            <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>
        </div>
    </div>

    {{-- AI Student Collective: text + team photo --}}
    <section class="px-6 py-12 lg:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-10 md:grid-cols-2 lg:gap-16">
            <div>
                <h2 class="text-3xl font-semibold text-primary md:text-4xl">
                    {{ __('site.about.intro_heading') }}
                </h2>

                <p class="mt-6 text-lg leading-relaxed text-foreground" data-test="about-intro">
                    {!! $introBody !!}
                </p>
            </div>

            <figure class="overflow-hidden rounded-2xl shadow-xl ring-1 ring-border">
                <img
                    src="{{ $teamPhotoUrl }}"
                    alt="{{ __('site.about.team_photo_alt') }}"
                    class="aspect-[4/3] w-full object-cover"
                    data-test="about-team-photo"
                >
            </figure>
        </div>
    </section>

    {{-- Mission: image + text (reversed on desktop) --}}
    <section class="px-6 py-12 lg:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-10 md:grid-cols-2 lg:gap-16">
            <div class="md:order-2">
                <h2 class="text-3xl font-semibold text-primary md:text-4xl">
                    {{ __('site.about.mission_heading') }}
                </h2>

                <p class="mt-6 text-lg text-foreground">
                    {{ __('site.about.mission_intro') }}
                </p>

                <ul class="mt-4 space-y-3">
                    @foreach (__('site.about.mission_items') as $item)
                        <li class="flex gap-3 text-lg leading-relaxed text-foreground">
                            <span class="mt-2.5 size-2 shrink-0 rounded-full bg-primary" aria-hidden="true"></span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- The image has black lettering, so it sits on a white card in dark mode. --}}
            <div class="flex justify-center rounded-2xl p-6 md:order-1 dark:bg-white">
                <img
                    src="{{ $missionImageUrl }}"
                    alt="{{ __('site.about.mission_image_alt') }}"
                    loading="lazy"
                    class="w-full max-w-md"
                >
            </div>
        </div>
    </section>

    {{-- Values: tabs like the legacy page --}}
    <section class="px-6 pt-8 pb-20 lg:px-8" x-data="{ activeValue: 0 }">
        <div class="mx-auto max-w-4xl text-center">
            <h2 class="text-3xl font-semibold text-primary md:text-4xl">
                {{ __('site.about.values_heading') }}
            </h2>

            <div class="mt-8 flex flex-wrap justify-center gap-2" role="tablist" aria-label="{{ __('site.about.values_heading') }}">
                @foreach ($values as $value)
                    <button
                        type="button"
                        role="tab"
                        id="value-tab-{{ $loop->index }}"
                        aria-controls="value-panel-{{ $loop->index }}"
                        x-on:click="activeValue = {{ $loop->index }}"
                        x-bind:aria-selected="(activeValue === {{ $loop->index }}).toString()"
                        x-bind:class="activeValue === {{ $loop->index }}
                            ? 'bg-primary text-primary-foreground shadow-md shadow-primary/30'
                            : 'text-foreground hover:text-primary'"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition-colors"
                    >
                        {{ $value['title'] }}
                    </button>
                @endforeach
            </div>

            <div class="mt-8 min-h-24">
                @foreach ($values as $value)
                    <p
                        role="tabpanel"
                        id="value-panel-{{ $loop->index }}"
                        aria-labelledby="value-tab-{{ $loop->index }}"
                        x-show="activeValue === {{ $loop->index }}"
                        x-transition.opacity.duration.200ms
                        @if (! $loop->first) x-cloak @endif
                        class="mx-auto max-w-2xl text-lg leading-relaxed text-foreground"
                    >
                        {{ $value['body'] }}
                    </p>
                @endforeach
            </div>
        </div>
    </section>

</x-layouts.public>
