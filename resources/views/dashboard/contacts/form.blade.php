@php
    $isEditing = $contact->exists;
@endphp

<x-layouts::app :title="$isEditing ? __('Editar contacto') : __('Crear contacto')">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <div>
            <flux:link :href="route('dashboard.contacts.index')" wire:navigate class="text-sm">← {{ __('Volver a contactos') }}</flux:link>
            <flux:heading size="xl" level="1" class="mt-2">
                {{ $isEditing ? __('Editar contacto') : __('Crear nuevo contacto') }}
            </flux:heading>
            @if ($isEditing)
                <flux:text class="mt-1">{{ $contact->full_name }}</flux:text>
            @endif
        </div>

        <form
            method="POST"
            action="{{ $isEditing ? route('dashboard.contacts.update', $contact) : route('dashboard.contacts.store') }}"
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
                <flux:input name="full_name" :label="__('Nombre completo')" :value="old('full_name', $contact->full_name)" required />
                <flux:input
                    name="greeting_name"
                    :label="__('Nombre para el saludo')"
                    :description="__('Si lo dejas vacío se usa el primer nombre.')"
                    :value="old('greeting_name', $contact->greeting_name)"
                />
                <flux:input type="email" name="email" :label="__('Email')" :value="old('email', $contact->email)" required class="sm:col-span-2" />
                <flux:input
                    name="category"
                    list="contact-categories"
                    :label="__('Categoría')"
                    :value="old('category', $contact->category)"
                    required
                />
                <datalist id="contact-categories">
                    @foreach ($categories as $name)
                        <option value="{{ $name }}"></option>
                    @endforeach
                </datalist>
                <flux:input name="organization" :label="__('Organización')" :value="old('organization', $contact->organization)" />
                <flux:textarea name="notes" :label="__('Notas')" rows="4" class="sm:col-span-2">{{ old('notes', $contact->notes) }}</flux:textarea>
            </section>

            <flux:checkbox
                name="active"
                value="1"
                :label="__('Contacto activo')"
                :checked="(bool) old('active', $contact->active)"
            />

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary" data-test="save-contact">
                    {{ $isEditing ? __('Actualizar contacto') : __('Guardar contacto') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('dashboard.contacts.index')" wire:navigate>{{ __('Cancelar') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>