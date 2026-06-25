@props([
    'themeMode' => 'system',
    'primaryColor' => '#18181b',
    'accentColor' => '#3f3f46',
    'secondaryColor' => null,
    'textColor' => null,
    'backgroundColor' => null,
    'buttonStyle' => 'rounded-md',
    'fontFamily' => 'sans',
    'title' => null,
    'seo' => null
])
@php
    // O Modo de Cor (Tema) tem prioridade sobre a Cor de Fundo da página:
    // a cor de fundo só é aplicada quando o tema está em "system".
    $effectiveBackground = $themeMode === 'system' ? $backgroundColor : null;

    // Deriva claro/escuro da luminância da cor de fundo, para que o texto
    // se adapte automaticamente (mesmo comportamento do dark mode).
    $backgroundIsDark = false;
    if (filled($effectiveBackground) && $effectiveBackground !== 'inherit') {
        $bgHex = ltrim($effectiveBackground, '#');
        if (strlen($bgHex) === 3) {
            $bgHex = $bgHex[0] . $bgHex[0] . $bgHex[1] . $bgHex[1] . $bgHex[2] . $bgHex[2];
        }
        if (strlen($bgHex) === 6 && ctype_xdigit($bgHex)) {
            $yiq = (hexdec(substr($bgHex, 0, 2)) * 299 + hexdec(substr($bgHex, 2, 2)) * 587 + hexdec(substr($bgHex, 4, 2)) * 114) / 1000;
            $backgroundIsDark = $yiq < 128;
        }
    }

    $forceDark = $themeMode === 'dark' || ($effectiveBackground && $backgroundIsDark);
    $forceLight = $themeMode === 'light' || ($effectiveBackground && ! $backgroundIsDark);
    $useDeviceTheme = $themeMode === 'system' && blank($effectiveBackground);
@endphp
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-profile-theme="{{ $themeMode }}"
      @class([
        'dark' => $forceDark,
        'light' => $forceLight,
      ])
      @if($useDeviceTheme)
          x-data="{ deviceTheme: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light' }"
      :class="deviceTheme"
    @endif
>
@if($useDeviceTheme)
    {{-- Pre-paint: aplica o tema do dispositivo antes da pintura para evitar flash (FOUC). --}}
    <script>
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
    </script>
@endif
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-layouts.tracking />

    <x-layouts.favicons />

    {{-- Lógica de SEO Sênior --}}
    @if($seo)
        {!! seo($seo) !!}
    @else
        <title>{{ $title ?? config('app.name') }}</title>
        {!! seo() !!}
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --profile-primary: {{ $primaryColor }};
            --profile-accent: {{ $accentColor }};
            --profile-secondary: {{ $secondaryColor ?? $accentColor }};
            --profile-text: {{ $textColor ?? 'inherit' }};
            --profile-bg: {{ $effectiveBackground ?? 'inherit' }};
            --profile-button-radius: {{ match($buttonStyle) {
                'rounded-md' => '0.375rem',
                'rounded-xl' => '0.75rem',
                'rounded-full' => '9999px',
                'square' => '0',
                default => '0.375rem'
            } }};
            --profile-font: {{ match($fontFamily) {
                'serif' => 'ui-serif, Georgia, Cambria, "Times New Roman", Times, serif',
                'mono' => 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
                default => 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif'
            } }};

            @php
                $hex = str_replace('#', '', $primaryColor);
                if(strlen($hex) == 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            @endphp
            --profile-primary-rgb: {{ $r }}, {{ $g }}, {{ $b }};
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        function getContrastYIQ(hexcolor){
            if (!hexcolor || hexcolor === 'inherit') return 'black';
            hexcolor = hexcolor.replace('#', '');
            if (hexcolor.length === 3) hexcolor = hexcolor[0]+hexcolor[0]+hexcolor[1]+hexcolor[1]+hexcolor[2]+hexcolor[2];
            const r = parseInt(hexcolor.substr(0,2),16);
            const g = parseInt(hexcolor.substr(2,2),16);
            const b = parseInt(hexcolor.substr(4,2),16);
            const yiq = ((r*299)+(g*587)+(b*114))/1000;
            return (yiq >= 128) ? 'black' : 'white';
        }
        document.documentElement.style.setProperty('--profile-primary-text', getContrastYIQ('{{ $primaryColor }}'));
        document.documentElement.style.setProperty('--profile-secondary-text', getContrastYIQ('{{ $secondaryColor ?? $accentColor }}'));
    </script>
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 antialiased transition-colors duration-300" style="font-family: var(--profile-font)">

<nav class="sticky top-0 z-50 w-full border-b border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <a href="/" class="flex items-center gap-2" wire:navigate>
                <x-application-logo class="h-8 w-auto fill-current text-zinc-900 dark:text-white" />
                <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white leading-none">Drafto</span>
            </a>

            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard.index') }}" class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" wire:navigate data-tracking="dfto_click_dashboard">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" wire:navigate data-tracking="dfto_click_login">Entrar</a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-zinc-900 dark:bg-white px-4 py-2 text-sm font-semibold text-white dark:text-zinc-900 hover:opacity-90 transition shadow-sm" wire:navigate data-tracking="dfto_click_register">Começar a escrever</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main class="min-h-screen">
    {{ $slot }}
</main>

<footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 py-12">
    <div class="mx-auto max-w-7xl px-4 flex flex-col items-center gap-6">
        <x-ui.social-links context="guest_footer" />
        <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-zinc-400 dark:text-zinc-500">
            <a href="{{ route('pages.privacy') }}" class="transition hover:text-zinc-700 dark:hover:text-zinc-300">Política de Privacidade</a>
            <a href="{{ route('pages.terms') }}" class="transition hover:text-zinc-700 dark:hover:text-zinc-300">Termos de Uso</a>
            <a href="{{ route('pages.guidelines') }}" class="transition hover:text-zinc-700 dark:hover:text-zinc-300">Diretrizes</a>
            <button type="button" onclick="window.draftoOpenConsent && window.draftoOpenConsent()" class="transition hover:text-zinc-700 dark:hover:text-zinc-300">Preferências de cookies</button>
        </nav>
        <div class="text-center text-sm text-zinc-500 dark:text-zinc-400">
            <p>&copy; {{ date('Y') }} Drafto. Escreva com clareza. Publique com identidade.</p>
        </div>
    </div>
</footer>

<x-cookie-consent />
</body>
</html>
