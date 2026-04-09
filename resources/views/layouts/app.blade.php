<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-zinc-50">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

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
</head>
<body
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: false,
    }"
    class="min-h-full bg-zinc-50 text-zinc-900 antialiased"
>
<div class="min-h-screen">
    <div class="flex min-h-screen">
        <div
            x-cloak
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-zinc-900/40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-zinc-200 bg-white transition-all duration-300 lg:translate-x-0"
            :class="{
                'translate-x-0': sidebarOpen,
                'lg:w-24': sidebarCollapsed,
                'lg:w-72': !sidebarCollapsed
            }"
        >
            <div class="flex h-20 items-center justify-between border-b border-zinc-200 px-4">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-50 text-sm font-semibold text-zinc-900">
                        W
                    </div>

                    <div x-show="!sidebarCollapsed" x-transition class="min-w-0">
                        <p class="truncate text-sm font-semibold text-zinc-900">
                            {{ config('app.name') }}
                        </p>
                        <p class="truncate text-xs text-zinc-500">
                            Plataforma para escritores
                        </p>
                    </div>
                </div>

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
            class="flex min-w-0 flex-1 flex-col transition-all duration-300"
            :class="{
                'lg:pl-24': sidebarCollapsed,
                'lg:pl-72': !sidebarCollapsed
            }"
        >
            <header class="sticky top-0 z-30 border-b border-zinc-200/80 bg-white/90 backdrop-blur">
                <div class="flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            <x-lucide-menu class="h-5 w-5" />
                        </button>

                        <button
                            type="button"
                            class="hidden h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 lg:inline-flex"
                            @click="sidebarCollapsed = !sidebarCollapsed"
                        >
                            <x-lucide-panel-left-close
                                x-show="!sidebarCollapsed"
                                class="h-5 w-5"
                            />

                            <x-lucide-panel-left-open
                                x-show="sidebarCollapsed"
                                class="h-5 w-5"
                            />
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
                        <button
                            @click="$dispatch('toggleNotifications')"
                            class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50"
                        >
                            <x-lucide-bell class="h-5 w-5" />
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>
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
</body>
</html>
