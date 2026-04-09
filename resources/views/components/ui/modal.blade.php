@props(['name', 'title' => null])

<div
    x-data="{ show: false }"
    x-show="show"
    x-on:open-modal.window="if ($event.detail.name === '{{ $name }}') show = true"
    x-on:close-modal.window="if ($event.detail.name === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    {{-- Overlay --}}
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-zinc-900/40 backdrop-blur-sm"></div>

    {{-- Modal Content --}}
    <div class="flex min-h-screen items-center justify-center p-4">
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.away="show = false"
            class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl sm:p-8"
        >
            @if($title)
                <div class="mb-4">
                    <h3 class="text-xl font-semibold text-zinc-900">{{ $title }}</h3>
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
