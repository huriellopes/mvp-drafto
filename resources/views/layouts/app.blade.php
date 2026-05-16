<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-zinc-50">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    <x-layouts.favicons />

    <script>
        (function() {
            document.documentElement.classList.remove('dark');
            if (localStorage.getItem('sidebar-collapsed') === 'false') {
                document.documentElement.classList.add('is-sidebar-expanded');
            }
        })();
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">

    <script>
        document.addEventListener('trix-before-initialize', () => {
            Trix.config.blockAttributes.heading2 = {
                tagName: 'h2',
                terminal: true,
                breakOnReturn: true,
                group: false,
            };

            Trix.config.textAttributes.code = {
                tagName: 'code',
                inheritable: true,
                parser(element) {
                    return element.tagName === 'CODE' && element.parentElement?.tagName !== 'PRE';
                },
            };

            Trix.config.blockAttributes.codeBlock = {
                tagName: 'pre',
                terminal: true,
                breakOnReturn: false,
                group: false,
                parser(element) {
                    return element.tagName === 'PRE';
                },
            };
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .livewire-progressive-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            width: 0;
            background: #4f46e5;
            z-index: 9999;
            transition: width 300ms ease-out, opacity 150ms;
            opacity: 0;
        }
    </style>
</head>
<body
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('sidebar-collapsed') === 'false' ? false : true,
        loading: false
    }"
    x-on:livewire:navigating.window="loading = true"
    x-on:livewire:navigated.window="loading = false"
    x-init="
        setTimeout(() => $el.classList.remove('no-transitions'), 100);
        document.documentElement.classList.remove('is-sidebar-expanded');
        $watch('sidebarCollapsed', value => localStorage.setItem('sidebar-collapsed', value));
    "
    class="min-h-full bg-zinc-50 text-zinc-900 antialiased no-transitions"
>
<livewire:dashboard.impersonation-banner />
<div class="livewire-progressive-bar" :style="loading ? 'width: 100%; opacity: 1;' : 'width: 0%; opacity: 0; transition: none;'"></div>
<div class="min-h-screen">
    <div class="flex min-h-screen">
        <div
            x-cloak
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 bg-zinc-900/40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-[60] flex w-72 lg:w-24 -translate-x-full flex-col border-r border-zinc-200 bg-white transition-[width,transform] duration-300 lg:translate-x-0 lg:z-40"
            :class="{
                'translate-x-0': sidebarOpen,
                'lg:w-24': sidebarCollapsed,
                'lg:w-72': !sidebarCollapsed
            }"
        >
            <div class="flex h-20 items-center justify-between border-b border-zinc-200 px-4">
                <div 
                    class="flex min-w-0 items-center gap-3 cursor-pointer group"
                    @click="sidebarCollapsed = false"
                >
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-50 overflow-hidden transition-transform group-hover:scale-105 active:scale-95">
                        <img src="{{ asset('images/favicon/android-chrome-192x192.png') }}" alt="Drafto Logo" class="w-8 h-auto" />
                    </div>

                    <template x-if="sidebarCollapsed">
                        <x-lucide-chevron-right 
                            class="hidden lg:block h-4 w-4 text-zinc-400 group-hover:text-indigo-600 transition-colors"
                        />
                    </template>

                    <div x-show="!sidebarCollapsed || sidebarOpen" x-transition class="min-w-0">
                        <p class="truncate text-sm font-semibold text-zinc-900">
                            {{ config('app.name') }}
                        </p>
                        <p class="truncate text-xs text-zinc-500">
                            Plataforma para escritores
                        </p>
                    </div>
                </div>

                {{-- Desktop Toggle --}}
                <button
                    x-show="!sidebarCollapsed"
                    x-transition
                    type="button"
                    class="hidden h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 transition-all hover:bg-zinc-50 hover:text-zinc-900 lg:flex active:scale-90"
                    @click.stop="sidebarCollapsed = !sidebarCollapsed"
                >
                    <x-lucide-panel-left-close class="h-4 w-4" />
                </button>

                {{-- Mobile Close --}}
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 lg:hidden"
                    @click="sidebarOpen = false"
                >
                    <x-lucide-x class="h-5 w-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-5">
                <x-dashboard.sidebar />
            </div>

            <div class="border-t border-zinc-200 px-3 py-4">
                <x-dashboard.sidebar-footer />
            </div>
        </aside>

        <div
            class="main-content-wrapper flex min-w-0 flex-1 flex-col lg:pl-24 transition-[padding] duration-300 ease-in-out will-change-[padding]"
            :class="{
                'lg:pl-24': sidebarCollapsed,
                'lg:pl-72': !sidebarCollapsed
            }"
        >
            <header class="sticky top-0 z-40 border-b border-zinc-200/80 bg-white/90 backdrop-blur lg:z-50">
                <div class="flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            <x-lucide-menu class="h-5 w-5" />
                        </button>

                        <div class="min-w-0">
                            <h1 class="truncate text-sm font-semibold text-zinc-900 sm:text-base">
                                {{ $heading ?? config('app.name') }}
                            </h1>

                            @isset($subheading)
                                <p class="truncate text-xs text-zinc-500 sm:text-sm">
                                    {{ $subheading }}
                                </p>
                            @endisset
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        {{ $headerActions ?? '' }}

                        <a 
                            href="{{ route('home') }}" 
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex h-10 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 text-[10px] font-bold uppercase tracking-widest text-zinc-600 transition hover:bg-zinc-50 shadow-sm active:scale-95"
                            title="Ver Site"
                        >
                            <x-lucide-external-link class="h-4 w-4 text-zinc-400" />
                            <span class="hidden sm:inline">Ir para o site</span>
                        </a>

                        <livewire:dashboard.notification-bell />
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <div class="mx-auto w-full max-w-none">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</div>

<x-toaster-hub />

<livewire:dashboard.notifications-sidebar />

@livewireScripts

<livewire:public.report-modal />
</body>
</html>
