<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased bg-zinc-50 text-zinc-900">
{{-- Navbar simples para visitantes --}}
<nav class="sticky top-0 z-40 w-full border-b border-zinc-200 bg-white/80 backdrop-blur-md">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <x-application-logo class="h-8 w-auto fill-current text-zinc-900" />
                <span class="text-xl font-bold tracking-tight text-zinc-900">Drafto</span>
            </a>

            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard.index') }}" class="text-sm font-semibold text-zinc-600 hover:text-zinc-900">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-zinc-600 hover:text-zinc-900">Entrar</a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800 transition">Começar a escrever</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main>
    {{ $slot }}
</main>

{{-- Footer simples --}}
<footer class="border-t border-zinc-200 bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 text-center text-sm text-zinc-500">
        <p>&copy; {{ date('Y') }} Drafto. Escreva com clareza. Publique com identidade.</p>
    </div>
</footer>
</body>
</html>
