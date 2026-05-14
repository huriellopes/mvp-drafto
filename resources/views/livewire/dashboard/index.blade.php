@use(App\Enums\RoleEnum)

<div class="space-y-8 animate-in fade-in duration-700">
    {{-- Top Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{ Breadcrumbs::render('dashboard.index') }}

        <div class="hidden sm:block">
            <x-ui.status-badge :label="__('dashboard.modules.status.online')" />
        </div>
    </div>

    {{-- Banner de Verificação --}}
    <x-dashboard.verification-banner />

    {{-- Banner de Boas-vindas (Reutilizável) --}}
    <x-dashboard.welcome-banner :user="$this->user" />

    @if (auth()->user()->hasRole(RoleEnum::WRITER) || auth()->user()->hasRole(RoleEnum::SUPER_ADMIN))
        {{-- Status do Perfil --}}
        <livewire:dashboard.profile-status />
    @endif

    {{-- Widgets de Estatísticas --}}
    <livewire:dashboard.widgets.stats-overview lazy />

    {{-- Grid Principal --}}
    <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
        {{-- Conteúdo Principal (Esquerda) --}}
        <div class="xl:col-span-2 space-y-8">
            <livewire:dashboard.widgets.recent-activity lazy />

            @if($this->user->hasRole(RoleEnum::READER))
                <livewire:dashboard.widgets.suggested-writers />
            @endif
        </div>

        {{-- Barra Lateral (Direita) --}}
        <div class="space-y-8">
            <livewire:dashboard.widgets.profile-info />

            @if(!$this->user->hasRole(RoleEnum::READER))
                <livewire:dashboard.widgets.suggested-writers />
            @endif
        </div>
    </div>
</div>
