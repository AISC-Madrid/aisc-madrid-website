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
    <section class="px-6 py-16 lg:px-8">
        <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-10 md:grid-cols-12">
            <div class="md:col-span-7">
                <flux:heading size="lg" level="2">
                    {{ __('site.about.intro_heading') }}
                </flux:heading>

                <flux:text size="lg" class="mt-4">
                    {{ __('site.about.intro_body') }}
                </flux:text>
            </div>

            <div class="flex items-center justify-center md:col-span-5">
                <img
                    src="{{ asset('images/logos/aisc-logo-color.svg') }}"
                    alt="{{ __('site.home.hero.logo_alt') }}"
                    width="280"
                    height="280"
                    class="w-2/3 max-w-xs"
                >
            </div>
        </div>
    </section>

    {{-- Mission --}}
    <section class="border-t border-border bg-muted/40 px-6 py-16 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto max-w-2xl text-center">
                <flux:heading size="lg" level="2">
                    {{ __('site.about.mission_heading') }}
                </flux:heading>

                <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach (__('site.about.mission_items') as $item)
                    <flux:card class="p-6">
                        <flux:heading size="base">
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

    {{-- Values --}}
    <section class="px-6 py-16 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto max-w-2xl text-center">
                <flux:heading size="lg" level="2">
                    {{ __('site.about.values_heading') }}
                </flux:heading>

                <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (__('site.about.values_items') as $item)
                    <flux:card class="p-6">
                        <flux:heading size="base">
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
