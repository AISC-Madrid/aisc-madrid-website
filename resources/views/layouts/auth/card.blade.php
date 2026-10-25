<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-linear-to-r from-background via-primary/5 via-10% to-primary antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6">
                <div class="rounded-xl border border-border bg-surface text-foreground shadow-xs dark:bg-stone-950 dark:border-stone-800">
                    <div class="px-10 py-8">
                        <!-- Logo oficial de AISC centrado -->
                        <div class="mb-6 flex justify-center">
                            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" wire:navigate>
                                <img src="{{ asset('images/logos/aisc-logo-color.svg') }}" alt="AISC Madrid" class="h-16 w-auto" />
                            </a>
                        </div>

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
