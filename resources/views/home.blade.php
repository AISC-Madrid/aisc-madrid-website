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

                <div class="mt-12">
                    <p class="text-center text-muted-foreground">
                        {{ __('site.home.events.empty') }}
                    </p>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>