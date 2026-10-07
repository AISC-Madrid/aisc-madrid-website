@php
    $locale = app()->getLocale();
@endphp

<footer class="bg-linear-to-r from-background via-primary/5 via-10% to-primary">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-5 lg:px-8">
        {{-- Logo --}}
        <a
            href="{{ route('home', ['locale' => $locale]) }}"
            title="AISC Madrid"
        >
            <img
                src="{{ asset('images/logos/aisc-logo-color.svg') }}"
                alt="{{ __('site.nav.logo_alt') }}"
                class="h-12 w-auto"
            >
        </a>

        <div class="flex flex-col items-end gap-2">
            {{-- Social --}}
            <div class="flex items-center gap-4">
                <a
                    href="https://www.instagram.com/aisc_madrid/"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="{{ __('site.footer.instagram') }}"
                    class="text-white transition hover:text-aisc-blue"
                >
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
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
                    class="text-white transition hover:text-aisc-blue"
                >
                    <svg class="size-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.22 8.24H4.8V23H.22V8.24zM8.4 8.24h4.38v2.02h.06c.61-1.15 2.1-2.36 4.32-2.36 4.62 0 5.47 3.04 5.47 6.99V23h-4.57v-6.94c0-1.66-.03-3.79-2.31-3.79-2.32 0-2.67 1.81-2.67 3.67V23H8.4V8.24z" />
                    </svg>
                </a>

                <a
                    href="https://github.com/AISC-Madrid"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="{{ __('site.footer.github') }}"
                    class="text-white transition hover:text-aisc-blue"
                >
                    <svg class="size-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 .5C5.73.5.5 5.73.5 12c0 5.09 3.29 9.4 7.86 10.93.57.1.79-.25.79-.55v-2.1c-3.2.7-3.88-1.4-3.88-1.4-.52-1.33-1.28-1.68-1.28-1.68-1.05-.72.08-.71.08-.71 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.23-1.28-5.23-5.68 0-1.26.45-2.28 1.19-3.09-.12-.29-.52-1.46.11-3.04 0 0 .97-.31 3.18 1.18a11.1 11.1 0 012.9-.39c.98 0 1.97.13 2.9.39 2.2-1.49 3.17-1.18 3.17-1.18.63 1.58.23 2.75.11 3.04.74.81 1.19 1.83 1.19 3.09 0 4.41-2.69 5.38-5.25 5.67.41.36.78 1.08.78 2.17v3.22c0 .3.21.66.8.55A10.53 10.53 0 0023.5 12C23.5 5.73 18.27.5 12 .5z" />
                    </svg>
                </a>

                <a
                    href="mailto:info@aiscmadrid.com"
                    aria-label="{{ __('site.footer.email') }}"
                    class="text-white transition hover:text-aisc-blue"
                >
                    <flux:icon.envelope class="size-6" />
                </a>
            </div>

            {{-- Links --}}
            <div class="flex flex-wrap items-center justify-end gap-x-2 text-xs text-white">
                <a
                    href="{{ route('home', ['locale' => $locale]) }}#newsletter"
                    class="transition hover:text-aisc-blue"
                >
                    {{ __('site.footer.newsletter') }}
                </a>

                <span aria-hidden="true">|</span>

                <a
                    href="{{ route('terms', ['locale' => $locale]) }}"
                    class="transition hover:text-aisc-blue"
                >
                    {{ __('site.footer.terms') }}
                </a>

                <span aria-hidden="true">|</span>

                <a
                    href="{{ route('estatutos', ['locale' => $locale]) }}"
                    class="transition hover:text-aisc-blue"
                >
                    {{ __('site.footer.bylaws') }}
                </a>
            </div>
        </div>
    </div>
</footer>
