<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="scroll-smooth"
      x-data="{
        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
      }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(isset($seo))
        {!! seo($seo) !!}
    @else
        {!! seo() !!}
    @endif

    @if(!isset($seo))
        <title>{{ $title ?? config('app.name') }}</title>
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @php
        // Solução Sênior: Função anônima para converter Hex para RGB e evitar o erro "undefined function"
        $hex = $primaryColor ?? '#18181b';
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $rgb = "{$r}, {$g}, {$b}";
    @endphp

    <style>
        :root {
            --profile-primary: {{ $primaryColor ?? '#18181b' }};
            --profile-primary-rgb: {{ $rgb }};
        }
    </style>
</head>
<body class="font-sans antialiased bg-white text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 selection:bg-profile-primary/30 transition-colors duration-300">

<x-toaster-hub />
<livewire:public.report-modal />

{{-- Navbar --}}
<x-site.navbar />

<main class="pt-20">
    {{ $slot }}
</main>

{{-- Footer --}}
<x-site.footer />

@livewireScripts

<x-toaster-hub />
</body>
</html>
