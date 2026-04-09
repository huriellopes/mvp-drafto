<div>
    <button
        type="button"
        wire:click="logout"
        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
    >
        <x-lucide-log-out class="h-4 w-4" />

        <span x-show="!$data.sidebarCollapsed">
            {{ __('dashboard.auth.logout') }}
        </span>
    </button>
</div>
