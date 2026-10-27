<x-layouts::app :title="__('Eventos')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Lista de eventos') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ trans_choice(':count evento|:count eventos', $events->total(), ['count' => $events->total()]) }}
                </flux:text>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <form method="GET" action="{{ route('dashboard.events.index') }}" class="flex gap-2">
                    <flux:input
                        name="search"
                        :value="$search"
                        icon="magnifying-glass"
                        :placeholder="__('Buscar por título o ponente')"
                        :aria-label="__('Buscar por título o ponente')"
                        class="sm:w-64"
                    />
                    <flux:button type="submit">{{ __('Buscar') }}</flux:button>
                </form>

                @if ($canManage)
                    <flux:button variant="primary" icon="plus" :href="route('dashboard.events.create')" wire:navigate>
                        {{ __('Crear nuevo evento') }}
                    </flux:button>
                @endif
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        @if ($events->isEmpty())
            <flux:text data-test="events-empty">
                {{ $search !== '' ? __('No hay eventos que coincidan con la búsqueda.') : __('No se encontraron eventos.') }}
            </flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Evento') }}</flux:table.column>
                    <flux:table.column>{{ __('Tipo') }}</flux:table.column>
                    <flux:table.column>{{ __('Ponente') }}</flux:table.column>
                    <flux:table.column>{{ __('Fecha') }}</flux:table.column>
                    <flux:table.column>{{ __('Ubicación') }}</flux:table.column>
                    <flux:table.column align="center">{{ __('Registrados') }}</flux:table.column>
                    @if ($canManage)
                        <flux:table.column align="end">{{ __('Acciones') }}</flux:table.column>
                    @endif
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($events as $event)
                        @php
                            $start = $event->start_datetime->setTimezone('Europe/Madrid');
                            $end = $event->end_datetime->setTimezone('Europe/Madrid');
                            $isUpcoming = $event->end_datetime->isFuture();
                        @endphp

                        <flux:table.row :key="$event->id">
                            <flux:table.cell class="max-w-72 whitespace-normal">
                                <div class="flex items-center gap-3">
                                    @if ($event->image_url)
                                        <img src="{{ $event->image_url }}" alt="" class="size-10 shrink-0 rounded-md object-cover" loading="lazy">
                                    @else
                                        <div class="size-10 shrink-0 rounded-md bg-zinc-100 dark:bg-zinc-800"></div>
                                    @endif

                                    <div class="min-w-0">
                                        <a href="{{ route('events.show', ['locale' => 'es', 'event' => $event->id]) }}" target="_blank" class="font-medium text-zinc-900 hover:text-primary dark:text-white">
                                            {{ $event->title_es }}
                                        </a>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $event->title_en }}</div>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($event->type_es || $event->type_en)
                                    <div>{{ $event->type_es }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $event->type_en }}</div>
                                @else
                                    —
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="max-w-48 whitespace-normal">{{ $event->speaker }}</flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <span class="tabular-nums">{{ $start->format('d/m/Y H:i') }}</span>
                                    @if ($isUpcoming)
                                        <flux:badge color="green" size="sm">{{ __('Próximo') }}</flux:badge>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-500 tabular-nums dark:text-zinc-400">
                                    {{ __('hasta') }} {{ $start->isSameDay($end) ? $end->format('H:i') : $end->format('d/m/Y H:i') }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-56 whitespace-normal">{{ $event->location ?: '—' }}</flux:table.cell>

                            <flux:table.cell align="center">
                                <flux:badge size="sm" :color="$registrationCounts->get($event->id) ? 'pink' : 'zinc'">
                                    {{ $registrationCounts->get($event->id, 0) }}
                                </flux:badge>
                                @if ($event->requires_registration)
                                    <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Requiere registro') }}</div>
                                @endif
                            </flux:table.cell>

                            @if ($canManage)
                                <flux:table.cell align="end">
                                    <div class="flex justify-end gap-1">
                                        <flux:button size="sm" icon="pencil-square" :href="route('dashboard.events.edit', $event)" wire:navigate>
                                            {{ __('Editar') }}
                                        </flux:button>

                                        <flux:button size="sm" icon="document-arrow-down" :href="route('dashboard.events.attendees-pdf', $event)" target="_blank" :title="__('Descargar lista de asistentes en PDF')">
                                            PDF
                                        </flux:button>

                                        <form
                                            method="POST"
                                            action="{{ route('dashboard.events.destroy', $event) }}"
                                            x-data
                                            x-on:submit="if (! confirm(@js(__('¿Seguro que quieres eliminar «:title»? No se puede deshacer.', ['title' => $event->title_es])))) $event.preventDefault()"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <flux:button size="sm" variant="danger" icon="trash" type="submit" :aria-label="__('Eliminar :title', ['title' => $event->title_es])" />
                                        </form>
                                    </div>
                                </flux:table.cell>
                            @endif
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div>{{ $events->links() }}</div>
        @endif
    </div>
</x-layouts::app>
