<x-layouts.public :title="__('site.projects.title') . ' - AISC Madrid'">
    @php
        $isSpanish = app()->getLocale() === 'es';
    @endphp

    <section class="border-b border-border bg-muted/40">
        <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8 lg:py-20">
            <div class="max-w-3xl">
                <span class="text-sm font-semibold uppercase tracking-[0.2em] text-primary">
                    {{ __('site.projects.eyebrow') }}
                </span>

                <flux:heading size="xl" level="1" class="mt-4">
                    {{ __('site.projects.title') }}
                </flux:heading>

                <flux:text size="lg" class="mt-5 max-w-2xl">
                    {{ __('site.projects.description') }}
                </flux:text>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8 lg:py-16">
        <div class="flex flex-wrap items-center justify-between gap-5">
            <flux:heading size="lg">
                {{ __('site.projects.latest') }}
            </flux:heading>

            <div class="flex flex-wrap gap-2" aria-label="{{ __('site.projects.filter') }}">
                <button
                    type="button"
                    class="project-filter rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white transition"
                    data-filter="all"
                >
                    {{ __('site.projects.all') }}
                </button>

                @foreach ($categories as $category)
                    <button
                        type="button"
                        class="project-filter rounded-full border border-border px-4 py-2 text-sm font-medium text-muted-foreground transition hover:border-primary hover:text-primary"
                        data-filter="{{ $category }}"
                    >
                        {{ __('site.projects.categories.'.$category) }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($projects as $project)
                @php
                    $title = $isSpanish ? $project->title_es : $project->title_en;
                    $description = $isSpanish ? $project->description_es : $project->description_en;
                @endphp

                <a
                    href="{{ route('projects.show', ['locale' => app()->getLocale(), 'project' => $project]) }}"
                    class="project-node group flex h-full flex-col overflow-hidden rounded-2xl border border-border bg-surface transition hover:-translate-y-1 hover:border-primary hover:shadow-xl"
                    data-category="{{ $project->category }}"
                >
                    <div class="aspect-[16/10] overflow-hidden bg-muted">
                        @if ($project->image_url)
                            <img
                                src="{{ $project->image_url }}"
                                alt="{{ $title }}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            >
                        @else
                            <div class="flex h-full items-center justify-center bg-primary/10 text-4xl font-bold text-primary">
                                {{ str($title)->substr(0, 1)->upper() }}
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col gap-5 p-6">
                        <div class="flex items-start justify-between gap-4">
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-primary">
                                {{ __('site.projects.categories.'.$project->category) }}
                            </span>

                            <time datetime="{{ $project->start_date->toDateString() }}" class="shrink-0 text-sm text-muted-foreground">
                                {{ $project->start_date->format('m/Y') }}
                            </time>
                        </div>

                        <div>
                            <flux:heading size="lg">
                                {{ $title }}
                            </flux:heading>

                            <flux:text class="mt-3 line-clamp-3">
                                {{ $description }}
                            </flux:text>
                        </div>

                        <span class="mt-auto inline-flex items-center gap-2 text-sm font-semibold text-primary">
                            {{ __('site.projects.view_project') }}
                            <span aria-hidden="true" class="transition group-hover:translate-x-1">&rarr;</span>
                        </span>
                    </div>
                </a>
            @empty
                <flux:text class="col-span-full text-center text-muted-foreground">
                    {{ __('site.projects.empty') }}
                </flux:text>
            @endforelse
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('.project-filter');
            const nodes = document.querySelectorAll('.project-node');

            buttons.forEach(button => {
                button.addEventListener('click', function () {
                    buttons.forEach(filterButton => {
                        filterButton.classList.remove('bg-primary', 'text-white');
                        filterButton.classList.add('border', 'border-border', 'text-muted-foreground');
                    });

                    this.classList.add('bg-primary', 'text-white');
                    this.classList.remove('border', 'border-border', 'text-muted-foreground');

                    nodes.forEach(node => {
                        node.hidden = this.dataset.filter !== 'all' && node.dataset.category !== this.dataset.filter;
                    });
                });
            });
        });
    </script>
</x-layouts.public>
