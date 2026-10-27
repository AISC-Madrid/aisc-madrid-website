@props([
    'images' => [],
    'title' => '',
])

{{--
    Photo carousel for an event's gallery (like the Bootstrap carousel on the legacy evento.php):
    autoplay every 5s (paused on hover/focus), previous/next buttons, dots and keyboard arrows.
--}}
@if (count($images) > 0)
    <section
        {{ $attributes->class('relative') }}
        aria-roledescription="carousel"
        aria-label="{{ __('events.gallery') }}"
        data-test="event-gallery"
        x-data="{
            current: 0,
            total: {{ count($images) }},
            timer: null,
            go(index) { this.current = (index + this.total) % this.total },
            next() { this.go(this.current + 1) },
            previous() { this.go(this.current - 1) },
            play() { if (this.total > 1) { this.stop(); this.timer = setInterval(() => this.next(), 5000) } },
            stop() { clearInterval(this.timer) },
        }"
        x-init="play()"
        x-on:mouseenter="stop()"
        x-on:mouseleave="play()"
        x-on:focusin="stop()"
        x-on:focusout="play()"
        x-on:keydown.arrow-right.prevent="next()"
        x-on:keydown.arrow-left.prevent="previous()"
    >
        <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-zinc-900 shadow-lg sm:aspect-video">
            @foreach ($images as $index => $url)
                <figure
                    class="absolute inset-0"
                    role="group"
                    aria-roledescription="slide"
                    aria-label="{{ $index + 1 }} / {{ count($images) }}"
                    x-show="current === {{ $index }}"
                    x-transition:enter="transition-opacity duration-500"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-500"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @if ($index > 0) x-cloak @endif
                >
                    <img
                        src="{{ $url }}"
                        alt="{{ $title }} — {{ __('events.gallery_photo', ['number' => $index + 1]) }}"
                        @if ($index > 0) loading="lazy" @endif
                        class="size-full object-contain"
                    >
                </figure>
            @endforeach

            @if (count($images) > 1)
                <button
                    type="button"
                    x-on:click="previous()"
                    class="absolute left-3 top-1/2 flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur transition hover:bg-black/60 focus-visible:outline-2 focus-visible:outline-white"
                    aria-label="{{ __('events.gallery_previous') }}"
                >
                    <flux:icon.chevron-left class="size-5" />
                </button>

                <button
                    type="button"
                    x-on:click="next()"
                    class="absolute right-3 top-1/2 flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur transition hover:bg-black/60 focus-visible:outline-2 focus-visible:outline-white"
                    aria-label="{{ __('events.gallery_next') }}"
                >
                    <flux:icon.chevron-right class="size-5" />
                </button>

                <div class="absolute inset-x-0 bottom-3 flex justify-center gap-2">
                    @foreach ($images as $index => $url)
                        <button
                            type="button"
                            x-on:click="go({{ $index }})"
                            x-bind:class="current === {{ $index }} ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'"
                            x-bind:aria-current="current === {{ $index }} ? 'true' : 'false'"
                            class="h-2 rounded-full transition-all"
                            aria-label="{{ __('events.gallery_photo', ['number' => $index + 1]) }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
