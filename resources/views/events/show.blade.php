<x-layouts.public
    :title="app()->getLocale() === 'es' ? $event->title_es : $event->title_en"
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

        $gallery = is_array($event->gallery_paths)
            ? $event->gallery_paths
            : (json_decode($event->gallery_paths ?? '[]', true) ?: []);

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

    <section class="event-hero">

        <div class="event-hero-inner">

            {{-- IMAGE --}}

            <div class="event-hero-image">

                @if ($event->image_path)

                    <img
                        src="{{ $event->image_path }}"
                        alt="{{ $title }}"
                    >

                @endif

            </div>


            {{-- INFORMATION --}}

            <div class="event-hero-content">

                <span class="event-type">
                    {{ $typeName }}
                </span>


                <h1 class="event-title">
                    {{ $title }}
                </h1>


                @if ($event->speakers->isNotEmpty())

                    <p class="event-speakers">

                        {{ $isSpanish ? 'Ponente:' : 'Speaker:' }}

                        @foreach ($event->speakers as $speaker)

                            <span>
                                {{ $speaker->full_name }}
                            </span>

                            @if (!$loop->last)
                                ,
                            @endif

                        @endforeach

                    </p>

                @endif


                @if ($event->requires_registration)

                    <a
                        href="#"
                        class="event-register"
                    >
                        {{ $isSpanish
                            ? 'Inscribirse al evento'
                            : 'Register for event'
                        }}
                    </a>

                @endif

            </div>

        </div>

    </section>



    {{-- ============================================================= --}}
    {{-- EVENT BODY                                                   --}}
    {{-- ============================================================= --}}

    <section class="event-body">

        {{-- SIDEBAR --}}

        <aside class="event-sidebar">

            <div class="event-sidebar-inner">


                {{-- DATE --}}

                <div class="event-meta">

                    <strong>
                        {{ $start->format('d/m/Y') }}
                    </strong>

                    {{ $start->format('H:i') }}

                    -

                    {{ $end->format('H:i') }}

                </div>


                {{-- LOCATION --}}

                @if ($event->location)

                    <div class="event-location">
                        {{ $event->location }}
                    </div>

                @endif


                {{-- CALENDAR --}}

                <div class="event-calendar">

                    <span>
                        {{ $isSpanish
                            ? 'Añadir al calendario:'
                            : 'Add to calendar:'
                        }}
                    </span>

                    <a
                        href="{{ $calendarUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="calendar-button"
                        title="Google Calendar"
                    >
                        <svg
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect
                                x="3"
                                y="4"
                                width="18"
                                height="17"
                                rx="2"
                            />

                            <path d="M16 2v4" />
                            <path d="M8 2v4" />
                            <path d="M3 9h18" />

                            <path d="M8 13h2" />
                            <path d="M14 13h2" />
                            <path d="M8 17h2" />
                            <path d="M14 17h2" />
                        </svg>
                    </a>

                </div>


                {{-- SHARE --}}

                <div class="event-share">

                    <button
                        type="button"
                        onclick="toggleShareMenu()"
                        class="share-button"
                    >

                        {{ $isSpanish ? 'Compartir' : 'Share' }}

                        <svg
                            width="14"
                            height="14"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                clip-rule="evenodd"
                            />
                        </svg>

                    </button>


                    <div
                        id="share-menu"
                        class="share-menu hidden"
                    >

                        <a
                            href="https://api.whatsapp.com/send?text={{ urlencode($title . ' ' . url()->current()) }}"
                            target="_blank"
                        >
                            WhatsApp
                        </a>

                        <a
                            href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}"
                            target="_blank"
                        >
                            LinkedIn
                        </a>

                        <a
                            href="https://x.com/intent/tweet/?url={{ urlencode(url()->current()) }}"
                            target="_blank"
                        >
                            X
                        </a>

                    </div>

                </div>

            </div>

        </aside>



        {{-- DESCRIPTION --}}

        <main class="event-description">

            {!! $description !!}


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

                    <div class="event-video">

                        <iframe
                            src="https://www.youtube.com/embed/{{ $youtubeId }}"
                            title="{{ $title }}"
                            allowfullscreen
                        ></iframe>

                    </div>

                @endif

            @endif

        </main>

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