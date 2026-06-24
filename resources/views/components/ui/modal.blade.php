@props(['name', 'title' => null])

<div
    x-data="{ show: false }"
    x-on:open-modal.window="if ($event.detail.name === '{{ $name }}') show = true"
    x-on:close-modal.window="if ($event.detail.name === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
>
    <template x-teleport="body">
        <div
            x-show="show"
            class="fixed inset-0 overflow-y-auto"
            style="display: none; z-index: 9999;"
        >
            {{-- Overlay --}}
            <div 
                x-show="show" 
                x-transition.opacity 
                class="fixed inset-0 bg-zinc-950/60 backdrop-blur-md"
                x-on:click="show = false"
            ></div>

            {{-- Modal Content --}}
            <div
                class="flex min-h-screen items-center justify-center p-4 relative"
                style="z-index: 10000;"
                x-on:click.self="show = false"
            >
                <div
                    x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-lg rounded-[2.5rem] bg-white p-6 shadow-2xl sm:p-10 border border-zinc-100"
                >
                    {{-- Botão de fechar --}}
                    <button
                        type="button"
                        x-on:click="show = false"
                        aria-label="Fechar"
                        class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-full text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-900"
                    >
                        <x-lucide-x class="h-5 w-5" />
                    </button>

                    @if($title)
                        <div class="mb-6 pr-10">
                            <h3 class="text-2xl font-black text-zinc-900 tracking-tight italic">{{ $title }}</h3>
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </div>
        </div>
    </template>
</div>
