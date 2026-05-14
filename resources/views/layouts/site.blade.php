<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="scroll-smooth"
      data-site-theme
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-layouts.favicons />

    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

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
<body 
    x-data 
    x-init="
        if (!Alpine.store('theme')) {
            Alpine.store('theme', {
                darkMode: document.documentElement.classList.contains('dark'),
                toggle() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                    this.update();
                },
                update() {
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });
        }
        setTimeout(() => $el.classList.remove('no-transitions'), 100);
    "
    class="font-sans antialiased bg-white text-zinc-900 selection:bg-profile-primary/30 dark:bg-zinc-950 dark:text-white transition-colors duration-300 no-transitions"
>

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
