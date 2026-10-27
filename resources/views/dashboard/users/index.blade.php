<x-layouts::app :title="__('Usuarios')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Usuarios registrados') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ __('Personas apuntadas a través del formulario o la newsletter.') }}
                    <span data-test="users-total">{{ trans_choice(':count usuario|:count usuarios', $users->total(), ['count' => $users->total()]) }}</span>
                </flux:text>
            </div>

            <form method="GET" action="{{ route('dashboard.users.index') }}" class="flex w-full gap-2 sm:w-auto">
                <flux:input
                    name="search"
                    :value="$search"
                    icon="magnifying-glass"
                    :placeholder="__('Buscar por nombre o email')"
                    :aria-label="__('Buscar por nombre o email')"
                    class="sm:w-72"
                />
                <flux:button type="submit">{{ __('Buscar') }}</flux:button>

                @if ($search !== '')
                    <flux:button variant="ghost" :href="route('dashboard.users.index')">{{ __('Limpiar') }}</flux:button>
                @endif
            </form>
        </div>

        @if ($users->isEmpty())
            <flux:text data-test="users-empty">
                {{ $search !== '' ? __('No hay usuarios que coincidan con la búsqueda.') : __('No hay usuarios registrados.') }}
            </flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nombre') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Newsletter') }}</flux:table.column>
                    <flux:table.column>{{ __('Fecha de alta') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell variant="strong">{{ $user->full_name ?: '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($user->newsletter)
                                    <flux:badge color="green" size="sm" inset="top bottom">{{ __('Sí') }}</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm" inset="top bottom">{{ __('No') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $user->submitted_at?->format('d/m/Y H:i') ?? '—' }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            {{-- Plain links: Flux's pagination needs a Livewire component (wire:click). --}}
            <div data-test="users-pagination">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-layouts::app>
