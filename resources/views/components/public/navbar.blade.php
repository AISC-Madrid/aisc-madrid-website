@php
$locale = app()->getLocale();

$currentRoute = request()->route()?->getName() ?? 'home';
$currentParameters = request()->route()?->parameters() ?? [];

$englishUrl = route(
$currentRoute,
array_merge($currentParameters, ['locale' => 'en'])
);

$spanishUrl = route(
$currentRoute,
array_merge($currentParameters, ['locale' => 'es'])
);
@endphp

<nav
    x-data="{ mobileOpen: false, languageOpen: false }"
    class="fixed inset-x-0 top-0 z-50 border-b border-border bg-background/95 shadow-sm backdrop-blur"
    aria-label="{{ __('site.accessibility.main_navigation') }}">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8">
        {{-- Logo --}}
        <a
            href="{{ route('home', ['locale' => $locale]) }}"
            class="flex shrink-0 items-center gap-3"
            title="AISC Madrid">
            <img
                src="{{ asset('images/logos/aisc-logo-color.svg') }}"
                alt="{{ __('site.nav.logo_alt') }}"
                class="h-15 w-auto">

            <span class="text-md font-bold text-foreground">
                AISC MADRID
            </span>
        </a>

        {{-- Desktop navigation --}}
        <div class="hidden items-center gap-7 lg:flex">
            <a
                href="{{ route('events.index', ['locale' => $locale]) }}"
                @class([ 'text-sm font-medium transition-colors' , 'text-primary'=> request()->routeIs('events.*'),
                'text-muted-foreground hover:text-foreground' => ! request()->routeIs('events.*'),
                ])
                >
                {{ __('site.nav.events') }}
            </a>

            <a
                href="{{ route('team.index', ['locale' => $locale]) }}"
                @class([ 'text-sm font-medium transition-colors' , 'text-primary'=> request()->routeIs('team.*'),
                'text-muted-foreground hover:text-foreground' => ! request()->routeIs('team.*'),
                ])
                >
                {{ __('site.nav.team') }}
            </a>

            <a
                href="{{ route('projects.index', ['locale' => $locale]) }}"
                @class([ 'text-sm font-medium transition-colors' , 'text-primary'=> request()->routeIs('projects.*'),
                'text-muted-foreground hover:text-foreground' => ! request()->routeIs('projects.*'),
                ])
                >
                {{ __('site.nav.projects') }}
            </a>

            <a
                href="{{ route('about', ['locale' => $locale]) }}"
                @class([ 'text-sm font-medium transition-colors' , 'text-primary'=> request()->routeIs('about'),
                'text-muted-foreground hover:text-foreground' => ! request()->routeIs('about'),
                ])
                >
                {{ __('site.nav.about') }}
            </a>

            {{-- Login / dashboard --}}
            @guest
            <a
                href="{{ route('login') }}"
                class="rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                {{ __('site.nav.login') }}
            </a>
            @else
            <a
                href="{{ route('dashboard') }}"
                class="rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                {{ __('site.nav.dashboard') }}
            </a>
            @endguest

            {{-- Theme toggle --}}
            <flux:button
                x-data
                x-on:click="$flux.dark = ! $flux.dark"
                variant="subtle"
                square
                aria-label="{{ __('site.theme.toggle') }}"
                title="{{ __('site.theme.toggle') }}">
                <flux:icon.sun class="size-5 dark:hidden" />
                <flux:icon.moon class="hidden size-5 dark:block" />
            </flux:button>

            {{-- Language selector --}}
            <div
                class="relative"
                @click.outside="languageOpen = false">
                <button
                    type="button"
                    @click="languageOpen = ! languageOpen"
                    :aria-expanded="languageOpen.toString()"
                    class="flex items-center gap-1 rounded-full border border-border px-3 py-2 text-sm font-semibold text-foreground transition hover:border-primary"
                    aria-label="{{ __('site.language.change') }}">
                    {{ strtoupper($locale) }}

                    <svg
                        class="h-4 w-4 transition"
                        :class="{ 'rotate-180': languageOpen }"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true">
                        <path
                            fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-cloak
                    x-show="languageOpen"
                    x-transition
                    class="absolute right-0 mt-2 w-36 overflow-hidden rounded-xl border border-border bg-surface p-1 shadow-xl">
                    <a
                        href="{{ $englishUrl }}"
                        @class([ 'block rounded-lg px-3 py-2 text-sm transition hover:bg-muted hover:text-foreground' , 'text-primary'=> $locale === 'en',
                        'text-muted-foreground' => $locale !== 'en',
                        ])
                        >
                        {{ __('site.language.english') }}
                    </a>

                    <a
                        href="{{ $spanishUrl }}"
                        @class([ 'block rounded-lg px-3 py-2 text-sm transition hover:bg-muted hover:text-foreground' , 'text-primary'=> $locale === 'es',
                        'text-muted-foreground' => $locale !== 'es',
                        ])
                        >
                        {{ __('site.language.spanish') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Mobile menu button --}}
        <button
            type="button"
            @click="mobileOpen = ! mobileOpen"
            :aria-expanded="mobileOpen.toString()"
            class="rounded-lg p-2 text-foreground transition hover:bg-muted lg:hidden"
            aria-controls="mobile-navigation"
            aria-label="{{ __('site.accessibility.open_navigation') }}">
            {{-- Menu icon --}}
            <svg
                x-show="! mobileOpen"
                class="h-7 w-7"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
            </svg>

            {{-- Close icon --}}
            <svg
                x-cloak
                x-show="mobileOpen"
                class="h-7 w-7"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Mobile navigation --}}
    <div
        id="mobile-navigation"
        x-cloak
        x-show="mobileOpen"
        x-transition
        class="border-t border-border bg-background px-5 py-5 lg:hidden">
        <div class="flex flex-col gap-4">
            <a
                href="{{ route('events.index', ['locale' => $locale]) }}"
                @class([ 'transition hover:text-primary' , 'text-primary'=> request()->routeIs('events.*'),
                'text-muted-foreground' => ! request()->routeIs('events.*'),
                ])
                >
                {{ __('site.nav.events') }}
            </a>

            <a
                href="{{ route('team.index', ['locale' => $locale]) }}"
                @class([ 'transition hover:text-primary' , 'text-primary'=> request()->routeIs('team.*'),
                'text-muted-foreground' => ! request()->routeIs('team.*'),
                ])
                >
                {{ __('site.nav.team') }}
            </a>

            <a
                href="{{ route('projects.index', ['locale' => $locale]) }}"
                @class([ 'transition hover:text-primary' , 'text-primary'=> request()->routeIs('projects.*'),
                'text-muted-foreground' => ! request()->routeIs('projects.*'),
                ])
                >
                {{ __('site.nav.projects') }}
            </a>

            <a
                href="{{ route('about', ['locale' => $locale]) }}"
                @class([ 'transition hover:text-primary' , 'text-primary'=> request()->routeIs('about'),
                'text-muted-foreground' => ! request()->routeIs('about'),
                ])
                >
                {{ __('site.nav.about') }}
            </a>

            {{-- Mobile login / dashboard --}}
            @guest
            <a
                href="{{ route('login') }}"
                class="mt-2 rounded-full bg-primary px-5 py-3 text-center font-semibold text-white transition hover:opacity-90">
                {{ __('site.nav.login') }}
            </a>
            @else
            <a
                href="{{ route('dashboard') }}"
                class="mt-2 rounded-full bg-primary px-5 py-3 text-center font-semibold text-white transition hover:opacity-90">
                {{ __('site.nav.dashboard') }}
            </a>
            @endguest

            {{-- Mobile theme toggle --}}
            <div class="flex items-center justify-between border-t border-border pt-4">
                <span class="text-sm text-muted-foreground">
                    {{ __('site.theme.appearance') }}
                </span>

                <flux:button
                    x-data
                    x-on:click="$flux.dark = ! $flux.dark"
                    variant="subtle"
                    square
                    aria-label="{{ __('site.theme.toggle') }}"
                    title="{{ __('site.theme.toggle') }}">
                    <flux:icon.sun class="size-5 dark:hidden" />
                    <flux:icon.moon class="hidden size-5 dark:block" />
                </flux:button>
            </div>

            {{-- Mobile language selector --}}
            <div class="flex gap-4 border-t border-border pt-4">
                <a
                    href="{{ $spanishUrl }}"
                    @class([ 'text-sm transition hover:text-primary' , 'text-primary'=> $locale === 'es',
                    'text-muted-foreground' => $locale !== 'es',
                    ])
                    >
                    {{ __('site.language.spanish') }}
                </a>

                <a
                    href="{{ $englishUrl }}"
                    @class([ 'text-sm transition hover:text-primary' , 'text-primary'=> $locale === 'en',
                    'text-muted-foreground' => $locale !== 'en',
                    ])
                    >
                    {{ __('site.language.english') }}
                </a>
            </div>
        </div>
    </div>
</nav>