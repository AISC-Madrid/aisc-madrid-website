@php
    $isEditing = $project->exists;
@endphp

<x-layouts::app :title="$isEditing ? __('Editar proyecto') : __('Crear proyecto')">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
        <div>
            <flux:link :href="route('dashboard.projects.index')" wire:navigate class="text-sm">← {{ __('Volver a proyectos') }}</flux:link>
            <flux:heading size="xl" level="1" class="mt-2">
                {{ $isEditing ? __('Editar proyecto') : __('Crear nuevo proyecto') }}
            </flux:heading>
            @if ($isEditing)
                <flux:text class="mt-1">{{ $project->title_es }}</flux:text>
            @endif
        </div>

        <form
            method="POST"
            action="{{ $isEditing ? route('dashboard.projects.update', $project) : route('dashboard.projects.store') }}"
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

            <section class="grid gap-6 sm:grid-cols-2">
                <flux:input name="title_es" :label="__('Título (español)')" :value="old('title_es', $project->title_es)" required />
                <flux:input name="title_en" :label="__('Title (English)')" :value="old('title_en', $project->title_en)" required />
                <flux:input name="category" :label="__('Categoría')" :value="old('category', $project->category)" required />
                <flux:input
                    name="tags"
                    :label="__('Etiquetas')"
                    :description="__('Separadas por comas.')"
                    :value="old('tags', implode(', ', $project->tags ?? []))"
                />
                <flux:input name="team_credit" :label="__('Créditos del equipo')" :value="old('team_credit', $project->team_credit)" class="sm:col-span-2" />
            </section>

            <section class="grid gap-6 sm:grid-cols-2">
                <flux:textarea name="description_es" :label="__('Descripción (español)')" rows="8" required>{{ old('description_es', $project->description_es) }}</flux:textarea>
                <flux:textarea name="description_en" :label="__('Description (English)')" rows="8" required>{{ old('description_en', $project->description_en) }}</flux:textarea>
            </section>

            <section class="grid gap-6 sm:grid-cols-2">
                <flux:input type="date" name="start_date" :label="__('Fecha de inicio')" :value="old('start_date', $project->start_date?->format('Y-m-d'))" required />
                <flux:input
                    type="date"
                    name="end_date"
                    :label="__('Fecha de fin')"
                    :description="__('Déjala vacía si el proyecto sigue en curso.')"
                    :value="old('end_date', $project->end_date?->format('Y-m-d'))"
                />
                <flux:input type="url" name="github_url" :label="__('URL de GitHub (opcional)')" :value="old('github_url', $project->github_url)" />
                <flux:input type="url" name="external_url" :label="__('URL externa (opcional)')" :value="old('external_url', $project->external_url)" />
            </section>

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

                    @if ($project->image_url)
                        <img src="{{ $project->image_url }}" alt="{{ __('Imagen actual') }}" class="w-40 rounded-lg border border-zinc-200 dark:border-zinc-700" data-test="current-image">
                    @endif
                </div>

                <div class="flex flex-col gap-3">
                    <flux:input
                        type="file"
                        name="gallery[]"
                        accept="image/*"
                        multiple
                        :label="__('Galería')"
                        :description="$isEditing ? __('Si subes fotos nuevas, sustituyen a toda la galería actual.') : __('Fotos adicionales del proyecto.')"
                    />
                    <flux:error name="gallery.*" />

                    @if (count($project->gallery_urls) > 0)
                        <div class="flex flex-wrap gap-2" data-test="current-gallery">
                            @foreach ($project->gallery_urls as $url)
                                <img src="{{ $url }}" alt="" class="size-16 rounded-md object-cover" loading="lazy">
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary" data-test="save-project">
                    {{ $isEditing ? __('Actualizar proyecto') : __('Guardar proyecto') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('dashboard.projects.index')" wire:navigate>{{ __('Cancelar') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>