<x-layouts::app :title="$contact->full_name">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:link :href="route('dashboard.contacts.index')" wire:navigate class="text-sm">← {{ __('Volver a contactos') }}</flux:link>
                <flux:heading size="xl" level="1" class="mt-2">{{ $contact->full_name }}</flux:heading>
                <flux:text class="mt-1">{{ $contact->email }}</flux:text>
            </div>
            <flux:button icon="pencil-square" :href="route('dashboard.contacts.edit', $contact)" wire:navigate>{{ __('Editar') }}</flux:button>
        </div>

        <section class="grid gap-4 rounded-xl border border-zinc-200 p-5 sm:grid-cols-2 dark:border-zinc-700">
            <div>
                <flux:text class="text-xs">{{ __('Categoría') }}</flux:text>
                <div>{{ $contact->category ?: '—' }}</div>
            </div>
            <div>
                <flux:text class="text-xs">{{ __('Organización') }}</flux:text>
                <div>{{ $contact->organization ?: '—' }}</div>
            </div>
            <div>
                <flux:text class="text-xs">{{ __('Nombre para el saludo') }}</flux:text>
                <div>{{ $contact->greetingName() }}</div>
            </div>
            <div>
                <flux:text class="text-xs">{{ __('Activo') }}</flux:text>
                <flux:badge size="sm" :color="$contact->active ? 'green' : 'zinc'">{{ $contact->active ? __('Sí') : __('No') }}</flux:badge>
            </div>
            @if ($contact->notes)
                <div class="sm:col-span-2">
                    <flux:text class="text-xs">{{ __('Notas') }}</flux:text>
                    <div class="whitespace-pre-line">{{ $contact->notes }}</div>
                </div>
            @endif
        </section>

        <section class="flex flex-col gap-3">
            <flux:heading size="lg" level="2">{{ __('Emails enviados') }}</flux:heading>

            @if ($contact->emailLogs->isEmpty())
                <flux:text data-test="email-logs-empty">{{ __('Todavía no se ha enviado ningún email a este contacto.') }}</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Plantilla') }}</flux:table.column>
                        <flux:table.column>{{ __('Enviado') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($contact->emailLogs as $log)
                            <flux:table.row :key="$log->id">
                                <flux:table.cell>{{ $log->template_name }}</flux:table.cell>
                                <flux:table.cell class="tabular-nums">{{ \Illuminate\Support\Carbon::parse($log->sent_at)->format('d/m/Y H:i') }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </section>
    </div>
</x-layouts::app>