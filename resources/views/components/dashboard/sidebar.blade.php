@use(App\Enums\RoleEnum)
@php
    $user = auth()->user();
@endphp

<div class="space-y-8">
    {{-- Grupo Global: Acessível a todos --}}
    <div>
        <x-ui.sidebar-group-title>Navegação</x-ui.sidebar-group-title>
        <nav class="space-y-2">
            <x-ui.sidebar-link href="{{ route('dashboard.index') }}" :active="request()->routeIs('dashboard.index')">
                <x-slot:icon><x-lucide-house class="h-5 w-5" /></x-slot:icon>
                Dashboard
            </x-ui.sidebar-link>

            <x-ui.sidebar-link href="{{ route('dashboard.profile') }}" :active="request()->routeIs('dashboard.profile')">
                <x-slot:icon><x-lucide-user class="h-5 w-5" /></x-slot:icon>
                Perfil
            </x-ui.sidebar-link>
        </nav>
    </div>

    {{-- Visão do Escritor (Writer) --}}
    @if($user->hasRole(RoleEnum::WRITER) || $user->isAdmin())
        <div>
            <x-ui.sidebar-group-title>Escrita</x-ui.sidebar-group-title>
            <nav class="space-y-2">
                <x-ui.sidebar-link href="#" :active="request()->routeIs('dashboard.posts.*')">
                    <x-slot:icon><x-lucide-file-text class="h-5 w-5" /></x-slot:icon>
                    Meus conteúdos
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="#" :active="request()->routeIs('dashboard.drafts.*')">
                    <x-slot:icon><x-lucide-file-pen-line class="h-5 w-5" /></x-slot:icon>
                    Rascunhos
                </x-ui.sidebar-link>
            </nav>
        </div>
    @endif

    {{-- Visão do Leitor (Reader) --}}
    <div>
        <x-ui.sidebar-group-title>Comunidade</x-ui.sidebar-group-title>
        <nav class="space-y-2">
            <x-ui.sidebar-link href="#" :active="request()->routeIs('dashboard.saved.*')">
                <x-slot:icon><x-lucide-bookmark class="h-5 w-5" /></x-slot:icon>
                Salvos
            </x-ui.sidebar-link>

            <x-ui.sidebar-link href="#" :active="request()->routeIs('dashboard.comments.*')">
                <x-slot:icon><x-lucide-message-circle class="h-5 w-5" /></x-slot:icon>
                Comentários
            </x-ui.sidebar-link>
        </nav>
    </div>

    {{-- Visão do Super Admin --}}
    @if($user->isAdmin())
        <div>
            <x-ui.sidebar-group-title>Administração</x-ui.sidebar-group-title>
            <nav class="space-y-2">
                <x-ui.sidebar-link href="{{ route('dashboard.users.index') }}" :active="request()->routeIs('dashboard.users.*')">
                    <x-slot:icon><x-lucide-users class="h-5 w-5" /></x-slot:icon>
                    Usuários
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="#" :active="request()->routeIs('admin.reports.*')">
                    <x-slot:icon><x-lucide-shield-alert class="h-5 w-5" /></x-slot:icon>
                    Denúncias
                </x-ui.sidebar-link>
            </nav>
        </div>
    @endif

    {{-- Resumo do Perfil (Apenas se não estiver colapsado) --}}
    <div x-show="!sidebarCollapsed" x-transition class="pt-4">
        <x-ui.sidebar-group-title>Seu Espaço</x-ui.sidebar-group-title>
        <div class="space-y-3">
            <x-ui.info-card title="Identidade" :value="$user->profile->handle" />
            <x-ui.info-card title="Papel" :value="$user->role->label()" />
        </div>
    </div>
</div>
