@use(App\Enums\RoleEnum)
@use(App\Enums\ModuleEnum)

@php
    $user = auth()->user();
    $isAdmin = $user->hasRole(RoleEnum::SUPER_ADMIN);
@endphp

<div class="space-y-8">
    {{-- Grupo Global --}}
    <div>
        <x-ui.sidebar-group-title>{{ __('dashboard.sidebar.groups.navigation') }}</x-ui.sidebar-group-title>
        <nav class="space-y-2">
            <x-ui.sidebar-link href="{{ route('dashboard.index') }}" :active="request()->routeIs('dashboard.index')" wire:navigate.hover>
                <x-slot:icon><x-lucide-house class="h-5 w-5" /></x-slot:icon>
                {{ __('dashboard.sidebar.links.dashboard') }}
            </x-ui.sidebar-link>

            @module(ModuleEnum::PROFILE)
            <x-ui.sidebar-link href="{{ route('dashboard.profile') }}" :active="request()->routeIs('dashboard.profile')" wire:navigate.hover>
                <x-slot:icon><x-lucide-user-circle class="h-5 w-5" /></x-slot:icon>
                {{ __('dashboard.sidebar.links.my_profile') }}
            </x-ui.sidebar-link>
            @endmodule

            @module(ModuleEnum::PROFILE_BADGE)
            <x-ui.sidebar-link href="{{ route('dashboard.profile.badge') }}" :active="request()->routeIs('dashboard.profile.badge')" wire:navigate.hover>
                <x-slot:icon><x-lucide-badge-check class="h-5 w-5" /></x-slot:icon>
                {{ __('Crachá do Escritor') }}
            </x-ui.sidebar-link>
            @endmodule

            @module(ModuleEnum::ACCOUNT)
            <x-ui.sidebar-link href="{{ route('dashboard.account') }}" :active="request()->routeIs('dashboard.account')" wire:navigate.hover>
                <x-slot:icon><x-lucide-settings class="h-5 w-5" /></x-slot:icon>
                {{ __('dashboard.sidebar.links.settings') }}
            </x-ui.sidebar-link>
            @endmodule
        </nav>
    </div>

    {{-- Visão do Escritor (Writer / Admin Bypass) --}}
    @if($user->hasRole(RoleEnum::WRITER) || $isAdmin)
        <div>
            <x-ui.sidebar-group-title>{{ __('dashboard.sidebar.groups.creation') }}</x-ui.sidebar-group-title>
            <nav class="space-y-1">
                @module(ModuleEnum::MY_POSTS)
                <x-ui.sidebar-link href="{{ route('dashboard.posts.index') }}" :active="request()->routeIs('dashboard.posts.index')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-library class="h-5 w-5" /></x-slot:icon>
                    {{ __('dashboard.sidebar.links.my_works') }}
                </x-ui.sidebar-link>
                @endmodule

                @module(ModuleEnum::DRAFT)
                <x-ui.sidebar-link href="{{ route('dashboard.posts.draft') }}" :active="request()->routeIs('dashboard.posts.draft')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-file-text class="h-5 w-5" /></x-slot:icon>
                    {{ __('dashboard.sidebar.links.drafts') }}
                </x-ui.sidebar-link>
                @endmodule

                <x-ui.sidebar-link href="{{ route('dashboard.posts.categories.index') }}" :active="request()->routeIs('dashboard.posts.categories.index')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-folder-open class="h-5 w-5" /></x-slot:icon>
                    {{ __('dashboard.sidebar.links.categories') }}
                </x-ui.sidebar-link>

                @module(ModuleEnum::FOLLOWS)
                <x-ui.sidebar-link href="{{ route('dashboard.follows') }}" :active="request()->routeIs('dashboard.follows')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-users-round class="h-5 w-5" /></x-slot:icon>
                    {{ __('dashboard.sidebar.links.followers') }}
                </x-ui.sidebar-link>
                @endmodule
            </nav>
        </div>
    @endif

    {{-- Visão da Comunidade (Reader / Admin Bypass) --}}
    <div>
        <x-ui.sidebar-group-title>{{ __('dashboard.sidebar.groups.community') }}</x-ui.sidebar-group-title>
        <nav class="space-y-2">
            @module(ModuleEnum::SAVED_POST)
            <x-ui.sidebar-link href="{{ route('dashboard.posts.saved') }}" :active="request()->routeIs('dashboard.posts.saved')" wire:navigate.hover>
                <x-slot:icon><x-lucide-bookmark class="h-5 w-5" /></x-slot:icon>
                {{ __('dashboard.sidebar.links.saved') }}
            </x-ui.sidebar-link>
            @endmodule

            @module(ModuleEnum::COMMENTS)
            <x-ui.sidebar-link href="{{ route('dashboard.comments') }}" :active="request()->routeIs('dashboard.comments')" wire:navigate.hover>
                <x-slot:icon><x-lucide-message-square class="h-5 w-5" /></x-slot:icon>
                {{ __('dashboard.sidebar.links.my_comments') }}
            </x-ui.sidebar-link>
            @endmodule
        </nav>
    </div>

    {{-- Gestão de Assinatura --}}
    @module(ModuleEnum::SUBSCRIPTIONS)
        <div>
            <x-ui.sidebar-group-title>{{ __('Assinatura e Planos') }}</x-ui.sidebar-group-title>
            <nav class="space-y-2">
                <x-ui.sidebar-link href="{{ route('dashboard.billing.plans') }}" :active="request()->routeIs('dashboard.billing.plans')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-credit-card class="h-5 w-5" /></x-slot:icon>
                    {{ $user->subscribed() ? 'Mudar Plano' : 'Seja Pro' }}
                </x-ui.sidebar-link>

                @if($user->subscribed())
                    <x-ui.sidebar-link href="{{ route('dashboard.billing.portal') }}" wire:navigate="false">
                        <x-slot:icon><x-lucide-external-link class="h-5 w-5" /></x-slot:icon>
                        Gerenciar Pagamento
                    </x-ui.sidebar-link>
                @endif
            </nav>
        </div>
    @endmodule

    {{-- Gestão Master (Admin Only) --}}
    @can('admin')
        <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800">
            <x-ui.sidebar-group-title>{{ __('dashboard.sidebar.groups.master') }}</x-ui.sidebar-group-title>
            <nav class="space-y-1">
                <x-ui.sidebar-link href="{{ route('dashboard.admin.users.index') }}" :active="request()->routeIs('dashboard.admin.users.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-users class="h-5 w-5" /></x-slot:icon>
                    {{ __('dashboard.sidebar.links.users') }}
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.admin.subscriptions.index') }}" :active="request()->routeIs('dashboard.admin.subscriptions.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-credit-card class="h-5 w-5" /></x-slot:icon>
                    Assinaturas
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.admin.newsletter.index') }}" :active="request()->routeIs('dashboard.admin.newsletter.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-mail-plus class="h-5 w-5" /></x-slot:icon>
                    {{ __('dashboard.sidebar.links.newsletter') }}
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.admin.reports.index') }}" :active="request()->routeIs('dashboard.admin.reports.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-shield-alert class="h-5 w-5" /></x-slot:icon>
                    {{ __('dashboard.sidebar.links.moderation') }}
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.admin.posts.views') }}" :active="request()->routeIs('dashboard.admin.posts.views')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-bar-chart-3 class="h-5 w-5" /></x-slot:icon>
                    {{ __('dashboard.sidebar.links.stats') }}
                </x-ui.sidebar-link>

                <x-ui.sidebar-link href="{{ route('dashboard.admin.modules.index') }}" :active="request()->routeIs('dashboard.admin.modules.*')" wire:navigate.hover>
                    <x-slot:icon><x-lucide-component class="h-5 w-5" /></x-slot:icon>
                    {{ __('dashboard.sidebar.links.modules') }}
                </x-ui.sidebar-link>
            </nav>
        </div>
    @endcan

    @if(!$user->subscribed('pro') && !$user->onTrial() && !$isAdmin)
        <div class="px-4 pt-4">
            <div class="rounded-[2.5rem] bg-indigo-600 p-6 text-white shadow-xl shadow-indigo-500/20 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 bg-white/10 rounded-full blur-2xl transition-all group-hover:scale-150"></div>

                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-2 italic">Drafto Plus</p>
                    <p class="text-xs font-bold leading-tight mb-4">Desbloqueie cores customizadas, crachás HD e estatísticas avançadas.</p>

                    <a href="{{ route('dashboard.billing.plans') }}" wire:navigate class="inline-flex w-full items-center justify-center rounded-2xl bg-white py-3 text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:bg-zinc-100 transition shadow-lg active:scale-95">
                        Fazer Upgrade
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Footer --}}
    <div x-show="!sidebarCollapsed" x-transition class="pt-6">
        <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900 p-4 border border-zinc-100 dark:border-zinc-800 transition-colors">
            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2">{{ __('dashboard.sidebar.current_session') }}</p>
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
