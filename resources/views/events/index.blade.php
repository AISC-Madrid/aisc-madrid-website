<x-layouts.public :title="__('site.events.title') . ' - AISC Madrid'">

    {{-- Page header --}}
    <div class="mx-auto w-full max-w-7xl px-6 pt-10 lg:px-8">

        <div class="mx-auto max-w-3xl text-center">

            <flux:heading
                size="xl"
                level="1"
            >
                {{ __('events.heading') }}
            </flux:heading>

            <div class="mx-auto my-4 h-1 w-15 rounded-full bg-[var(--primary)]"></div>

            <flux:text
                size="lg"
                class="mx-auto max-w-2xl"
            >
                {{ __('events.subheading') }}
            </flux:text>

        </div>

    </div>


    {{-- Events section --}}
    <section class="pb-16 pt-10">

        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">

            {{-- Filter buttons --}}
            <div class="mb-8 flex justify-center sm:justify-end">

                <flux:button.group>

                    <flux:button
                        variant="primary"
                        class="filter-button"
                        data-filter="all"
                    >
                        {{ __('events.filters.all') }}
                    </flux:button>

                    <flux:button
                        variant="ghost"
                        class="filter-button"
                        data-filter="event"
                    >
                        {{ __('events.filters.event') }}
                    </flux:button>

                    <flux:button
                        variant="ghost"
                        class="filter-button"
                        data-filter="workshop"
                    >
                        {{ __('events.filters.workshop') }}
                    </flux:button>

                </flux:button.group>

            </div>


            {{-- Events grid --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">

                @foreach ($events as $event)

                    @php
                        $isFuture = $event->end_datetime >= now();

                        $eventClass = $isFuture
                            ? 'event-future'
                            : 'event-past';

                        $startDate = $event->start_datetime
                            ->copy()
                            ->setTimezone('Europe/Madrid');

                        $endDate = $event->end_datetime
                            ->copy()
                            ->setTimezone('Europe/Madrid');

                        $type = strtolower($event->type->slug);

                        $title = app()->getLocale() === 'en'
                            ? $event->title_en
                            : $event->title_es;
                    @endphp

                    <div
                        class="event-card {{ $eventClass }}"
                        data-type="{{ $type }}"
                        data-date="{{ $event->end_datetime->timestamp }}"
                    >

                        <a
                            href="{{ route('events.show', [
                                'locale' => request()->route('locale'),
                                'event' => $event->id,
                            ]) }}"
                            class="block h-full"
                        >

                            <flux:card class="relative flex h-full flex-col overflow-hidden">

                                {{-- Event image --}}
                                @if ($event->image_path)

                                    <div class="aspect-square w-full overflow-hidden">

                                        <img
                                            src="{{ asset($event->image_path) }}"
                                            alt="{{ $title }}"
                                            class="h-full w-full object-cover"
                                        >

                                    </div>

                                @endif


                                {{-- Upcoming badge --}}
                                @if ($isFuture)

                                    <flux:badge
                                        color="green"
                                        class="absolute left-3 top-3"
                                    >
                                        {{ __('events.upcoming') }}
                                    </flux:badge>

                                @endif


                                {{-- Event information --}}
                                <div class="flex flex-1 flex-col p-5">

                                    <flux:heading size="lg">
                                        {{ $title }}
                                    </flux:heading>


                                    {{-- Date --}}
                                    <div class="mt-4 flex gap-3">

                                        <flux:icon.calendar class="mt-0.5 size-5 shrink-0" />

                                        <flux:text>
                                            <strong>
                                                {{ $startDate->format('d/m/Y') }}
                                            </strong>

                                            <br>

                                            {{ $startDate->format('H:i') }}
                                            -
                                            {{ $endDate->format('H:i') }}
                                        </flux:text>

                                    </div>


                                    {{-- Location --}}
                                    @if ($event->location)

                                        <div class="mt-2 flex gap-3">

                                            <flux:icon.map-pin class="mt-0.5 size-5 shrink-0" />

                                            <flux:text>
                                                {{ $event->location }}
                                            </flux:text>

                                        </div>

                                    @endif

                                </div>


                                {{-- More information --}}
                                <div class="px-5 pb-5">

                                    <flux:text
                                        class="font-medium"
                                    >
                                        {{ __('events.read_more') }}
                                    </flux:text>

                                </div>

                            </flux:card>

                        </a>

                    </div>

                @endforeach

            </div>

        </div>

    </section>


    {{-- Filtering --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const buttons = document.querySelectorAll('.filter-button');
            const cards = document.querySelectorAll('.event-card');

            buttons.forEach(button => {

                button.addEventListener('click', function () {

                    buttons.forEach(btn => {
                        btn.setAttribute('variant', 'ghost');
                    });

                    this.setAttribute('variant', 'primary');

                    const filter = this.dataset.filter;

                    cards.forEach(card => {

                        if (filter === 'all') {
                            card.style.display = '';
                            return;
                        }

                        card.style.display =
                            card.dataset.type === filter
                                ? ''
                                : 'none';

                    });

                });

            });

        });

    </script>

</x-layouts.public>