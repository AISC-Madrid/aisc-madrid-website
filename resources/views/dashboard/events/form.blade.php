@php
    $isEditing = $event->exists;
    $madridValue = fn ($date): string => $date ? $date->setTimezone('Europe/Madrid')->format('Y-m-d\TH:i') : '';
@endphp

<x-layouts::app :title="$isEditing ? __('Editar evento') : __('Crear evento')">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
        <div>
            <flux:link :href="route('dashboard.events.index')" wire:navigate class="text-sm">← {{ __('Volver a eventos') }}</flux:link>
            <flux:heading size="xl" level="1" class="mt-2">
                {{ $isEditing ? __('Editar evento') : __('Crear nuevo evento') }}
            </flux:heading>
            @if ($isEditing)
                <flux:text class="mt-1">{{ $event->title_es }}</flux:text>
            @endif
        </div>

        <form
            method="POST"
            action="{{ $isEditing ? route('dashboard.events.update', $event) : route('dashboard.events.store') }}"
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

            {{-- Basic information --}}
            <section class="grid gap-6 sm:grid-cols-2">
                <flux:input name="title_es" :label="__('Título (español)')" :value="old('title_es', $event->title_es)" required />
                <flux:input name="title_en" :label="__('Title (English)')" :value="old('title_en', $event->title_en)" required />
                <flux:input name="type_es" :label="__('Tipo (español)')" :value="old('type_es', $event->type_es)" placeholder="Evento, Taller…" />
                <flux:input name="type_en" :label="__('Type (English)')" :value="old('type_en', $event->type_en)" placeholder="Event, Workshop…" />
                <flux:input name="speaker" :label="__('Ponente')" :value="old('speaker', $event->speaker)" />
                <flux:input name="company" :label="__('Empresa')" :value="old('company', $event->company)" placeholder="Spotify" />
            </section>

            <section class="grid gap-6 sm:grid-cols-2">
                <flux:textarea name="description_es" :label="__('Descripción (español)')" rows="8" required>{{ old('description_es', $event->description_es) }}</flux:textarea>
                <flux:textarea name="description_en" :label="__('Description (English)')" rows="8" required>{{ old('description_en', $event->description_en) }}</flux:textarea>
                <flux:text class="text-xs sm:col-span-2">
                    {{ __('Los saltos de línea se respetan en la web. Puedes usar HTML sencillo como <strong>, <ul> y <li>.') }}
                </flux:text>
            </section>

            {{-- When and where --}}
            <section class="grid gap-6 sm:grid-cols-2">
                <flux:input
                    type="datetime-local"
                    name="start_datetime"
                    :label="__('Inicio (hora de Madrid)')"
                    :value="old('start_datetime', $madridValue($event->start_datetime))"
                    required
                />
                <flux:input
                    type="datetime-local"
                    name="end_datetime"
                    :label="__('Fin (hora de Madrid)')"
                    :value="old('end_datetime', $madridValue($event->end_datetime))"
                    required
                />
                <flux:input name="location" :label="__('Ubicación')" :value="old('location', $event->location)" class="sm:col-span-2" />
                <flux:input type="url" name="youtube_url" :label="__('URL de YouTube (opcional)')" :value="old('youtube_url', $event->youtube_url)" class="sm:col-span-2" />
            </section>

            {{-- Images --}}
            <section class="grid gap-6 sm:grid-cols-2">
                <div class="flex flex-col gap-3">
                    <flux:input
                        type="file"
                        name="image"
                        accept="image/*"
                        :label="__('Imagen principal')"
                        :description="$isEditing ? __('Déjalo vacío para mantener la actual.') : __('Se convierte a WebP y se sube al bucket.')"
                        :required="! $isEditing"
                    />

                    @if ($event->image_url)
                        <img src="{{ $event->image_url }}" alt="{{ __('Imagen actual') }}" class="w-40 rounded-lg border border-zinc-200 dark:border-zinc-700" data-test="current-image">
                    @endif
                </div>

                <div class="flex flex-col gap-3">
                    <flux:input
                        type="file"
                        name="gallery[]"
                        accept="image/*"
                        multiple
                        :label="__('Fotos del evento')"
                        :description="$isEditing ? __('Si subes fotos nuevas, sustituyen a toda la galería actual.') : __('Se muestran en un carrusel en la página del evento.')"
                    />
                    <flux:error name="gallery.*" />

                    @if (count($event->gallery_urls) > 0)
                        <div class="flex flex-wrap gap-2" data-test="current-gallery">
                            @foreach ($event->gallery_urls as $url)
                                <img src="{{ $url }}" alt="" class="size-16 rounded-md object-cover" loading="lazy">
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            {{-- Registration and reminder --}}
            <section class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:checkbox
                    name="requires_registration"
                    value="1"
                    :label="__('Requiere inscripción')"
                    :checked="(bool) old('requires_registration', $event->requires_registration)"
                />

                <flux:checkbox
                    name="reminder_enabled"
                    value="1"
                    :label="__('Enviar recordatorio por email antes del evento')"
                    :checked="(bool) old('reminder_enabled', $event->reminder_enabled)"
                />

                <flux:select name="reminder_days_before" :label="__('Días antes del evento')" class="max-w-48">
                    @foreach (range(1, 7) as $days)
                        <flux:select.option :value="$days" :selected="(int) old('reminder_days_before', $event->reminder_days_before ?? 2) === $days">
                            {{ trans_choice(':count día|:count días', $days, ['count' => $days]) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </section>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary" data-test="save-event">
                    {{ $isEditing ? __('Actualizar evento') : __('Guardar evento') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('dashboard.events.index')" wire:navigate>{{ __('Cancelar') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
