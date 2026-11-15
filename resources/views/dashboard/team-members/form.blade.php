@php
    $isEditing = $teamMember->exists;
    $currentRole = old('role', $teamMember->role?->value ?? \App\Enums\MemberRole::Marketing->value);
    $isHonor = (bool) old('is_honor', $teamMember->alumniHonor !== null);
@endphp

<x-layouts::app :title="$isEditing ? __('Editar miembro') : __('Añadir miembro')">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
        <div>
            <flux:link :href="route('dashboard.team-members.index')" wire:navigate class="text-sm">← {{ __('Volver al equipo') }}</flux:link>
            <flux:heading size="xl" level="1" class="mt-2">
                {{ $isEditing ? __('Editar miembro') : __('Añadir nuevo miembro') }}
            </flux:heading>
            @if ($isEditing)
                <flux:text class="mt-1">{{ $teamMember->full_name }}</flux:text>
            @endif
        </div>

        <form
            method="POST"
            action="{{ $isEditing ? route('dashboard.team-members.update', $teamMember) : route('dashboard.team-members.store') }}"
            enctype="multipart/form-data"
            class="flex flex-col gap-8"
        >
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            @if ($errors->any())
                <flux:callout variant="danger" icon="exclamation-triangle" :heading="__('Revisa los campos marcados.')" />
            @endif

            {{-- Account --}}
            <section class="grid gap-6 sm:grid-cols-2">
                <flux:input name="full_name" :label="__('Nombre y apellidos')" :value="old('full_name', $teamMember->full_name)" required />
                <flux:input type="email" name="mail" :label="__('Email (inicio de sesión)')" :value="old('mail', $teamMember->mail)" required />

                <div
                    x-data="{
                        generate() {
                            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
                            const values = crypto.getRandomValues(new Uint32Array(12));
                            this.$root.querySelector('input[name=password]').value = Array.from(values, (value) => chars[value % chars.length]).join('');
                        },
                    }"
                    class="flex items-end gap-2"
                >
                    <flux:input
                        name="password"
                        :label="__('Contraseña')"
                        :description="$isEditing ? __('Déjalo vacío para mantener la actual.') : __('Mínimo 6 caracteres.')"
                        autocomplete="new-password"
                        class="font-mono"
                        :required="! $isEditing"
                    />
                    <flux:button type="button" icon="sparkles" x-on:click="generate()" :tooltip="__('Generar contraseña')" :aria-label="__('Generar contraseña')" />
                </div>

                <flux:select name="role" :label="__('Rol')" :description="__('Define los permisos en el panel.')">
                    @foreach (\App\Enums\MemberRole::cases() as $role)
                        <flux:select.option :value="$role->value" :selected="$currentRole === $role->value">{{ ucfirst($role->value) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </section>

            {{-- Public profile --}}
            <section class="grid gap-6 sm:grid-cols-2">
                <flux:input name="position_es" :label="__('Posición (español)')" :value="old('position_es', $teamMember->position_es)" placeholder="Miembro" />
                <flux:input name="position_en" :label="__('Position (English)')" :value="old('position_en', $teamMember->position_en)" placeholder="Member" />
                <flux:input name="phone" :label="__('Teléfono')" :value="old('phone', $teamMember->phone)" />
                <flux:input name="dni" :label="__('DNI')" :value="old('dni', $teamMember->dni)" maxlength="9" />
                <flux:input name="socials" :label="__('Red social (URL)')" :value="old('socials', $teamMember->socials)" placeholder="https://www.linkedin.com/in/…" class="sm:col-span-2" />

                <div class="flex flex-col gap-3 sm:col-span-2">
                    <flux:input
                        type="file"
                        name="image"
                        accept="image/*"
                        :label="__('Foto')"
                        :description="$isEditing ? __('Déjalo vacío para mantener la actual.') : __('Se convierte a WebP y se sube al bucket.')"
                    />

                    @if ($teamMember->image_url)
                        <img src="{{ $teamMember->image_url }}" alt="{{ __('Foto actual') }}" class="size-24 rounded-full border border-zinc-200 object-cover dark:border-zinc-700" data-test="current-image">
                    @endif
                </div>
            </section>

            {{-- Visibility --}}
            <section class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700" x-data="{ honor: @js($isHonor) }">
                <flux:checkbox
                    name="active"
                    value="1"
                    :label="__('Activo')"
                    :description="__('Los miembros activos aparecen en la página de equipo.')"
                    :checked="(bool) old('active', $teamMember->active)"
                />

                <flux:checkbox
                    name="board"
                    value="1"
                    :label="__('Board')"
                    :description="__('Forma parte de la junta directiva.')"
                    :checked="(bool) old('board', $teamMember->board)"
                />

                <div x-on:change="honor = $event.target.checked">
                    <flux:checkbox
                        name="is_honor"
                        value="1"
                        :label="__('Miembro de honor')"
                        :description="__('Aparece en la sección de alumni de la página de equipo.')"
                        :checked="$isHonor"
                    />
                </div>

                <div x-show="honor" x-cloak class="grid gap-6 sm:grid-cols-2">
                    <flux:input
                        type="number"
                        name="graduation_year"
                        :label="__('Año de graduación')"
                        :description="__('Dos cifras, ej: 26 (Clase de 26).')"
                        :value="old('graduation_year', $teamMember->alumniHonor?->graduation_year)"
                        min="0"
                        placeholder="26"
                    />
                    <flux:textarea name="honor_quote" :label="__('Frase personal')" rows="2" :placeholder="__('Una frase que te defina…')">{{ old('honor_quote', $teamMember->alumniHonor?->honor_quote) }}</flux:textarea>
                </div>
            </section>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary" data-test="save-member">
                    {{ $isEditing ? __('Actualizar miembro') : __('Guardar miembro') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('dashboard.team-members.index')" wire:navigate>{{ __('Cancelar') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
