<div
    x-data="{ open: @entangle('show') }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100]"
>
    {{-- Overlay --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm"
        @click="open = false"
    ></div>

    {{-- Sliding Panel --}}
    <div class="fixed inset-y-0 right-0 flex max-w-full">
        <div
            x-show="open"
            x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-screen max-w-md bg-white dark:bg-zinc-950 shadow-2xl"
        >
            <div class="flex h-full flex-col">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 p-6">
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Notificações</h2>
                        <p class="text-xs text-zinc-500">Acompanhe suas interações</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button wire:click="markAllAsRead" class="text-xs font-bold text-profile-primary hover:underline transition">
                            Ler todas
                        </button>
                        <button @click="open = false" class="text-zinc-400 hover:text-zinc-600 transition">
                            <x-lucide-x class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                {{-- List --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    @forelse($notifications as $notification)
                        <div
                            wire:key="{{ $notification->id }}"
                            @class([
                                'group relative flex gap-4 p-4 rounded-2xl transition border overflow-hidden',
                                'bg-profile-primary/5 border-profile-primary/10' => !$notification->read_at,
                                'bg-white dark:bg-zinc-900 border-zinc-100 dark:border-zinc-800' => $notification->read_at,
                            ])
                        >
                            {{-- Clique na área principal redireciona --}}
                            <div
                                class="flex flex-1 gap-4 cursor-pointer"
                                wire:click="readAndRedirect('{{ $notification->id }}')"
                            >
                                <img src="{{ Storage::url($notification->data['causer_avatar']) }}" class="h-10 w-10 rounded-xl object-cover shrink-0 bg-zinc-100 border border-zinc-200/50">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-zinc-900 dark:text-white leading-snug">
                                        <span class="font-bold">{{ $notification->data['causer_name'] }}</span>
                                        {{ $notification->data['message'] }}
                                    </p>
                                    <p class="mt-1 text-[10px] text-zinc-400 uppercase tracking-widest font-medium">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            {{-- Botões de Ação Rápida (Aparecem no Hover) --}}
                            <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                @if(!$notification->read_at)
                                    <button
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        class="p-1.5 text-zinc-400 hover:text-profile-primary bg-zinc-50 dark:bg-zinc-800 rounded-lg shadow-sm"
                                        title="Marcar como lida"
                                    >
                                        <x-lucide-check class="h-3.5 w-3.5" />
                                    </button>
                                @endif
                                <button
                                    wire:click="delete('{{ $notification->id }}')"
                                    class="p-1.5 text-zinc-400 hover:text-red-500 bg-zinc-50 dark:bg-zinc-800 rounded-lg shadow-sm"
                                    title="Excluir notificação"
                                >
                                    <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-20 text-center">
                            <div class="h-20 w-20 bg-zinc-100 dark:bg-zinc-900 rounded-3xl flex items-center justify-center mb-4 border border-dashed border-zinc-300">
                                <x-lucide-bell-off class="h-10 w-10 text-zinc-300" />
                            </div>
                            <p class="text-zinc-500 font-medium italic">Silêncio total por aqui...</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
