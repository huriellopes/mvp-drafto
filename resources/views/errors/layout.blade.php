<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Drafto</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; height: 100%; overflow: hidden; }
    </style>
</head>
<body class="antialiased bg-zinc-50 dark:bg-zinc-950 transition-colors duration-500">

{{-- Camada 0: O Código de Erro Gigante (Atrás de tudo) --}}
<div class="fixed inset-0 flex items-center justify-center pointer-events-none select-none z-0">
        <span class="text-[25rem] md:text-[40rem] font-black text-zinc-900/[0.03] dark:text-white/[0.03] tracking-tighter">
            @yield('code')
        </span>
</div>

{{-- Camada 1: Estrutura Principal --}}
<div class="relative z-10 flex flex-col h-full w-full px-6 py-12">

    {{-- Header: Logo --}}
    <header class="flex justify-center">
        <a href="/" class="text-2xl font-black tracking-tighter text-zinc-900 dark:text-white transition hover:opacity-70">
            Drafto<span class="text-profile-primary">.</span>
        </a>
    </header>

    {{-- Main: Conteúdo Centralizado --}}
    <main class="flex-1 flex flex-col items-center justify-center text-center">
        <div class="max-w-2xl space-y-8">
            {{-- Badge de Status --}}
            <div class="inline-flex items-center gap-2 rounded-full bg-zinc-900 dark:bg-white px-4 py-1.5 shadow-2xl">
                <span class="h-2 w-2 rounded-full bg-red-500 animate-ping"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white dark:text-zinc-900">
                        Status Error @yield('code')
                    </span>
            </div>

            <div class="space-y-4">
                <h2 class="text-5xl font-black text-zinc-900 dark:text-white md:text-8xl tracking-tighter italic leading-none">
                    @yield('message')
                </h2>
                <p class="mx-auto max-w-lg text-base md:text-lg font-medium text-zinc-500 dark:text-zinc-400 leading-relaxed">
                    @yield('description')
                </p>
            </div>

            {{-- Ações --}}
            <div class="flex flex-col items-center justify-center gap-4 sm:flex-row pt-6">
                <x-ui.button variant="primary" size="lg" onclick="window.location.href='/'" class="w-full sm:w-auto shadow-2xl shadow-zinc-900/20">
                    <x-lucide-house class="h-5 w-5" />
                    Voltar ao Início
                </x-ui.button>

                <x-ui.button variant="secondary" size="lg" onclick="window.history.back()" class="w-full sm:w-auto">
                    <x-lucide-undo-2 class="h-5 w-5" />
                    Tentar Novamente
                </x-ui.button>
            </div>
        </div>
    </main>

    {{-- Footer: Fixo na Base --}}
    <footer class="flex flex-col items-center gap-4">
        <div class="h-px w-12 bg-zinc-200 dark:bg-zinc-800"></div>
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-zinc-400 dark:text-zinc-600">
            Drafto Platform &copy; {{ date('Y') }}
        </p>
    </footer>
</div>

</body>
</html>
