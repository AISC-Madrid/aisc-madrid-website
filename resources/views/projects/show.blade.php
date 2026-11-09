<x-layouts.public
    :title="(app()->getLocale() === 'es' ? $project->title_es : $project->title_en) . ' - AISC Madrid'"
>
    @php
        $isSpanish = app()->getLocale() === 'es';
        $title = $isSpanish ? $project->title_es : $project->title_en;
        $description = $isSpanish ? $project->description_es : $project->description_en;
        $dateRange = $project->start_date->format('d/m/Y');

        if ($project->end_date) {
            $dateRange .= ' - '.$project->end_date->format('d/m/Y');
        }
    @endphp

    <section class="border-b border-border bg-muted/40">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-6 py-14 md:grid-cols-12 lg:px-8">
            <div class="order-2 flex flex-col justify-center md:order-1 md:col-span-7">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-primary">
                        {{ __('site.projects.categories.'.$project->category) }}
                    </span>
                </div>

                <flux:heading size="xl" level="1" class="mt-4">
                    {{ $title }}
                </flux:heading>

                @if ($project->team_credit)
                    <flux:text size="lg" class="mt-4">
                        <strong>{{ __('site.projects.team') }}</strong>
                        {{ $project->team_credit }}
                    </flux:text>
                @endif

                <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3">
                    <div class="flex items-center gap-2 text-foreground">
                        <flux:icon.calendar class="size-5 shrink-0 text-primary" />

                        <flux:text>
                            <strong>{{ $dateRange }}</strong>
                        </flux:text>
                    </div>
                </div>
            </div>

            <div class="order-1 flex items-center justify-center md:order-2 md:col-span-5">
                @if ($project->image_url)
                    <div class="aspect-square w-full overflow-hidden rounded-2xl shadow-lg">
                        <img src="{{ $project->image_url }}" alt="{{ $title }}" class="h-full w-full object-cover">
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-14 lg:grid lg:grid-cols-12 lg:gap-10 lg:px-8">
        <main class="lg:col-span-8">
            <div
                class="whitespace-pre-line text-base leading-7 text-foreground
                    [&_h2]:text-2xl [&_h2]:font-bold
                    [&_h3]:text-xl [&_h3]:font-semibold
                    [&_ul]:my-2 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-6 [&_ul]:whitespace-normal
                    [&_ol]:my-2 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-6 [&_ol]:whitespace-normal
                    [&_strong]:font-semibold
                    [&_a]:text-primary [&_a]:underline [&_a]:underline-offset-2"
                data-test="project-description"
            >{!! trim((string) $description) !!}</div>
        </main>

        <aside class="mt-10 lg:col-span-4 lg:mt-0">
            <flux:card class="sticky top-28 space-y-6 p-6">
                <div>
                    <flux:heading size="sm" class="uppercase tracking-wide text-muted-foreground">
                        {{ __('site.projects.technologies') }}
                    </flux:heading>

                    @if ($project->tags)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($project->tags as $tag)
                                <span class="rounded-full border border-border px-3 py-1 text-sm text-muted-foreground">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="mt-2 text-muted-foreground">&mdash;</flux:text>
                    @endif
                </div>

                @if ($project->github_url || $project->external_url)
                    <div class="flex flex-wrap gap-3 border-t border-border pt-6">
                        @if ($project->github_url)
                            <a
                                href="{{ $project->github_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-medium text-foreground transition hover:border-primary hover:text-primary"
                            >
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

                <div class="relative border-t border-border pt-6">
                    <button
                        type="button"
                        onclick="toggleShareMenu()"
                        class="share-button inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-medium text-foreground transition hover:border-primary hover:text-primary"
                    >
                        {{ __('site.projects.share') }}

                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div id="share-menu" class="hidden absolute left-0 z-10 mt-2 w-44 overflow-hidden rounded-xl border border-border bg-surface p-1 shadow-xl">
                        <button
                            type="button"
                            onclick="copyProjectLink()"
                            class="block w-full rounded-lg px-3 py-2 text-left text-sm text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        >
                            <span id="copy-link-label">{{ __('site.projects.copy_link') }}</span>
                        </button>

                        <a href="https://api.whatsapp.com/send?text={{ urlencode($title . ' ' . url()->current()) }}" target="_blank" rel="noopener noreferrer" class="block rounded-lg px-3 py-2 text-sm text-muted-foreground transition hover:bg-muted hover:text-foreground">WhatsApp</a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer" class="block rounded-lg px-3 py-2 text-sm text-muted-foreground transition hover:bg-muted hover:text-foreground">LinkedIn</a>
                        <a href="https://x.com/intent/tweet/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer" class="block rounded-lg px-3 py-2 text-sm text-muted-foreground transition hover:bg-muted hover:text-foreground">X</a>
                    </div>
                </div>
            </flux:card>
        </aside>
    </section>

    <script>
        const projectUrl = @json(url()->current());

        function toggleShareMenu() {
            document.getElementById('share-menu')?.classList.toggle('hidden');
        }

        async function copyProjectLink() {
            const label = document.getElementById('copy-link-label');

            try {
                if (navigator.clipboard) {
                    await navigator.clipboard.writeText(projectUrl);
                } else {
                    const input = document.createElement('textarea');
                    input.value = projectUrl;
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    input.remove();
                }

                if (label) {
                    label.textContent = @json(__('site.projects.link_copied'));
                    window.setTimeout(() => {
                        label.textContent = @json(__('site.projects.copy_link'));
                    }, 2000);
                }
            } catch {
                if (label) {
                    label.textContent = @json(__('site.projects.copy_failed'));
                }
            }
        }

        document.addEventListener('click', function (event) {
            const menu = document.getElementById('share-menu');

            if (menu && !menu.contains(event.target) && !event.target.closest('.share-button')) {
                menu.classList.add('hidden');
            }
        });
    </script>
</x-layouts.public>
