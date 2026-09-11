<x-layouts.public
    :title="(app()->getLocale() === 'es' ? $event->title_es : $event->title_en) . ' - AISC Madrid'"
>

    @php
        $isSpanish = app()->getLocale() === 'es';

        $title = $isSpanish
            ? $event->title_es
            : $event->title_en;

        $description = $isSpanish
            ? $event->description_es
            : $event->description_en;

        $typeName = $isSpanish
            ? $event->type->name_es
            : $event->type->name_en;

        $start = $event->start_datetime
            ->copy()
            ->setTimezone('Europe/Madrid');

        $end = $event->end_datetime
            ->copy()
            ->setTimezone('Europe/Madrid');

        $isFuture = $end->greaterThanOrEqualTo(
            now()->setTimezone('Europe/Madrid')
        );

        $calendarDates =
            $start->utc()->format('Ymd\THis\Z')
            . '/'
            . $end->utc()->format('Ymd\THis\Z');

        $calendarUrl =
            'https://calendar.google.com/calendar/render'
            . '?action=TEMPLATE'
            . '&text=' . urlencode($title)
            . '&dates=' . urlencode($calendarDates)
            . '&details=' . urlencode(strip_tags($description ?? ''))
            . '&location=' . urlencode($event->location ?? '');
    @endphp


    {{-- ============================================================= --}}
    {{-- HERO                                                         --}}
    {{-- ============================================================= --}}

    <section class="border-b border-border bg-muted/40">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-6 py-14 md:grid-cols-12 lg:px-8">

            {{-- INFORMATION --}}
            <div class="order-2 flex flex-col justify-center md:order-1 md:col-span-7">

                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-primary">
                        {{ $typeName }}
                    </span>

                    @if ($isFuture)
                        <flux:badge color="green">
                            {{ $isSpanish ? 'Próximamente' : 'Upcoming' }}
                        </flux:badge>
                    @endif
                </div>

                <flux:heading size="xl" level="1" class="mt-4">
                    {{ $title }}
                </flux:heading>

                @if ($event->speakers->isNotEmpty())
                    <flux:text size="lg" class="mt-4">
                        <strong>{{ $isSpanish ? 'Ponente:' : 'Speaker:' }}</strong>

                        @foreach ($event->speakers as $speaker)
                            {{ $speaker->full_name }}{{ ! $loop->last ? ',' : '' }}
                        @endforeach
                    </flux:text>
                @endif

                <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3">
                    <div class="flex items-center gap-2 text-foreground">
                        <flux:icon.calendar class="size-5 shrink-0 text-primary" />

                        <flux:text>
                            <strong>{{ $start->format('d/m/Y') }}</strong>
                            &middot;
                            {{ $start->format('H:i') }}-{{ $end->format('H:i') }}
                        </flux:text>
                    </div>

                    @if ($event->location)
                        <div class="flex items-center gap-2 text-foreground">
                            <flux:icon.map-pin class="size-5 shrink-0 text-primary" />

                            <flux:text>
                                {{ $event->location }}
                            </flux:text>
                        </div>
                    @endif
                </div>

                @if ($event->requires_registration && $isFuture)
                    <a
                        href="#"
                        class="mt-8 inline-flex w-fit items-center rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90"
                    >
                        {{ $isSpanish
                            ? 'Inscribirse al evento'
                            : 'Register for event'
                        }}
                    </a>
                @endif
            </div>

            {{-- IMAGE --}}
            <div class="order-1 flex items-center justify-center md:order-2 md:col-span-5">
                @if ($event->image_path)
                    <div class="aspect-square w-full overflow-hidden rounded-2xl shadow-lg">
                        <img
                            src="{{ asset($event->image_path) }}"
                            alt="{{ $title }}"
                            class="h-full w-full object-cover"
                        >
                    </div>
                @endif
            </div>

        </div>
    </section>


    {{-- ============================================================= --}}
    {{-- EVENT BODY                                                   --}}
    {{-- ============================================================= --}}

    <section class="mx-auto max-w-7xl px-6 py-14 lg:grid lg:grid-cols-12 lg:gap-10 lg:px-8">

        {{-- DESCRIPTION --}}
        <main class="lg:col-span-8">
            <div class="space-y-4 leading-relaxed text-foreground
                [&_h2]:mt-8 [&_h2]:text-2xl [&_h2]:font-bold
                [&_h3]:mt-6 [&_h3]:text-xl [&_h3]:font-semibold
                [&_p]:mb-4
                [&_ul]:list-disc [&_ul]:pl-6
                [&_ol]:list-decimal [&_ol]:pl-6
                [&_a]:text-primary [&_a]:underline [&_a]:underline-offset-2"
            >
                {!! $description !!}
            </div>

            {{-- YOUTUBE --}}
            @if ($event->youtube_url)
                @php
                    $youtubeId = null;

                    if (
                        preg_match(
                            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&?\/]+)/',
                            $event->youtube_url,
                            $matches
                        )
                    ) {
                        $youtubeId = $matches[1];
                    }
                @endphp

                @if ($youtubeId)
                    <div class="mt-10 aspect-video w-full overflow-hidden rounded-2xl shadow-lg">
                        <iframe
                            src="https://www.youtube.com/embed/{{ $youtubeId }}"
                            title="{{ $title }}"
                            class="h-full w-full"
                            allowfullscreen
                        ></iframe>
                    </div>
                @endif
            @endif
        </main>

        {{-- SIDEBAR --}}
        <aside class="mt-10 lg:col-span-4 lg:mt-0">
            <flux:card class="sticky top-28 space-y-6 p-6">

                {{-- DATE --}}
                <div>
                    <flux:heading size="sm" class="uppercase tracking-wide text-muted-foreground">
                        {{ $isSpanish ? 'Fecha' : 'Date' }}
                    </flux:heading>

                    <flux:text class="mt-2">
                        <strong>{{ $start->format('d/m/Y') }}</strong>
                        <br>
                        {{ $start->format('H:i') }} - {{ $end->format('H:i') }}
                    </flux:text>
                </div>

                {{-- LOCATION --}}
                @if ($event->location)
                    <div class="border-t border-border pt-6">
                        <flux:heading size="sm" class="uppercase tracking-wide text-muted-foreground">
                            {{ $isSpanish ? 'Ubicación' : 'Location' }}
                        </flux:heading>

                        <flux:text class="mt-2">
                            {{ $event->location }}
                        </flux:text>
                    </div>
                @endif

                {{-- CALENDAR --}}
                <div class="border-t border-border pt-6">
                    <flux:text class="mb-2">
                        {{ $isSpanish
                            ? 'Añadir al calendario:'
                            : 'Add to calendar:'
                        }}
                    </flux:text>

                    <a
                        href="{{ $calendarUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        title="Google Calendar"
                        class="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-medium text-foreground transition hover:border-primary hover:text-primary"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="17" rx="2" />
                            <path d="M16 2v4" />
                            <path d="M8 2v4" />
                            <path d="M3 9h18" />
                            <path d="M8 13h2" />
                            <path d="M14 13h2" />
                            <path d="M8 17h2" />
                            <path d="M14 17h2" />
                        </svg>

                        Google Calendar
                    </a>
                </div>

                {{-- SHARE --}}
                <div class="relative border-t border-border pt-6">
                    <button
                        type="button"
                        onclick="toggleShareMenu()"
                        class="share-button inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-medium text-foreground transition hover:border-primary hover:text-primary"
                    >
                        {{ $isSpanish ? 'Compartir' : 'Share' }}

                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <div
                        id="share-menu"
                        class="hidden absolute left-0 z-10 mt-2 w-40 overflow-hidden rounded-xl border border-border bg-surface p-1 shadow-xl"
                    >
                        <a
                            href="https://api.whatsapp.com/send?text={{ urlencode($title . ' ' . url()->current()) }}"
                            target="_blank"
                            class="block rounded-lg px-3 py-2 text-sm text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        >
                            WhatsApp
                        </a>

                        <a
                            href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}"
                            target="_blank"
                            class="block rounded-lg px-3 py-2 text-sm text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        >
                            LinkedIn
                        </a>

                        <a
                            href="https://x.com/intent/tweet/?url={{ urlencode(url()->current()) }}"
                            target="_blank"
                            class="block rounded-lg px-3 py-2 text-sm text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        >
                            X
                        </a>
                    </div>
                </div>

            </flux:card>
        </aside>

    </section>


    {{-- ============================================================= --}}
    {{-- SHARE JS                                                     --}}
    {{-- ============================================================= --}}

    <script>

        function toggleShareMenu() {

            const menu = document.getElementById('share-menu');

            if (menu) {
                menu.classList.toggle('hidden');
            }

        }

        document.addEventListener('click', function (event) {

            const menu = document.getElementById('share-menu');

            if (!menu) {
                return;
            }

            const button = event.target.closest('.share-button');

            if (!menu.contains(event.target) && !button) {
                menu.classList.add('hidden');
            }

        });

    </script>

</x-layouts.public>
