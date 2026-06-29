<div wire:poll.60s>
    <x-ui.tooltip text="Notificações" position="bottom">
        <button
            @click="$dispatch('toggleNotifications')"
            type="button"
            aria-label="Notificações"
            class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50"
        >
            <x-lucide-bell class="h-5 w-5" aria-hidden="true" />

            @if($this->unreadCount > 0)
                <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white animate-pulse">
                    {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
                </span>
            @endif
        </button>
    </x-ui.tooltip>
</div>
