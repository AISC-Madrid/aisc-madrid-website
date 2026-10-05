<x-layouts.public :title="__('site.about.title') . ' - AISC Madrid'">


    {{-- Page header --}}
    <div class="mx-auto w-full max-w-7xl px-6 pt-10 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <flux:heading size="xl" level="1">
                {{ __('site.about.title') }}
            </flux:heading>

            <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>

            <flux:text size="lg" class="mx-auto max-w-2xl">
                {{ __('site.about.description') }}
            </flux:text>
        </div>
    </div>

    {{-- Intro --}}
    <section class="relative overflow-hidden px-6 py-20 lg:px-8">
        {{-- Decorative blobs --}}
        <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-primary/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-primary/10 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl grid-cols-1 items-center gap-12 md:grid-cols-12">
            <div class="md:col-span-7">
                <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary">
                    AISC Madrid
                </span>

                <flux:heading size="lg" level="2" class="mt-5 text-3xl font-bold md:text-4xl">
                    {{ __('site.about.intro_heading') }}
                </flux:heading>

                <flux:text size="lg" class="mt-4 max-w-xl leading-relaxed">
                    {{ __('site.about.intro_body') }}
                </flux:text>
            </div>

            <div class="flex items-center justify-center md:col-span-5">
                <div class="relative">
                    <div class="absolute inset-0 -z-10 rounded-full bg-primary/15 blur-2xl"></div>
                    <img
                        src="{{ asset('images/logos/aisc-logo-color.svg') }}"
                        alt="{{ __('site.home.hero.logo_alt') }}"
                        width="280"
                        height="280"
                        class="w-2/3 max-w-xs drop-shadow-xl transition-transform duration-500 hover:scale-105"
                    >
                </div>
            </div>
        </div>
    </section>

    {{-- Mission --}}
    <section class="relative border-t border-border bg-gradient-to-b from-muted/40 to-transparent px-6 py-20 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto max-w-2xl text-center">
                <flux:heading size="lg" level="2" class="text-2xl font-bold md:text-3xl">
                    {{ __('site.about.mission_heading') }}
                </flux:heading>

                <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                @php
                    $missionIcons = ['rocket-launch', 'academic-cap', 'users'];
                @endphp

                @foreach (__('site.about.mission_items') as $item)
                    <flux:card class="group relative overflow-hidden p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-primary/10">
                        <div class="absolute top-0 right-0 h-24 w-24 -translate-y-8 translate-x-8 rounded-full bg-primary/5 transition-transform duration-300 group-hover:scale-125"></div>

                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/30">
                            <flux:icon name="{{ $missionIcons[$loop->index % count($missionIcons)] }}" class="size-6" />
                        </div>

                        <flux:heading size="base" class="relative mt-4">
                            {{ $item['title'] }}
                        </flux:heading>

                        <flux:text class="relative mt-2">
                            {{ $item['body'] }}
                        </flux:text>
                    </flux:card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="px-6 py-20 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto max-w-2xl text-center">
                <flux:heading size="lg" level="2" class="text-2xl font-bold md:text-3xl">
                    {{ __('site.about.values_heading') }}
                </flux:heading>

                <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $valueIcons = ['sparkles', 'light-bulb', 'heart', 'globe-alt'];
                @endphp

                @foreach (__('site.about.values_items') as $item)
                    <flux:card class="aisc-value-card group rounded-2xl border-l-4 border-l-primary/0 p-6 shadow-sm hover:border-l-primary">
                        <div class="aisc-value-icon flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white shadow-md shadow-primary/30">
                            <flux:icon name="{{ $valueIcons[$loop->index % count($valueIcons)] }}" class="size-5" />
                        </div>

                        <flux:heading size="base" class="mt-4">
                            {{ $item['title'] }}
                        </flux:heading>

                        <flux:text class="mt-2">
                            {{ $item['body'] }}
                        </flux:text>
                    </flux:card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- International network --}}
    <section class="border-t border-border bg-muted/40 px-6 py-16 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <flux:heading size="lg" level="2">
                {{ __('site.about.chapters_heading') }}
            </flux:heading>

            <flux:text size="lg" class="mt-4">
                {{ __('site.about.chapters_body') }}
            </flux:text>
        </div>
    </section>

</x-layouts.public>
