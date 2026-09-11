<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'AISC Madrid' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body>
    <x-public.navbar />

    <main class="pt-20">
        {{ $slot }}
    </main>

    <x-public.footer />

    @fluxScripts
</body>

</html>
