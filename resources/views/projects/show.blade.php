<x-layouts.public
    :title="(app()->getLocale() === 'es' ? $project->title_es : $project->title_en) . ' - AISC Madrid'"
>
    @php
        $isSpanish = app()->getLocale() === 'es';
        $title = $isSpanish ? $project->title_es : $project->title_en;
        $description = $isSpanish ? $project->description_es : $project->description_en;
    @endphp

    <section class="border-b border-border bg-muted/40">
        <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8 lg:py-16">
            <a href="{{ route('projects.index', ['locale' => app()->getLocale()]) }}" class="text-sm font-semibold text-primary hover:underline">
                &larr; {{ __('site.projects.back') }}
            </a>

            <div class="mt-8 grid grid-cols-1 gap-10 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-7">
                    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-primary">
                        {{ __('site.projects.categories.'.$project->category) }}
                    </span>

                    <flux:heading size="xl" level="1" class="mt-5">
                        {{ $title }}
                    </flux:heading>

                    <flux:text size="lg" class="mt-5 max-w-2xl">
                        {{ $description }}
                    </flux:text>
                </div>

                @if ($project->image_url)
                    <div class="overflow-hidden rounded-2xl shadow-lg lg:col-span-5">
                        <img src="{{ $project->image_url }}" alt="{{ $title }}" class="aspect-[4/3] w-full object-cover">
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-6 py-12 lg:grid-cols-12 lg:px-8 lg:py-16">
        <main class="lg:col-span-8">
            <flux:heading size="lg">{{ __('site.projects.about') }}</flux:heading>

            <div class="mt-6 whitespace-pre-line text-base leading-7 text-foreground">
                {{ $description }}
            </div>
        </main>

        <aside class="lg:col-span-4">
            <flux:card class="space-y-6 p-6">
                <div>
                    <flux:heading size="sm" class="uppercase tracking-wide text-muted-foreground">
                        {{ __('site.projects.date') }}
                    </flux:heading>
                    <flux:text class="mt-2">
                        {{ $project->start_date->format('m/Y') }}
                        @if ($project->end_date)
                            &mdash; {{ $project->end_date->format('m/Y') }}
                        @endif
                    </flux:text>
                </div>

                @if ($project->team_credit)
                    <div class="border-t border-border pt-6">
                        <flux:heading size="sm" class="uppercase tracking-wide text-muted-foreground">
                            {{ __('site.projects.team') }}
                        </flux:heading>
                        <flux:text class="mt-2">{{ $project->team_credit }}</flux:text>
                    </div>
                @endif

                @if ($project->tags)
                    <div class="border-t border-border pt-6">
                        <flux:heading size="sm" class="uppercase tracking-wide text-muted-foreground">
                            {{ __('site.projects.technologies') }}
                        </flux:heading>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($project->tags as $tag)
                                <span class="rounded-full border border-border px-3 py-1 text-sm text-muted-foreground">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($project->github_url || $project->external_url)
                    <div class="flex flex-wrap gap-3 border-t border-border pt-6">
                        @if ($project->github_url)
                            <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer" class="rounded-full border border-border px-4 py-2 text-sm font-medium hover:border-primary hover:text-primary">
                                {{ __('site.projects.view_code') }}
                            </a>
                        @endif

                        @if ($project->external_url)
                            <a href="{{ $project->external_url }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                                {{ __('site.projects.view_site') }}
                            </a>
                        @endif
                    </div>
                @endif
            </flux:card>
        </aside>
    </section>
</x-layouts.public>
