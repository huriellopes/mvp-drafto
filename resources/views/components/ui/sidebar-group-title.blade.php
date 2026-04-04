<div
    x-show="!sidebarCollapsed"
    x-transition
    class="mb-3 px-2"
>
    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">
        {{ $slot }}
    </p>
</div>
