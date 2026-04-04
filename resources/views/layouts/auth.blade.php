<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-zinc-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full bg-zinc-50 text-zinc-900 antialiased">
    <main class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(24,24,27,0.06),_transparent_40%)]"></div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-7xl items-center justify-center px-6 py-10 sm:px-8">
            {{ $slot }}
        </div>
    </main>

    @livewireScripts
</body>
</html>
