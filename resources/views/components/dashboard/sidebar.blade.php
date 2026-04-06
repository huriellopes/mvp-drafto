@use(App\Enums\RoleEnum)
@php
    $user = auth()->user();
@endphp

<div class="space-y-8">
    {{-- Grupo Global: Acessível a todos --}}
    <div>
        <x-ui.sidebar-group-title>Navegação</x-ui.sidebar-group-title>
        <nav class="space-y-2">
            <x-ui.sidebar-link href="{{ route('dashboard.index') }}" :active="request()->routeIs('dashboard.index')" wire:navigate.hover>
                <x-slot:icon><x-lucide-house class="h-5 w-5" /></x-slot:icon>
                Dashboard
            </x-ui.sidebar-link>

            <x-ui.sidebar-link href="{{ route('dashboard.profile') }}" :active="request()->routeIs('dashboard.profile')" wire:navigate.hover>
                <x-slot:icon><x-lucide-user-circle class="h-5 w-5" /></x-slot:icon>
                Meu Perfil Público
            </x-ui.sidebar-link>

            <x-ui.sidebar-link href="{{ route('dashboard.account') }}" :active="request()->routeIs('dashboard.account')" wire:navigate.hover>
                <x-slot:icon><x-lucide-settings class="h-5 w-5" /></x-slot:icon>
                Configurações
            </x-ui.sidebar-link>
        </nav>
    </div>

    {{-- Visão do Escritor (Writer) --}}
    @if($user->hasRole(RoleEnum::WRITER) || $user->isAdmin())
        <div>
            <x-ui.sidebar-group-title>Escritório de Criação</x-ui.sidebar-group-title>
            <nav class="space-y-1">
                <x-ui.sidebar-link href="{{ route('dashboard.posts.index') }}" :active="request()->routeIs('dashboard.posts.index')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-library class="h-5 w-5" /></x-slot:icon>
                    Minhas Obras
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.posts.draft') }}" :active="request()->routeIs('dashboard.posts.draft')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-file-text class="h-5 w-5" /></x-slot:icon>
                    Rascunhos
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.follows') }}" :active="request()->routeIs('dashboard.follows')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-users-round class="h-5 w-5" /></x-slot:icon>
                    Seguidores
                </x-ui.sidebar-link>
            </nav>
        </div>
    @endif

    {{-- Visão do Leitor (Reader) --}}
    <div>
        <x-ui.sidebar-group-title>Comunidade</x-ui.sidebar-group-title>
        <nav class="space-y-2">
            <x-ui.sidebar-link href="{{ route('dashboard.posts.saved') }}" :active="request()->routeIs('dashboard.posts.saved')" wire:navigate.hover>
                <x-slot:icon><x-lucide-bookmark class="h-5 w-5" /></x-slot:icon>
                Salvos
            </x-ui.sidebar-link>

            <x-ui.sidebar-link href="{{ route('dashboard.comments') }}" :active="request()->routeIs('dashboard.comments')" wire:navigate.hover>
                <x-slot:icon><x-lucide-message-square class="h-5 w-5" /></x-slot:icon>
                Meus Comentários
            </x-ui.sidebar-link>
        </nav>
    </div>

    {{-- Visão do Super Admin --}}
    @if($user->isAdmin())
        <div class="pt-4 border-t border-zinc-100">
            <x-ui.sidebar-group-title>Gestão Master</x-ui.sidebar-group-title>
            <nav class="space-y-1">
                <x-ui.sidebar-link href="{{ route('dashboard.users.index') }}" :active="request()->routeIs('dashboard.users.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-users class="h-5 w-5" /></x-slot:icon>
                    Controle de Usuários
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.newsletter.index') }}" :active="request()->routeIs('dashboard.newsletter.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-mail-plus class="h-5 w-5" /></x-slot:icon>
                    Newsletter
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.reports.index') }}" :active="request()->routeIs('dashboard.reports.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-shield-alert class="h-5 w-5" /></x-slot:icon>
                    Moderação / Denúncias
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.posts.views') }}" :active="request()->routeIs('dashboard.posts.views')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-bar-chart-3 class="h-5 w-5" /></x-slot:icon>
                    Posts Views
                </x-ui.sidebar-link>
            </nav>
        </div>
    @endif

    {{-- Footer da Sidebar --}}
    <div x-show="!sidebarCollapsed" x-transition class="pt-6">
        <div class="rounded-2xl bg-zinc-50 p-4 border border-zinc-100">
            <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">Sessão Atual</p>
            <div class="flex items-center gap-3">
                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-xs font-bold text-zinc-700">{{ $user->role->label() }}</span>
            </div>
        </div>
    </div>
</div>
