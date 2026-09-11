<x-layouts.public :title="__('site.projects.title') . ' - AISC Madrid'">

    {{-- Page header --}}
    <div class="mx-auto w-full max-w-7xl px-6 pt-10 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <flux:heading size="xl" level="1">
                {{ __('site.projects.title') }}
            </flux:heading>

            <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>

            <flux:text size="lg" class="mx-auto max-w-2xl">
                {{ __('site.projects.description') }}
            </flux:text>
        </div>

        {{-- Category filters --}}
        <div class="mt-10 flex flex-wrap justify-center gap-3">
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

    {{-- Timeline --}}
    <section class="px-6 py-16 lg:px-8">
        <div class="relative mx-auto max-w-4xl">

            {{-- Spine --}}
            <div class="absolute left-4 top-2 bottom-2 w-px bg-border md:left-1/2"></div>

            <div class="space-y-14">
                @foreach ($projects as $project)
                    @php
                        $isEven = $loop->index % 2 === 0;

                        $accentDot = $isEven ? 'bg-primary' : 'bg-secondary-foreground';
                        $accentBorder = $isEven ? 'border-primary' : 'border-secondary-foreground';
                        $accentBg = $isEven ? 'bg-primary/10' : 'bg-secondary-foreground/10';
                        $accentText = $isEven ? 'text-primary' : 'text-secondary-foreground';

                        $title = app()->getLocale() === 'en' ? $project->title_en : $project->title_es;
                        $description = app()->getLocale() === 'en' ? $project->description_en : $project->description_es;

                        $dateRange = $project->start_date->format('m/Y');

                        if ($project->end_date && $project->end_date->format('Y-m') !== $project->start_date->format('Y-m')) {
                            $dateRange .= ' — '.$project->end_date->format('m/Y');
                        }
                    @endphp

                    <div
                        class="project-node relative md:grid md:grid-cols-2 md:gap-x-14"
                        data-category="{{ $project->category }}"
                    >
                        {{-- Dot --}}
                        <div class="absolute left-4 top-2 z-10 size-3 -translate-x-1/2 rounded-full {{ $accentDot }} ring-4 ring-background md:left-1/2"></div>

                        @if ($isEven)
                            <div class="pl-10 md:col-start-1 md:pl-0 md:pr-4 md:text-right">
                                <x-project-card
                                    :project="$project"
                                    :title="$title"
                                    :description="$description"
                                    :date-range="$dateRange"
                                    :accent-border="$accentBorder"
                                    :accent-bg="$accentBg"
                                    :accent-text="$accentText"
                                    align="right"
                                />
                            </div>
                        @else
                            <div class="pl-10 md:col-start-2 md:pl-4">
                                <x-project-card
                                    :project="$project"
                                    :title="$title"
                                    :description="$description"
                                    :date-range="$dateRange"
                                    :accent-border="$accentBorder"
                                    :accent-bg="$accentBg"
                                    :accent-text="$accentText"
                                    align="left"
                                />
                            </div>
                        @endif
                    </div>
                @endforeach

                {{-- What's next --}}
                <div class="relative md:grid md:grid-cols-2 md:gap-x-14">
                    <div class="absolute left-4 top-2 z-10 size-3 -translate-x-1/2 rounded-full border-2 border-dashed border-muted-foreground bg-background md:left-1/2"></div>

                    <div class="pl-10 md:col-start-1 md:pl-0 md:pr-4 md:text-right">
                        <flux:heading size="base" class="text-muted-foreground">
                            {{ __('site.projects.more_soon') }}
                        </flux:heading>

                        <flux:text class="mt-2 text-muted-foreground">
                            {{ __('site.projects.more_soon_body') }}
                        </flux:text>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- Filtering --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const buttons = document.querySelectorAll('.project-filter');
            const nodes = document.querySelectorAll('.project-node');

            buttons.forEach(button => {

                button.addEventListener('click', function () {

                    buttons.forEach(btn => {
                        btn.classList.remove('bg-primary', 'text-white');
                        btn.classList.add('border', 'border-border', 'text-muted-foreground');
                    });

                    this.classList.add('bg-primary', 'text-white');
                    this.classList.remove('border', 'border-border', 'text-muted-foreground');

                    const filter = this.dataset.filter;

                    nodes.forEach(node => {
                        node.style.display =
                            filter === 'all' || node.dataset.category === filter
                                ? ''
                                : 'none';
                    });

                });

            });

        });

    </script>

</x-layouts.public>
