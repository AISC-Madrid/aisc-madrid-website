<x-layouts::app :title="__('Panel de control')">
    @php
        $member = auth()->user();
        $firstName = \Illuminate\Support\Str::before(trim((string) $member?->name), ' ') ?: __('equipo');
        $canSeeUsers = $member instanceof \App\Models\Member && $member->hasAnyRole(['admin', 'events']);
        $maxSignups = max(1, $signupsByMonth->max('total'));
        $signupsInChart = $signupsByMonth->sum('total');
    @endphp

    <div class="flex w-full flex-1 flex-col gap-6">
        {{-- Greeting --}}
        <div>
            <flux:heading size="xl" level="1">{{ __('Hola, :name', ['name' => $firstName]) }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Panel de control') }} · {{ now()->locale('es')->translatedFormat('l j \d\e F \d\e Y') }}
            </flux:text>
        </div>

        {{-- Key numbers --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-test="dashboard-stats">
            <x-dashboard.stat-card :label="__('Usuarios registrados')" :value="number_format($users['total'], 0, ',', '.')" icon="users">
                <span class="font-medium text-green-600 dark:text-green-400">+{{ $users['newLast30Days'] }}</span>
                {{ __('en los últimos 30 días') }}
            </x-dashboard.stat-card>

            <x-dashboard.stat-card :label="__('Suscritos a la newsletter')" :value="number_format($users['newsletter'], 0, ',', '.')" icon="envelope">
                {{ __(':rate% del total · :count bajas', ['rate' => $users['newsletterRate'], 'count' => $users['unsubscribed']]) }}
            </x-dashboard.stat-card>

            <x-dashboard.stat-card :label="__('Eventos programados')" :value="$events['upcoming']" icon="calendar-days">
                @if ($events['totalRegistrations'] !== null)
                    {{ __(':count inscripciones en total', ['count' => number_format($events['totalRegistrations'], 0, ',', '.')]) }}
                @else
                    {{ __(':count eventos realizados', ['count' => $events['past']]) }}
                @endif
            </x-dashboard.stat-card>

            <x-dashboard.stat-card :label="__('Aplicantes recruiting')" :value="$recruiting['applicants'] ?? '—'" icon="briefcase">
                {{ __('Proceso :cohort', ['cohort' => $recruiting['cohort']]) }}
            </x-dashboard.stat-card>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            {{-- Sign-ups per month --}}
            <section class="rounded-xl border border-zinc-200 bg-white p-5 xl:col-span-2 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <flux:heading size="lg" level="2">{{ __('Nuevos registros por mes') }}</flux:heading>
                    <flux:text class="text-sm">{{ __(':count en los últimos 12 meses', ['count' => $signupsInChart]) }}</flux:text>
                </div>

                <div class="mt-6 flex h-48 items-end gap-1.5 sm:gap-2" role="img" aria-label="{{ __('Nuevos registros por mes') }}" data-test="signups-chart">
                    @foreach ($signupsByMonth as $point)
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1" title="{{ $point['month']->locale('es')->translatedFormat('F Y') }}: {{ $point['total'] }}">
                            <span class="text-xs tabular-nums text-zinc-500 dark:text-zinc-400">{{ $point['total'] ?: '' }}</span>
                            <div
                                @class([
                                    'w-full rounded-t-md transition-colors',
                                    'bg-primary hover:bg-primary/80' => $point['total'] > 0,
                                    'bg-zinc-100 dark:bg-zinc-800' => $point['total'] === 0,
                                ])
                                style="height: {{ $point['total'] > 0 ? max(4, round($point['total'] / $maxSignups * 100)) : 2 }}%"
                            ></div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-2 flex gap-1.5 sm:gap-2">
                    @foreach ($signupsByMonth as $point)
                        <span class="flex-1 text-center text-xs capitalize text-zinc-500 dark:text-zinc-400">
                            {{ $point['month']->locale('es')->translatedFormat('M') }}
                        </span>
                    @endforeach
                </div>
            </section>

            {{-- Community --}}
            <section class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" level="2">{{ __('Comunidad') }}</flux:heading>

                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Miembros activos') }}</dt>
                        <dd class="mt-1 text-2xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ $community['activeMembers'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Junta directiva') }}</dt>
                        <dd class="mt-1 text-2xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ $community['boardMembers'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Eventos realizados') }}</dt>
                        <dd class="mt-1 text-2xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ $events['past'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Proyectos') }}</dt>
                        <dd class="mt-1 text-2xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ $community['projects'] }}</dd>
                    </div>
                </dl>

                <div class="mt-auto">
                    <div class="flex justify-between text-sm">
                        <flux:text>{{ __('Newsletter') }}</flux:text>
                        <flux:text class="tabular-nums">{{ $users['newsletterRate'] }}%</flux:text>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full rounded-full bg-primary" style="width: {{ $users['newsletterRate'] }}%"></div>
                    </div>
                    <flux:text class="mt-2 text-xs">
                        {{ __(':subscribed suscritos · :unsubscribed sin newsletter', ['subscribed' => $users['newsletter'], 'unsubscribed' => $users['unsubscribed']]) }}
                    </flux:text>
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            {{-- Latest subscribers --}}
            <section class="rounded-xl border border-zinc-200 bg-white p-5 xl:col-span-2 dark:border-zinc-700 dark:bg-zinc-900" data-test="latest-subscribers">
                <div class="flex items-center justify-between gap-2">
                    <flux:heading size="lg" level="2">{{ __('Últimos suscriptores') }}</flux:heading>

                    @if ($canSeeUsers)
                        <flux:link :href="route('dashboard.users.index')" wire:navigate class="text-sm">{{ __('Ver todos') }}</flux:link>
                    @endif
                </div>

                @if ($latestSubscribers->isEmpty())
                    <flux:text class="mt-4">{{ __('Todavía no hay usuarios registrados.') }}</flux:text>
                @else
                    <flux:table class="mt-2">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Nombre') }}</flux:table.column>
                            <flux:table.column>{{ __('Email') }}</flux:table.column>
                            <flux:table.column>{{ __('Fecha') }}</flux:table.column>
                            <flux:table.column>{{ __('Newsletter') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($latestSubscribers as $subscriber)
                                <flux:table.row :key="$subscriber->id">
                                    <flux:table.cell variant="strong">{{ $subscriber->full_name ?: '—' }}</flux:table.cell>
                                    <flux:table.cell>{{ $subscriber->email }}</flux:table.cell>
                                    <flux:table.cell>{{ $subscriber->submitted_at?->format('d/m/Y H:i') ?? '—' }}</flux:table.cell>
                                    <flux:table.cell>
                                        @if ($subscriber->newsletter)
                                            <flux:badge color="green" size="sm" inset="top bottom">{{ __('Activo') }}</flux:badge>
                                        @else
                                            <flux:badge color="red" size="sm" inset="top bottom">{{ __('Inactivo') }}</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </section>

            {{-- Next events --}}
            <section class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900" data-test="next-events">
                <flux:heading size="lg" level="2">{{ __('Próximos eventos') }}</flux:heading>

                @if ($nextEvents->isEmpty())
                    <flux:text class="mt-4">{{ __('No hay eventos programados.') }}</flux:text>
                @else
                    <ul class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($nextEvents as $event)
                            @php $start = $event->start_datetime->setTimezone('Europe/Madrid'); @endphp
                            <li class="flex gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="flex w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-primary/10 py-1.5 text-primary">
                                    <span class="text-lg font-semibold leading-none">{{ $start->format('d') }}</span>
                                    <span class="text-xs uppercase">{{ $start->locale('es')->translatedFormat('M') }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('events.show', ['locale' => 'es', 'event' => $event->id]) }}" class="block truncate font-medium text-zinc-900 hover:text-primary dark:text-white">
                                        {{ $event->title_es }}
                                    </a>
                                    <flux:text class="truncate text-sm">
                                        {{ $start->format('H:i') }}@if ($event->location) · {{ $event->location }}@endif
                                    </flux:text>
                                </div>

                                @if ($registrationsByEvent->has($event->id))
                                    <flux:badge size="sm" class="self-center" title="{{ __('Inscripciones') }}">
                                        {{ $registrationsByEvent->get($event->id) }}
                                    </flux:badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-layouts::app>
