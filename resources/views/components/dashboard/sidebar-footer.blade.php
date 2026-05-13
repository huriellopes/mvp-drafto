@php
    $user = auth()->user();
@endphp

@if ($user)
    <div class="space-y-3">
        <div
            x-show="!sidebarCollapsed"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            class="rounded-2xl bg-zinc-50 p-4 border border-zinc-100 mb-3"
        >
            <p class="text-sm font-semibold text-zinc-900 truncate">
                {{ format_display_name($user->name) }}
            </p>
            <p class="mt-0.5 truncate text-xs text-zinc-500">
                {{ $user->email }}
            </p>
            <button
                @click="$dispatch('openReportModal', { type: 'user', id: {{ $user->id }} })"
                class="mt-3 flex w-full items-center gap-2 rounded-lg bg-white px-3 py-1.5 text-[10px] font-bold text-zinc-600 border border-zinc-200 hover:text-profile-primary hover:border-profile-primary transition shadow-sm"
            >
                <x-lucide-message-square-heart class="h-3 w-3" />
                <span>Elogios e Feedbacks</span>
            </button>
        </div>

        <div class="px-1 flex flex-col gap-2">
            {{-- Theme Toggle Dashboard --}}
            <button @click="$store.theme.toggle()"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 border border-transparent hover:border-zinc-200">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-50 border border-zinc-100 group-hover:bg-white">
                    <x-lucide-sun x-show="!$store.theme.darkMode" class="h-4 w-4" />
                    <x-lucide-moon x-show="$store.theme.darkMode" class="h-4 w-4" x-cloak />
                </div>
                <span x-show="!sidebarCollapsed">Alternar Tema</span>
            </button>

            <livewire:auth.logout wire:key="logout-component-sidebar" />
        </div>
    </div>
@endif
