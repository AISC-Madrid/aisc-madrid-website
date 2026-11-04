<x-layouts.public :title="__('site.projects.title') . ' - AISC Madrid'">
    <div class="mx-auto w-full max-w-7xl px-6 pt-10 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <flux:heading size="xl" level="1">
                {{ __('site.projects.title') }}
            </flux:heading>

            <div class="mx-auto my-4 h-1 w-15 rounded-full bg-[var(--primary)]"></div>

            <flux:text size="lg" class="mx-auto max-w-2xl">
                {{ __('site.projects.description') }}
            </flux:text>
        </div>
    </div>

    <section class="pb-16 pt-10">
        <div class="mx-auto w-full max-w-5xl px-6 lg:px-8">
            <div class="mb-8 flex flex-wrap justify-center gap-2 sm:justify-end">
                <flux:button.group>
                    <flux:button variant="ghost" class="filter-button" data-filter="all">
                        {{ __('site.projects.all') }}
                    </flux:button>

                    @foreach ($categories as $category)
                        <flux:button variant="ghost" class="filter-button" data-filter="{{ $category }}">
                            {{ __('site.projects.categories.'.$category) }}
                        </flux:button>
                    @endforeach
                </flux:button.group>
            </div>

            <div class="relative">
                <div class="absolute bottom-0 left-4 top-0 w-px bg-border md:left-1/2"></div>

                <div class="space-y-8">
                    @forelse ($projects as $project)
                        @php
                            $isRight = $loop->index % 2 === 0;
                            $title = app()->getLocale() === 'en' ? $project->title_en : $project->title_es;
                            $description = app()->getLocale() === 'en' ? $project->description_en : $project->description_es;
                        @endphp

                        <div
                            class="project-card relative md:grid md:grid-cols-2 md:gap-10"
                            data-category="{{ $project->category }}"
                        >
                            <div class="absolute left-4 top-8 z-10 size-3 -translate-x-1/2 rounded-full bg-primary ring-4 ring-background md:left-1/2"></div>

                            <div @class([
                                'order-1 pl-10 pt-2 md:pt-6',
                                'md:col-start-2 md:pl-5 md:text-left' => $isRight,
                                'md:col-start-1 md:pr-5 md:text-right' => ! $isRight,
                            ])>
                                <time
                                    datetime="{{ $project->start_date->toDateString() }}"
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    {{ $project->start_date->format('m/Y') }}
                                </time>
                            </div>

                            <div @class([
                                'order-2 pl-10',
                                'md:col-start-1 md:pl-0 md:pr-5' => $isRight,
                                'md:col-start-2 md:pl-5' => ! $isRight,
                            ])>
                                <a
                                    href="{{ route('projects.show', ['locale' => request()->route('locale'), 'project' => $project->id]) }}"
                                    class="block h-full"
                                >
                                    <flux:card class="relative flex h-full flex-col overflow-hidden text-left transition hover:-translate-y-1 hover:shadow-xl md:text-left">
                                        @if ($project->image_url)
                                            <div class="aspect-video w-full overflow-hidden">
                                                <img
                                                    src="{{ $project->image_url }}"
                                                    alt="{{ $title }}"
                                                    class="h-full w-full object-cover transition duration-500 hover:scale-105"
                                                >
                                            </div>
                                        @endif

                                        <div class="flex flex-1 flex-col p-5">
                                            <flux:heading size="lg">
                                                {{ $title }}
                                            </flux:heading>

                                            <flux:text class="mt-3 line-clamp-3">
                                                {{ $description }}
                                            </flux:text>
                                        </div>

                                        <div class="px-5 pb-5">
                                            <div class="flex items-center justify-between gap-4 border-t border-border pt-4">
                                                <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                                    {{ __('site.projects.categories.'.$project->category) }}
                                                </span>

                                                <flux:text class="font-medium text-primary">
                                                    {{ __('site.projects.view_project') }}
                                                </flux:text>
                                            </div>
                                        </div>
                                    </flux:card>
                                </a>
                            </div>
                        </div>
                    @empty
                        <flux:text class="pl-10 text-center text-muted-foreground">
                            {{ __('site.projects.empty') }}
                        </flux:text>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('.filter-button');
            const cards = document.querySelectorAll('.project-card');

            function setActiveButton(button) {
                buttons.forEach(btn => {
                    btn.style.backgroundColor = '';
                    btn.style.color = '';
                });

                button.style.backgroundColor = 'var(--primary)';
                button.style.color = 'var(--primary-foreground)';
            }

            buttons.forEach(button => {
                button.addEventListener('click', function () {
                    setActiveButton(this);

                    cards.forEach(card => {
                        card.style.display = this.dataset.filter === 'all' || card.dataset.category === this.dataset.filter
                            ? ''
                            : 'none';
                    });
                });
            });

            setActiveButton(document.querySelector('.filter-button[data-filter="all"]'));
        });
    </script>
</x-layouts.public>
