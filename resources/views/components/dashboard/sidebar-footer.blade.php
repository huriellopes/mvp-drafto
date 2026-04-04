@php
    $user = auth()->user();
@endphp

@if ($user)
    <div class="space-y-3">
        <div
            x-show="!sidebarCollapsed"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            class="rounded-2xl bg-zinc-50 p-4 border border-zinc-100"
        >
            <p class="text-sm font-semibold text-zinc-900 truncate">
                {{ $user->name }}
            </p>
            <p class="mt-0.5 truncate text-xs text-zinc-500">
                {{ $user->email }}
            </p>
        </div>

        <div class="px-1">
            <livewire:auth.logout wire:key="logout-component-sidebar" />
        </div>
    </div>
@endif
