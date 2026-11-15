@php
    $roleColors = ['admin' => 'red', 'events' => 'pink', 'web' => 'sky', 'finance' => 'amber', 'marketing' => 'violet'];
@endphp

<x-layouts::app :title="__('Equipo')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Miembros del equipo') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ trans_choice(':count miembro|:count miembros', $members->total(), ['count' => $members->total()]) }}
                </flux:text>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <form method="GET" action="{{ route('dashboard.team-members.index') }}" class="flex gap-2">
                    <flux:input
                        name="search"
                        :value="$search"
                        icon="magnifying-glass"
                        :placeholder="__('Buscar por nombre, email o posición')"
                        :aria-label="__('Buscar por nombre, email o posición')"
                        class="sm:w-64"
                    />
                    <flux:button type="submit">{{ __('Buscar') }}</flux:button>
                </form>

                <flux:button variant="primary" icon="plus" :href="route('dashboard.team-members.create')" wire:navigate>
                    {{ __('Añadir miembro') }}
                </flux:button>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        @error('member')
            <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
        @enderror

        @if ($members->isEmpty())
            <div class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-zinc-300 py-12 text-center dark:border-zinc-700" data-test="members-empty">
                <flux:icon.user-group class="size-8 text-zinc-400" />
                <flux:text>
                    {{ $search !== '' ? __('No hay miembros que coincidan con la búsqueda.') : __('Todavía no hay miembros.') }}
                </flux:text>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Miembro') }}</flux:table.column>
                    <flux:table.column>{{ __('Posición') }}</flux:table.column>
                    <flux:table.column>{{ __('Contacto') }}</flux:table.column>
                    <flux:table.column>{{ __('DNI') }}</flux:table.column>
                    <flux:table.column>{{ __('Rol') }}</flux:table.column>
                    <flux:table.column>{{ __('Estado') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('Acciones') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($members as $member)
                        <flux:table.row :key="$member->id">
                            <flux:table.cell class="max-w-64 whitespace-normal">
                                <div class="flex items-center gap-3">
                                    @if ($member->image_url)
                                        <img src="{{ $member->image_url }}" alt="" class="size-10 shrink-0 rounded-full object-cover" loading="lazy">
                                    @else
                                        <flux:avatar circle :name="$member->name" :initials="$member->initials()" />
                                    @endif

                                    <div class="min-w-0">
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $member->full_name ?: '—' }}</div>
                                        @if (filter_var($member->socials, FILTER_VALIDATE_URL))
                                            <a href="{{ $member->socials }}" target="_blank" rel="noopener" class="block truncate text-xs text-zinc-500 hover:text-primary dark:text-zinc-400">
                                                {{ \Illuminate\Support\Str::after($member->socials, '://') }}
                                            </a>
                                        @elseif ($member->socials)
                                            <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $member->socials }}</div>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-48 whitespace-normal">
                                <div>{{ $member->position_es ?: '—' }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $member->position_en }}</div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($member->mail)
                                    <flux:link href="mailto:{{ $member->mail }}" variant="subtle">{{ $member->mail }}</flux:link>
                                @else
                                    —
                                @endif
                                <div class="text-xs text-zinc-500 tabular-nums dark:text-zinc-400">{{ $member->phone }}</div>
                            </flux:table.cell>

                            <flux:table.cell class="font-mono text-xs">{{ $member->dni ?: '—' }}</flux:table.cell>

                            <flux:table.cell>
                                @if ($member->role)
                                    <flux:badge size="sm" :color="$roleColors[$member->role->value] ?? 'zinc'">{{ ucfirst($member->role->value) }}</flux:badge>
                                @else
                                    —
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1">
                                    @if ($member->alumniHonor)
                                        <flux:badge size="sm" color="amber" icon="star">{{ __('Honor') }}</flux:badge>
                                    @elseif ($member->active)
                                        <flux:badge size="sm" color="green">{{ __('Activo') }}</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="zinc">{{ __('Inactivo') }}</flux:badge>
                                    @endif

                                    @if ($member->board)
                                        <flux:badge size="sm" color="indigo">{{ __('Board') }}</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <div class="ms-auto flex w-28 flex-col gap-1" data-test="member-actions">
                                    <flux:button size="xs" icon="pencil-square" class="w-full justify-start!" :href="route('dashboard.team-members.edit', $member)" wire:navigate>
                                        {{ __('Editar') }}
                                    </flux:button>

                                    @unless (auth()->user()?->is($member))
                                        <form
                                            method="POST"
                                            action="{{ route('dashboard.team-members.destroy', $member) }}"
                                            x-data
                                            x-on:submit="if (! confirm(@js(__('¿Seguro que quieres eliminar a «:name»? No se puede deshacer.', ['name' => $member->full_name])))) $event.preventDefault()"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <flux:button size="xs" variant="danger" icon="trash" type="submit" class="w-full justify-start!" :aria-label="__('Eliminar :name', ['name' => $member->full_name])">
                                                {{ __('Eliminar') }}
                                            </flux:button>
                                        </form>
                                    @endunless
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div>{{ $members->links() }}</div>
        @endif
    </div>
</x-layouts::app>
