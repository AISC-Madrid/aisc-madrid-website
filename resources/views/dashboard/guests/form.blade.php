@php
    $isEditing = $guest->exists;
    $checkedEventIds = array_map('intval', old('events', $selectedEventIds));
@endphp

<x-layouts::app :title="$isEditing ? __('Editar invitado') : __('Crear invitado')">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
        <div>
            <flux:link :href="route('dashboard.guests.index')" wire:navigate class="text-sm">← {{ __('Volver a invitados') }}</flux:link>
            <flux:heading size="xl" level="1" class="mt-2">
                {{ $isEditing ? __('Editar invitado') : __('Crear nuevo invitado') }}
            </flux:heading>
            <flux:text class="mt-1">
                {{ $isEditing ? $guest->username : __('El invitado podrá iniciar sesión y ver los asistentes de los eventos que selecciones.') }}
            </flux:text>
        </div>

        <form
            method="POST"
            action="{{ $isEditing ? route('dashboard.guests.update', $guest) : route('dashboard.guests.store') }}"
            class="flex flex-col gap-8"
        >
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            @if ($errors->any())
                <flux:callout variant="danger" icon="exclamation-triangle" :heading="__('Revisa los campos marcados.')" />
            @endif

            {{-- Credentials --}}
            <section class="grid gap-6 sm:grid-cols-2">
                <flux:input name="username" :label="__('Usuario')" :value="old('username', $guest->username)" placeholder="ej: spotify_talk" autocomplete="off" required />

                <div
                    x-data="{
                        generate() {
                            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
                            const values = crypto.getRandomValues(new Uint32Array(12));
                            this.$root.querySelector('input[name=password]').value =Array.from(values, (value) => chars[value % chars.length]).join('');
                        },
                    }"
                    class="flex items-end gap-2"
                >
                    <flux:input
                        name="password"
                        :label="__('Contraseña')"
                        :description="$isEditing ? __('Déjalo vacío para mantener la actual.') : __('Mínimo 6 caracteres. Compártela con el invitado.')"
                        autocomplete="new-password"
                        class="font-mono"
                        :required="! $isEditing"
                    />
                    <flux:button type="button" icon="sparkles" x-on:click="generate()" :tooltip="__('Generar contraseña')" :aria-label="__('Generar contraseña')" />
                </div>

                <flux:input name="name" :label="__('Nombre')" :value="old('name', $guest->name)" placeholder="ej: Juan García" />
                <flux:input type="email" name="email" :label="__('Email')" :value="old('email', $guest->email)" placeholder="juan@empresa.com" />

                <div class="sm:col-span-2">
                    <flux:checkbox
                        name="is_active"
                        value="1"
                        :label="__('Cuenta activa')"
                        :description="__('Si la desactivas, el invitado no podrá iniciar sesión.')"
                        :checked="(bool) old('is_active', $guest->is_active ?? true)"
                    />
                </div>
            </section>

            {{-- Event access --}}
            <section
                class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700"
                x-data="{ filter: '' }"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Eventos con acceso') }}</flux:heading>
                        <flux:text class="mt-1">{{ __('Elige qué eventos puede ver este invitado.') }}</flux:text>
                    </div>
                    <flux:input x-model="filter" size="sm" icon="magnifying-glass" :placeholder="__('Filtrar eventos')" :aria-label="__('Filtrar eventos')" class="sm:w-56" />
                </div>

                <flux:error name="events" />
                <flux:error name="events.*" />

                @if ($events->isEmpty())
                    <flux:text>{{ __('No hay eventos creados.') }}</flux:text>
                @else
                    <div class="max-h-96 divide-y divide-zinc-100 overflow-y-auto rounded-lg border border-zinc-100 dark:divide-zinc-800 dark:border-zinc-800" data-test="guest-events">
                        @foreach ($events as $event)
                            @php $start = $event->start_datetime?->setTimezone('Europe/Madrid'); @endphp
                            <label
                                class="flex cursor-pointer items-center gap-3 px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800/60"
                                x-show="@js(\Illuminate\Support\Str::lower($event->title_es)).includes(filter.toLowerCase())"
                            >
                                <input
                                    type="checkbox"
                                    name="events[]"
                                    value="{{ $event->id }}"
                                    @checked(in_array($event->id, $checkedEventIds, true))
                                    class="size-4 rounded border-zinc-300 accent-pink-600 dark:border-zinc-600"
                                >
                                <span class="min-w-0 flex-1 truncate text-sm text-zinc-800 dark:text-zinc-100">{{ $event->title_es }}</span>
                                @if ($start?->isFuture())
                                    <flux:badge color="green" size="sm">{{ __('Próximo') }}</flux:badge>
                                @endif
                                <span class="shrink-0 text-xs text-zinc-500 tabular-nums dark:text-zinc-400">{{ $start?->format('d/m/Y') }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </section>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary" data-test="save-guest">
                    {{ $isEditing ? __('Actualizar invitado') : __('Crear invitado') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('dashboard.guests.index')" wire:navigate>{{ __('Cancelar') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
