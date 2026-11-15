<x-layouts::app :title="__('Invitados')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Invitados') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ trans_choice(':count invitado|:count invitados', $guests->total(), ['count' => $guests->total()]) }}
                    · {{ __('Acceso externo a la lista de asistentes de sus eventos.') }}
                </flux:text>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <form method="GET" action="{{ route('dashboard.guests.index') }}" class="flex gap-2">
                    <flux:input
                        name="search"
                        :value="$search"
                        icon="magnifying-glass"
                        :placeholder="__('Buscar por usuario, nombre o email')"
                        :aria-label="__('Buscar por usuario, nombre o email')"
                        class="sm:w-64"
                    />
                    <flux:button type="submit">{{ __('Buscar') }}</flux:button>
                </form>

                <flux:button variant="primary" icon="plus" :href="route('dashboard.guests.create')" wire:navigate>
                    {{ __('Crear invitado') }}
                </flux:button>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        @if ($guests->isEmpty())
            <div class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-zinc-300 py-12 text-center dark:border-zinc-700" data-test="guests-empty">
                <flux:icon.inbox class="size-8 text-zinc-400" />
                <flux:text>
                    {{ $search !== '' ? __('No hay invitados que coincidan con la búsqueda.') : __('Todavía no hay invitados.') }}
                </flux:text>
                @if ($search === '')
                    <flux:button size="sm" variant="primary" icon="plus" :href="route('dashboard.guests.create')" wire:navigate>
                        {{ __('Crear el primer invitado') }}
                    </flux:button>
                @endif
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Invitado') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Estado') }}</flux:table.column>
                    <flux:table.column>{{ __('Eventos') }}</flux:table.column>
                    <flux:table.column>{{ __('Creado') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('Acciones') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($guests as $guest)
                        <flux:table.row :key="$guest->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$guest->name ?: $guest->username" />
                                    <div class="min-w-0">
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $guest->name ?: '—' }}</div>
                                        <div class="font-mono text-xs text-zinc-500 dark:text-zinc-400">{{ $guest->username }}</div>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($guest->email)
                                    <flux:link href="mailto:{{ $guest->email }}" variant="subtle">{{ $guest->email }}</flux:link>
                                @else
                                    —
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($guest->is_active)
                                    <flux:badge color="green" size="sm" icon="check-circle">{{ __('Activo') }}</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm" icon="x-circle">{{ __('Inactivo') }}</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="max-w-72 whitespace-normal">
                                @if ($guest->events->isNotEmpty())
                                    <flux:badge color="pink" size="sm">
                                        {{ trans_choice(':count evento|:count eventos', $guest->events->count(), ['count' => $guest->events->count()]) }}
                                    </flux:badge>
                                    <div class="mt-1 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400" title="{{ $guest->events->pluck('title_es')->join(', ') }}">
                                        {{ $guest->events->pluck('title_es')->join(', ') }}
                                    </div>
                                @else
                                    <span class="text-zinc-400">{{ __('Sin eventos') }}</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="tabular-nums">
                                {{ $guest->created_at?->setTimezone('Europe/Madrid')->format('d/m/Y H:i') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <div class="ms-auto flex w-28 flex-col gap-1" data-test="guest-actions">
                                    <flux:button size="xs" icon="pencil-square" class="w-full justify-start!" :href="route('dashboard.guests.edit', $guest)" wire:navigate>
                                        {{ __('Editar') }}
                                    </flux:button>

                                    <form
                                        method="POST"
                                        action="{{ route('dashboard.guests.destroy', $guest) }}"
                                        x-data
                                        x-on:submit="if (! confirm(@js(__('¿Seguro que quieres eliminar al invitado «:username»? Perderá el acceso a sus eventos.', ['username' => $guest->username])))) $event.preventDefault()"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <flux:button size="xs" variant="danger" icon="trash" type="submit" class="w-full justify-start!" :aria-label="__('Eliminar :username', ['username' => $guest->username])">
                                            {{ __('Eliminar') }}
                                        </flux:button>
                                    </form>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div>{{ $guests->links() }}</div>
        @endif
    </div>
</x-layouts::app>
