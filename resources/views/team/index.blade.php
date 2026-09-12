<x-layouts.public title="{{ __('site.team.title') }} - AISC Madrid">

    {{-- Page header --}}
    <div class="mx-auto w-full max-w-7xl px-6 pt-10 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <flux:heading size="xl" level="1">
                {{ __('team.heading') }}
            </flux:heading>

            <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>

            <flux:text size="lg" class="mx-auto max-w-2xl">
                {{ __('team.subheading') }}
            </flux:text>
        </div>
    </div>

    {{-- Active members --}}
    <section class="pb-16 pt-10">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($activeMembers as $member)
                    <x-team-member-card :member="$member" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Honor members --}}
    @if ($honorMembers->isNotEmpty())
        <section class="border-t border-border bg-muted/40 py-16">
            <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <flux:heading size="lg" level="2">
                        {{ __('team.honor_heading') }}
                    </flux:heading>

                    <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>

                    <flux:text size="lg" class="mx-auto max-w-2xl">
                        {{ __('team.honor_subheading') }}
                    </flux:text>
                </div>

                <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($honorMembers as $member)
                        <x-team-member-card :member="$member" :honor="true" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-layouts.public>
