@use(App\Enums\RoleEnum)
@use(App\Enums\ModuleEnum)

@php
    $user = auth()->user();
    $isAdmin = $user->hasRole(RoleEnum::SUPER_ADMIN);
@endphp

<div class="space-y-8">
    {{-- Grupo Global --}}
    <div>
        <x-ui.sidebar-group-title>Navegação</x-ui.sidebar-group-title>
        <nav class="space-y-2">
            <x-ui.sidebar-link href="{{ route('dashboard.index') }}" :active="request()->routeIs('dashboard.index')" wire:navigate.hover>
                <x-slot:icon><x-lucide-house class="h-5 w-5" /></x-slot:icon>
                Dashboard
            </x-ui.sidebar-link>

            @module(ModuleEnum::PROFILE)
            <x-ui.sidebar-link href="{{ route('dashboard.profile') }}" :active="request()->routeIs('dashboard.profile')" wire:navigate.hover>
                <x-slot:icon><x-lucide-user-circle class="h-5 w-5" /></x-slot:icon>
                Meu Perfil Público
            </x-ui.sidebar-link>
            @endmodule

            @module(ModuleEnum::ACCOUNT)
            <x-ui.sidebar-link href="{{ route('dashboard.account') }}" :active="request()->routeIs('dashboard.account')" wire:navigate.hover>
                <x-slot:icon><x-lucide-settings class="h-5 w-5" /></x-slot:icon>
                Configurações
            </x-ui.sidebar-link>
            @endmodule
        </nav>
    </div>

    {{-- Visão do Escritor (Writer / Admin Bypass) --}}
    @if($user->hasRole(RoleEnum::WRITER) || $isAdmin)
        <div>
            <x-ui.sidebar-group-title>Escritório de Criação</x-ui.sidebar-group-title>
            <nav class="space-y-1">
                @module(ModuleEnum::MY_POSTS)
                <x-ui.sidebar-link href="{{ route('dashboard.posts.index') }}" :active="request()->routeIs('dashboard.posts.index')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-library class="h-5 w-5" /></x-slot:icon>
                    Minhas Obras
                </x-ui.sidebar-link>
                @endmodule

                @module(ModuleEnum::DRAFT)
                <x-ui.sidebar-link href="{{ route('dashboard.posts.draft') }}" :active="request()->routeIs('dashboard.posts.draft')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-file-text class="h-5 w-5" /></x-slot:icon>
                    Rascunhos
                </x-ui.sidebar-link>
                @endmodule

                @module(ModuleEnum::FOLLOWS)
                <x-ui.sidebar-link href="{{ route('dashboard.follows') }}" :active="request()->routeIs('dashboard.follows')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-users-round class="h-5 w-5" /></x-slot:icon>
                    Seguidores
                </x-ui.sidebar-link>
                @endmodule
            </nav>
        </div>
    @endif

    {{-- Visão da Comunidade (Reader / Admin Bypass) --}}
    <div>
        <x-ui.sidebar-group-title>Comunidade</x-ui.sidebar-group-title>
        <nav class="space-y-2">
            @module(ModuleEnum::SAVED_POST)
            <x-ui.sidebar-link href="{{ route('dashboard.posts.saved') }}" :active="request()->routeIs('dashboard.posts.saved')" wire:navigate.hover>
                <x-slot:icon><x-lucide-bookmark class="h-5 w-5" /></x-slot:icon>
                Salvos
            </x-ui.sidebar-link>
            @endmodule

            @module(ModuleEnum::COMMENTS)
            <x-ui.sidebar-link href="{{ route('dashboard.comments') }}" :active="request()->routeIs('dashboard.comments')" wire:navigate.hover>
                <x-slot:icon><x-lucide-message-square class="h-5 w-5" /></x-slot:icon>
                Meus Comentários
            </x-ui.sidebar-link>
            @endmodule
        </nav>
    </div>

    {{-- Gestão Master (Admin Only) --}}
    @can('admin')
        <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800">
            <x-ui.sidebar-group-title>Gestão Master</x-ui.sidebar-group-title>
            <nav class="space-y-1">
                <x-ui.sidebar-link href="{{ route('dashboard.admin.users.index') }}" :active="request()->routeIs('dashboard.admin.users.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-users class="h-5 w-5" /></x-slot:icon>
                    Usuários
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.admin.newsletter.index') }}" :active="request()->routeIs('dashboard.admin.newsletter.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-mail-plus class="h-5 w-5" /></x-slot:icon>
                    Newsletter
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.admin.reports.index') }}" :active="request()->routeIs('dashboard.admin.reports.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-shield-alert class="h-5 w-5" /></x-slot:icon>
                    Moderação
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.admin.posts.views') }}" :active="request()->routeIs('dashboard.admin.posts.views')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-bar-chart-3 class="h-5 w-5" /></x-slot:icon>
                    Estatísticas
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.admin.modules.index') }}" :active="request()->routeIs('dashboard.admin.modules.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-component class="h-5 w-5" /></x-slot:icon>
                    Módulos
                </x-ui.sidebar-link>
            </nav>
        </div>
    @endcan

    {{-- Footer --}}
    <div x-show="!sidebarCollapsed" x-transition class="pt-6">
        <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900 p-4 border border-zinc-100 dark:border-zinc-800 transition-colors">
            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2">Sessão Atual</p>
            <div class="flex items-center gap-3">
                <div @class([
                    'h-2 w-2 rounded-full animate-pulse shadow-sm',
                    'bg-emerald-500 shadow-emerald-500/40' => $isAdmin,
                    'bg-yellow-100 shadow-yellow-100/60' => !$isAdmin
                ])></div>
                <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                    {{ $user->role->label() }}
                </span>
            </div>
        </div>
    </div>
</div>
