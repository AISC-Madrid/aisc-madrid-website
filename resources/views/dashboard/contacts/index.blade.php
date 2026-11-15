<x-layouts::app :title="__('Contactos')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Contactos') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ trans_choice(':count contacto|:count contactos', $contacts->total(), ['count' => $contacts->total()]) }}
                </flux:text>
            </div>

            <div class="flex w-full flex-col gap-2 lg:w-auto lg:flex-row">
                <form method="GET" action="{{ route('dashboard.contacts.index') }}" class="flex flex-col gap-2 sm:flex-row">
                    <flux:input
                        name="search"
                        :value="$search"
                        icon="magnifying-glass"
                        :placeholder="__('Buscar por nombre, email u organización')"
                        :aria-label="__('Buscar por nombre, email u organización')"
                        class="sm:w-72"
                    />
                    <flux:select name="category" class="sm:w-48" :aria-label="__('Categoría')">
                        <flux:select.option value="">{{ __('Todas las categorías') }}</flux:select.option>
                        @foreach ($categories as $name)
                            <flux:select.option :value="$name" :selected="$name === $category">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:button type="submit">{{ __('Filtrar') }}</flux:button>
                </form>

                <flux:button variant="primary" icon="plus" :href="route('dashboard.contacts.create')" wire:navigate>
                    {{ __('Crear contacto') }}
                </flux:button>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        @if ($contacts->isEmpty())
            <flux:text data-test="contacts-empty">
                {{ ($search !== '' || $category !== '') ? __('No hay contactos que coincidan con la búsqueda.') : __('No se encontraron contactos.') }}
            </flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nombre') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Categoría') }}</flux:table.column>
                    <flux:table.column>{{ __('Organización') }}</flux:table.column>
                    <flux:table.column align="center">{{ __('Activo') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('Acciones') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($contacts as $contact)
                        <flux:table.row :key="$contact->id">
                            <flux:table.cell class="max-w-56 whitespace-normal">
                                <a href="{{ route('dashboard.contacts.show', $contact) }}" class="font-medium text-zinc-900 hover:text-primary dark:text-white">
                                    {{ $contact->full_name }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $contact->email }}</flux:table.cell>
                            <flux:table.cell>{{ $contact->category ?: '—' }}</flux:table.cell>
                            <flux:table.cell class="max-w-56 whitespace-normal">{{ $contact->organization ?: '—' }}</flux:table.cell>
                            <flux:table.cell align="center">
                                <flux:badge size="sm" :color="$contact->active ? 'green' : 'zinc'">
                                    {{ $contact->active ? __('Sí') : __('No') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="ms-auto flex w-28 flex-col gap-1" data-test="contact-actions">
                                    <flux:button size="xs" icon="pencil-square" class="w-full justify-start!" :href="route('dashboard.contacts.edit', $contact)" wire:navigate>
                                        {{ __('Editar') }}
                                    </flux:button>

                                    <form
                                        method="POST"
                                        action="{{ route('dashboard.contacts.destroy', $contact) }}"
                                        x-data
                                        x-on:submit="if (! confirm(@js(__('¿Seguro que quieres eliminar a «:name»? No se puede deshacer.', ['name' => $contact->full_name])))) $event.preventDefault()"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <flux:button size="xs" variant="danger" icon="trash" type="submit" class="w-full justify-start!" :aria-label="__('Eliminar :name', ['name' => $contact->full_name])">
                                            {{ __('Eliminar') }}
                                        </flux:button>
                                    </form>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div>{{ $contacts->links() }}</div>
        @endif
    </div>
</x-layouts::app>