<div>
    <div
        x-data="{
            tipShow: false,
            tipX: 0,
            tipY: 0,
            showTip(event) {
                const rect = event.currentTarget.getBoundingClientRect();
                this.tipX = rect.right + 12;
                this.tipY = rect.top + (rect.height / 2);
                this.tipShow = true;
            },
        }"
        @mouseenter="if ($data.sidebarCollapsed && ! $data.sidebarOpen) showTip($event)"
        @mouseleave="tipShow = false"
    >
        <button
            type="button"
            wire:click="logout"
            @click="localStorage.removeItem('sidebar-collapsed')"
            aria-label="{{ __('dashboard.auth.logout') }}"
            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
        >
            <x-lucide-log-out class="h-4 w-4" />

            <span x-show="!$data.sidebarCollapsed">
                {{ __('dashboard.auth.logout') }}
            </span>
        </button>

        {{-- Tooltip apenas com a sidebar recolhida (teleportado, fixed, z alto). --}}
        <template x-teleport="body">
            <span
                x-show="tipShow"
                x-cloak
                x-transition.opacity.duration.150ms
                :style="`left: ${tipX}px; top: ${tipY}px;`"
                class="pointer-events-none fixed z-[100] -translate-y-1/2 whitespace-nowrap rounded-lg bg-zinc-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-xl dark:bg-zinc-800"
            >
                {{ __('dashboard.auth.logout') }}
            </span>
        </template>
    </div>
</div>
