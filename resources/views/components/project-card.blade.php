@props([
    'project',
    'title',
    'description',
    'dateRange',
    'accentBorder',
    'accentBg',
    'accentText',
    'align' => 'left',
])

@php
    $isRight = $align === 'right';
@endphp

<flux:card class="group relative overflow-hidden border-l-4 p-6 transition hover:-translate-y-1 hover:shadow-xl {{ $accentBorder }}">
    <div @class([
        'flex flex-wrap items-center gap-2',
        'justify-end' => $isRight,
    ])>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $accentBg }} {{ $accentText }}">
            {{ __('site.projects.categories.'.$project->category) }}
        </span>

        <flux:text size="sm" class="text-muted-foreground">
            {{ $dateRange }}
        </flux:text>
    </div>

    <flux:heading size="lg" class="mt-4">
        {{ $title }}
    </flux:heading>

    <flux:text class="mt-3">
        {{ $description }}
    </flux:text>

    @if ($project->tags)
        <div @class([
            'mt-4 flex flex-wrap gap-2',
            'justify-end' => $isRight,
        ])>
            @foreach ($project->tags as $tag)
                <span class="rounded-full border border-border px-2.5 py-1 text-xs text-muted-foreground">
                    {{ $tag }}
                </span>
            @endforeach
        </div>
    @endif

    @if ($project->team_credit)
        <flux:text size="sm" class="mt-4 block text-muted-foreground">
            <strong>{{ __('site.projects.team') }}:</strong> {{ $project->team_credit }}
        </flux:text>
    @endif

    @if ($project->github_url || $project->external_url)
        <div @class([
            'mt-5 flex flex-wrap gap-3',
            'justify-end' => $isRight,
        ])>
            @if ($project->github_url)
                <a
                    href="{{ $project->github_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-medium text-foreground transition hover:border-primary hover:text-primary"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 .5C5.73.5.5 5.73.5 12c0 5.09 3.29 9.4 7.86 10.93.57.1.79-.25.79-.55v-2.1c-3.2.7-3.88-1.4-3.88-1.4-.52-1.33-1.28-1.68-1.28-1.68-1.05-.72.08-.71.08-.71 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.23-1.28-5.23-5.68 0-1.26.45-2.28 1.19-3.09-.12-.29-.52-1.46.11-3.04 0 0 .97-.31 3.18 1.18a11.1 11.1 0 012.9-.39c.98 0 1.97.13 2.9.39 2.2-1.49 3.17-1.18 3.17-1.18.63 1.58.23 2.75.11 3.04.74.81 1.19 1.83 1.19 3.09 0 4.41-2.69 5.38-5.25 5.67.41.36.78 1.08.78 2.17v3.22c0 .3.21.66.8.55A10.53 10.53 0 0023.5 12C23.5 5.73 18.27.5 12 .5z" />
                    </svg>

                    {{ __('site.projects.view_code') }}
                </a>
            @endif

            @if ($project->external_url)
                <a
                    href="{{ $project->external_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90"
                >
                    {{ __('site.projects.view_site') }}
                </a>
            @endif
        </div>
    @endif
</flux:card>
