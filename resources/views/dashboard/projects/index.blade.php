<x-layouts::app :title="__('Proyectos')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Proyectos') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ trans_choice(':count proyecto|:count proyectos', $projects->total(), ['count' => $projects->total()]) }}
                </flux:text>
            </div>

            <flux:button variant="primary" icon="plus" :href="route('dashboard.projects.create')" wire:navigate>
                {{ __('Crear proyecto') }}
            </flux:button>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        @if ($projects->isEmpty())
            <flux:text data-test="projects-empty">{{ __('No se encontraron proyectos.') }}</flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Proyecto') }}</flux:table.column>
                    <flux:table.column>{{ __('Estado') }}</flux:table.column>
                    <flux:table.column>{{ __('Categoría') }}</flux:table.column>
                    <flux:table.column>{{ __('Fechas') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('Acciones') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($projects as $project)
                        @php
                            [$phaseLabel, $phaseColor] = match ($project->phase) {
                                'upcoming' => [__('Próximo'), 'blue'],
                                'finished' => [__('Finalizado'), 'zinc'],
                                default => [__('En curso'), 'green'],
                            };
                        @endphp

                        <flux:table.row :key="$project->id">
                            <flux:table.cell class="max-w-72 whitespace-normal">
                                <div class="flex items-center gap-3">
                                    @if ($project->image_url)
                                        <img src="{{ $project->image_url }}" alt="" class="size-10 shrink-0 rounded-md object-cover" loading="lazy">
                                    @else
                                        <div class="size-10 shrink-0 rounded-md bg-zinc-100 dark:bg-zinc-800"></div>
                                    @endif

                                    <div class="min-w-0">
                                        <a href="{{ route('projects.show', ['locale' => 'es', 'project' => $project->id]) }}" target="_blank" class="font-medium text-zinc-900 hover:text-primary dark:text-white">
                                            {{ $project->title_es }}
                                        </a>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $project->title_en }}</div>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$phaseColor">{{ $phaseLabel }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>{{ $project->category ?: '—' }}</flux:table.cell>

                            <flux:table.cell class="tabular-nums">
                                {{ $project->start_date?->format('d/m/Y') ?? '—' }}
                                →
                                {{ $project->end_date?->format('d/m/Y') ?? __('actualidad') }}
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <div class="ms-auto flex w-28 flex-col gap-1" data-test="project-actions">
                                    <flux:button size="xs" icon="pencil-square" class="w-full justify-start!" :href="route('dashboard.projects.edit', $project)" wire:navigate>
                                        {{ __('Editar') }}
                                    </flux:button>

                                    <form
                                        method="POST"
                                        action="{{ route('dashboard.projects.destroy', $project) }}"
                                        x-data
                                        x-on:submit="if (! confirm(@js(__('¿Seguro que quieres eliminar «:title»? No se puede deshacer.', ['title' => $project->title_es])))) $event.preventDefault()"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <flux:button size="xs" variant="danger" icon="trash" type="submit" class="w-full justify-start!" :aria-label="__('Eliminar :title', ['title' => $project->title_es])">
                                            {{ __('Eliminar') }}
                                        </flux:button>
                                    </form>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div>{{ $projects->links() }}</div>
        @endif
    </div>
</x-layouts::app>