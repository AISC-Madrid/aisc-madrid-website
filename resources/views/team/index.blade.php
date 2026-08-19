<x-layouts.public :title="__('site.team.title') . ' - AISC Madrid'">
    <div class="flex min-h-screen flex-col items-center">

        {{-- Active team --}}
        <section class="w-full scroll-mt-24 px-6 py-20 md:px-12">
            <div class="mx-auto max-w-7xl">
                <h2 class="text-center text-3xl font-bold md:text-4xl">
                    {{ __('site.team.title') }}
                </h2>

                <div class="mx-auto mt-4 h-[3px] w-16 rounded-full bg-primary"></div>

                <p class="mx-auto mt-6 max-w-2xl text-center text-base leading-8 text-muted-foreground">
                    {{ __('site.team.description') }}
                </p>

                @if ($activeMembers->isEmpty())
                    <p class="mt-12 text-center text-muted-foreground">
                        {{ __('site.team.empty') }}
                    </p>
                @else
                    <div class="mt-12 grid grid-cols-2 gap-8 md:grid-cols-4">
                        @foreach ($activeMembers as $member)
                            <x-team-member-card :member="$member" />
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- Honor members --}}
        @if ($honorMembers->isNotEmpty())
            <section class="w-full scroll-mt-24 bg-muted/30 px-6 py-20 md:px-12">
                <div class="mx-auto max-w-7xl">
                    <h2 class="text-center text-3xl font-bold md:text-4xl">
                        {{ __('site.team.honor_heading') }}
                    </h2>

                    <div class="mx-auto mt-4 h-[3px] w-16 rounded-full bg-primary"></div>

                    <p class="mx-auto mt-6 max-w-2xl text-center text-base leading-8 text-muted-foreground">
                        {{ __('site.team.honor_description') }}
                    </p>

                    <div class="mt-12 grid grid-cols-2 justify-items-center gap-8 md:grid-cols-4">
                        @foreach ($honorMembers as $member)
                            <x-team-member-card :member="$member" :honor="true" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

    </div>
</x-layouts.public>