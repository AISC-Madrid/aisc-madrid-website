@php
    $locale = app()->getLocale();
@endphp

<footer class="border-t border-border bg-surface">
    <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-3">
            {{-- Logo & tagline --}}
            <div>
                <a
                    href="{{ route('home', ['locale' => $locale]) }}"
                    class="flex items-center gap-3"
                    title="AISC Madrid"
                >
                    <img
                        src="{{ asset('images/logos/aisc-logo-color.svg') }}"
                        alt="{{ __('site.nav.logo_alt') }}"
                        class="h-10 w-auto"
                    >

                    <span class="text-md font-bold text-foreground">
                        AISC MADRID
                    </span>
                </a>

                <flux:text class="mt-4 max-w-xs">
                    {{ __('site.footer.tagline') }}
                </flux:text>
            </div>

            {{-- Links --}}
            <div>
                <flux:heading size="sm" class="uppercase tracking-wide text-muted-foreground">
                    {{ __('site.footer.links_heading') }}
                </flux:heading>

                <ul class="mt-4 space-y-2">
                    <li>
                        <a
                            href="{{ route('home', ['locale' => $locale]) }}#newsletter"
                            class="text-sm text-muted-foreground transition hover:text-foreground"
                        >
                            {{ __('site.footer.newsletter') }}
                        </a>
                    </li>

                    <li>
                        <a
                            href="#"
                            class="text-sm text-muted-foreground transition hover:text-foreground"
                        >
                            {{ __('site.footer.terms') }}
                        </a>
                    </li>

                    <li>
                        <a
                            href="#"
                            class="text-sm text-muted-foreground transition hover:text-foreground"
                        >
                            {{ __('site.footer.bylaws') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Social --}}
            <div>
                <flux:heading size="sm" class="uppercase tracking-wide text-muted-foreground">
                    {{ __('site.footer.social_heading') }}
                </flux:heading>

                <div class="mt-4 flex items-center gap-4">
                    <a
                        href="https://www.instagram.com/aisc_madrid/"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="{{ __('site.footer.instagram') }}"
                        class="text-muted-foreground transition hover:text-primary"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="3" width="18" height="18" rx="5" />
                            <circle cx="12" cy="12" r="4" />
                            <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
                        </svg>
                    </a>

                    <a
                        href="https://www.linkedin.com/company/ai-student-collective-madrid"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="{{ __('site.footer.linkedin') }}"
                        class="text-muted-foreground transition hover:text-primary"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.22 8.24H4.8V23H.22V8.24zM8.4 8.24h4.38v2.02h.06c.61-1.15 2.1-2.36 4.32-2.36 4.62 0 5.47 3.04 5.47 6.99V23h-4.57v-6.94c0-1.66-.03-3.79-2.31-3.79-2.32 0-2.67 1.81-2.67 3.67V23H8.4V8.24z" />
                        </svg>
                    </a>

                    <a
                        href="https://github.com/AISC-Madrid"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="{{ __('site.footer.github') }}"
                        class="text-muted-foreground transition hover:text-primary"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 .5C5.73.5.5 5.73.5 12c0 5.09 3.29 9.4 7.86 10.93.57.1.79-.25.79-.55v-2.1c-3.2.7-3.88-1.4-3.88-1.4-.52-1.33-1.28-1.68-1.28-1.68-1.05-.72.08-.71.08-.71 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.23-1.28-5.23-5.68 0-1.26.45-2.28 1.19-3.09-.12-.29-.52-1.46.11-3.04 0 0 .97-.31 3.18 1.18a11.1 11.1 0 012.9-.39c.98 0 1.97.13 2.9.39 2.2-1.49 3.17-1.18 3.17-1.18.63 1.58.23 2.75.11 3.04.74.81 1.19 1.83 1.19 3.09 0 4.41-2.69 5.38-5.25 5.67.41.36.78 1.08.78 2.17v3.22c0 .3.21.66.8.55A10.53 10.53 0 0023.5 12C23.5 5.73 18.27.5 12 .5z" />
                        </svg>
                    </a>

                    <a
                        href="mailto:info@aiscmadrid.com"
                        aria-label="{{ __('site.footer.email') }}"
                        class="text-muted-foreground transition hover:text-primary"
                    >
                        <flux:icon.envelope class="size-5" />
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-10 border-t border-border pt-6 text-center">
            <flux:text size="sm">
                &copy; {{ now()->year }} AISC Madrid. {{ __('site.footer.copyright') }}
            </flux:text>
        </div>
    </div>
</footer>
