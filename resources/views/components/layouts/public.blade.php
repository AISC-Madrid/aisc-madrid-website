<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'AISC Madrid' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body>
    <nav class="flex items-center justify-between px-8 py-5">
        <a href="{{ route('home') }}" class="font-bold">
            AISC Madrid
        </a>

        <div class="flex gap-5">
            <a href="#events">Events</a>

            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Log in</a>
            @endauth
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>

    @fluxScripts
</body>
</html>