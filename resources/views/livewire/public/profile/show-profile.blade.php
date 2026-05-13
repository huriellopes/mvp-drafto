@use(App\Enums\ThemePlatformEnum)
@use(App\Enums\ProfileVisibilityEnum)
@use(Illuminate\Support\Facades\Storage)
@use(Carbon\Carbon)

@php
    $settings = $this->user->profile->settings;
    $cardClasses = match($settings->card_style) {
        'shadow' => 'shadow-xl shadow-zinc-900/5 border-0',
        'flat' => 'border-0 bg-zinc-100/50 dark:bg-zinc-900/50',
        default => 'border border-zinc-200 dark:border-zinc-800'
    };
    $layoutClasses = match($settings->layout_type) {
        'centered' => 'flex flex-col items-center text-center',
        default => ''
    };
@endphp

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950" 
     style="background-color: var(--profile-bg); color: var(--profile-text);"
     x-data="{
        scrolled: false,
        init() {
            window.addEventListener('scroll', () => { this.scrolled = window.scrollY > 50 })
        }
     }">
    @if($this->isOwner && !$this->isProfileComplete)
        <div class="relative overflow-hidden bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-profile-button mb-12 shadow-sm animate-in fade-in slide-in-from-top-4 duration-700">
            <div class="absolute top-0 right-0 p-4">
                <div class="text-[40px] font-black text-zinc-50 dark:text-zinc-800/50 leading-none">
                    {{ $this->user->profile->getCompletionPercentage() }}%
                </div>
            </div>

            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <x-lucide-sparkles class="h-6 w-6" />
                    </div>
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-tighter text-zinc-900 dark:text-white">Perfil em Construção</h4>
                        <p class="text-xs text-zinc-500 font-medium">Complete seu perfil para desbloquear todo o potencial da sua estante.</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:block w-32 h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-600 transition-all duration-1000" style="width: {{ $this->user->profile->getCompletionPercentage() }}%"></div>
                    </div>
                    <a href="{{ route('dashboard.profile') }}" wire:navigate class="px-6 py-3 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-[10px] font-black uppercase tracking-widest rounded-profile-button hover:scale-105 transition-all shadow-xl shadow-zinc-900/10">
                        Completar Perfil
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- 1. Verificação de Perfil Incompleto para visitantes --}}
    @if($this->user->profile->visibility === ProfileVisibilityEnum::PRIVATE && !$this->isOwner)
        <div class="flex min-h-[80vh] flex-col items-center justify-center px-4 text-center animate-in fade-in duration-700">
            <div class="h-24 w-24 bg-zinc-100 dark:bg-zinc-900 rounded-[2.5rem] flex items-center justify-center mb-8">
                <x-lucide-lock class="h-10 w-10 text-zinc-300 dark:text-zinc-700" />
            </div>
            <h1 class="text-5xl font-black text-zinc-900 dark:text-white tracking-tighter italic">Privacidade <span class="text-indigo-600">Absoluta.</span></h1>
            <p class="mt-4 text-zinc-500 font-medium max-w-sm">Este escritor optou por manter sua estante privada no momento.</p>
            <a href="/" wire:navigate class="mt-8 px-8 py-3 rounded-2xl bg-zinc-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl">Voltar ao Início</a>
        </div>

    @elseif(!$this->isProfileComplete && !$this->isOwner)
        <div class="flex min-h-[80vh] flex-col items-center justify-center px-4 text-center" wire:key="construction-state">
            <div class="relative mb-8">
                <div class="absolute -inset-4 rounded-full bg-profile-primary/10 blur-2xl animate-pulse"></div>
                <x-lucide-construction class="relative h-20 w-20 text-profile-primary" />
            </div>

            <h1 class="text-3xl font-black tracking-tight text-zinc-900 dark:text-white md:text-5xl">
                Perfil em Construção
            </h1>
            <p class="mt-4 max-w-md text-lg text-zinc-500 dark:text-zinc-400">
                O escritor <strong>{{ '@' . $this->username }}</strong> está organizando sua estante. Volte em breve para conferir as publicações.
            </p>

            <a href="/" wire:navigate class="mt-10 font-bold text-profile-primary hover:underline flex items-center gap-2">
                <x-lucide-arrow-left class="h-4 w-4" />
                Explorar outros escritores
            </a>
        </div>
    @else
        {{-- 2. Perfil Ativo ou Preview do Dono --}}
        <div class="pb-20" wire:key="profile-active-state">
            {{-- Banner de Alerta para o Dono --}}
            @if($this->isOwner)
                <div class="relative z-[60] flex items-center justify-between bg-zinc-950 px-6 py-2.5 text-white border-b border-white/5">
                    <div class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em]">
                        <span class="flex h-2 w-2 rounded-full bg-indigo-500 animate-ping"></span>
                        Modo de Visualização do Autor
                    </div>
                    <a href="{{ route('dashboard.profile') }}" wire:navigate class="text-[9px] font-black uppercase tracking-widest px-4 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition">
                        Editar Perfil
                    </a>
                </div>
            @endif

            {{-- Cover Image --}}
            <div class="relative h-64 w-full bg-zinc-200 dark:bg-zinc-800 md:h-96">
                @if($this->user->profile->cover_path)
                    <img
                        src="{{ Storage::url($this->user->profile->cover_path) }}"
                        class="h-full w-full object-cover"
                        alt="Capa {{ $this->user->display_name }}"
                    />
                @else
                    <div class="h-full w-full bg-profile-primary/10"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="relative -mt-32 z-10">
                    <div @class([
                        'flex flex-col md:flex-row md:items-end md:justify-between md:gap-8',
                        'md:flex-col md:items-center md:text-center' => $settings->layout_type === 'centered'
                    ])>

                        {{-- Avatar --}}
                        <div class="h-44 w-44 overflow-hidden rounded-[2.5rem] border-[6px] border-zinc-50 dark:border-zinc-950 bg-white dark:bg-zinc-900 shadow-2xl transition-transform hover:scale-105">
                            @if($this->user->profile->avatar_path)
                                <img src="{{ Storage::url($this->user->profile->avatar_path) }}" class="h-full w-full object-cover" alt="{{ $this->user->display_name }}" />
                            @else
                                <div class="flex h-full items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-5xl font-bold text-zinc-300">
                                    {{ substr($this->user->display_name, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="mt-8 flex items-center gap-3 md:mb-4">
                            <x-ui.share-profile :username="$this->user->profile->username" />

                            @if($this->user->profile->website_url)
                                <a href="{{ $this->user->profile->website_url }}" target="_blank" class="flex h-12 w-12 items-center justify-center rounded-profile-button {{ $cardClasses }} text-zinc-600 transition hover:text-profile-primary">
                                    <x-lucide-globe class="h-5 w-5" />
                                </a>
                            @endif

                            @if (!$this->isOwner)
                                <livewire:actions.follow-button :user="$this->user" :key="'follow-'.$this->user->id" />
                            @else
                                <a href="{{ route('dashboard.profile') }}" wire:navigate class="inline-flex h-12 px-6 items-center justify-center rounded-profile-button {{ $cardClasses }} text-sm font-bold text-zinc-900 shadow-sm transition hover:bg-zinc-50">
                                    <x-lucide-settings class="mr-2 h-4 w-4" />
                                    Configurar
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Info Grid --}}
                    <div @class([
                        'mt-8 grid grid-cols-1 gap-12',
                        'lg:grid-cols-3' => $settings->layout_type === 'default',
                        'max-w-4xl mx-auto' => $settings->layout_type !== 'default'
                    ])>
                        <div @class([
                            'lg:col-span-2' => $settings->layout_type === 'default',
                            'flex flex-col items-center text-center' => $settings->layout_type === 'centered'
                        ])>
                            <h1 class="text-5xl md:text-6xl font-black tracking-tighter text-zinc-900 dark:text-white italic flex items-center gap-3">
                                {{ $this->user->display_name }}
                                @if($this->user->isVerified())
                                    <x-lucide-badge-check class="h-8 w-8 text-blue-500 fill-blue-500/10" />
                                @endif
                            </h1>

                            <div @class([
                                'mt-2 flex flex-wrap items-center gap-3',
                                'justify-center' => $settings->layout_type === 'centered'
                            ])>
                                <p class="text-xl font-semibold text-profile-primary">
                                    {{ $this->user->profile->handle ?? '@' . $this->user->profile->username }}
                                </p>

                                {{-- Location Badge --}}
                                @if($this->user->profile->location)
                                    <span class="flex items-center gap-1.5 text-xs font-bold text-zinc-400 uppercase tracking-widest">
                                        <span class="h-1 w-1 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                                        <x-lucide-map-pin class="h-3.5 w-3.5 text-profile-primary" />
                                        {{ $this->user->profile->location }}
                                    </span>
                                @endif
                            </div>

                            @if($this->user->profile->bio)
                                <div x-data="{
            expanded: false,
            showButton: false,
            checkResize() {
                this.showButton = this.$refs.bioText.scrollHeight > this.$refs.bioText.offsetHeight;
            }
         }"
                                     {{-- O nextTick garante que o seção 'line-clamp-3' já foi aplicado antes de medir --}}
                                     x-init="$nextTick(() => checkResize())"
                                     {{-- Se a janela redimensionar, recalculamos (importante para responsividade) --}}
                                     @window.resize.debounce.200ms="checkResize()"
                                     @class([
                                         'relative max-w-3xl',
                                         'text-center' => $settings->layout_type === 'centered',
                                         'mx-auto' => $settings->layout_type !== 'default'
                                     ])>

                                    <p x-ref="bioText"
                                       :class="expanded ? '' : 'line-clamp-3'"
                                       class="text-lg leading-relaxed text-zinc-600 dark:text-zinc-400 font-medium transition-all duration-500 whitespace-pre-line">
                                        {{ $this->user->profile->bio }}
                                    </p>

                                    <button x-show="showButton"
                                            @click="expanded = !expanded"
                                            type="button"
                                            @class([
                                                'mt-3 text-[10px] font-black uppercase tracking-[0.2em] text-profile-primary hover:opacity-70 transition flex items-center gap-1 group',
                                                'mx-auto' => $settings->layout_type !== 'default'
                                            ])>
                                        <span x-text="expanded ? 'Reduzir biografia' : 'Ver biografia completa'"></span>
                                        <x-lucide-chevron-down
                                            class="h-3 w-3 transition-transform duration-500"
                                            ::class="expanded ? 'rotate-180' : 'group-hover:translate-y-0.5'"
                                        />
                                    </button>
                                </div>
                            @endif
                            <div @class([
                                'mt-8 flex flex-wrap items-center gap-4 text-sm font-medium text-zinc-500',
                                'justify-center' => $settings->layout_type !== 'default'
                            ])>
                                {{-- Followers Count --}}
                                @if($settings->show_subscriber_count)
                                <span class="flex items-center gap-2 bg-white dark:bg-zinc-900 px-4 py-2 rounded-profile-button {{ $cardClasses }} shadow-sm transition-all hover:shadow-md">
                                    <x-lucide-users class="h-4 w-4 text-profile-primary" />
                                    <span class="font-bold text-zinc-900 dark:text-white">{{ number_format($this->user->followers->count()) }}</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest opacity-70">seguidores</span>
                                </span>
                                @endif

                                {{-- Views Count --}}
                                @if($settings->show_view_count)
                                <span class="flex items-center gap-2 bg-white dark:bg-zinc-900 px-4 py-2 rounded-profile-button {{ $cardClasses }} shadow-sm transition-all hover:shadow-md">
                                    <x-lucide-eye class="h-4 w-4 text-profile-primary" />
                                    <span class="font-bold text-zinc-900 dark:text-white">{{ number_format($this->user->profile->profile_views) }}</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest opacity-70">visitas</span>
                                </span>
                                @endif

                                {{-- Posts Count --}}
                                <span class="flex items-center gap-2 bg-white dark:bg-zinc-900 px-4 py-2 rounded-profile-button {{ $cardClasses }} shadow-sm transition-all hover:shadow-md">
                                    <x-lucide-pen-tool class="h-4 w-4 text-profile-primary" />
                                    <span class="font-bold text-zinc-900 dark:text-white">{{ number_format($this->user->published_posts_count ?? $this->user->posts()->published()->count()) }}</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest opacity-70">publicações</span>
                                </span>
                            </div>
                        </div>

                        @if($settings->layout_type === 'default')
                        <aside class="hidden lg:block animate-in fade-in slide-in-from-right-8 duration-1000 delay-300">
                            <div class="rounded-3xl {{ $cardClasses }} bg-white dark:bg-zinc-900 p-8 shadow-sm sticky top-32">
                                <h3 class="font-bold text-zinc-900 dark:text-white mb-4 italic tracking-tighter text-xl">Sobre o autor</h3>
                                <p class="text-sm text-zinc-500 leading-relaxed font-medium">
                                    Membro desde <span class="text-zinc-900 dark:text-zinc-200">{{ $this->user->created_at->translatedFormat('F Y') }}</span>.
                                </p>
                            </div>
                        </aside>
                        @endif
                    </div>
                </div>

                {{-- Posts Grid --}}
                <div class="mt-32 space-y-12 animate-in fade-in slide-in-from-bottom-12 duration-1000 delay-500">
                    <div @class([
                        'flex items-center gap-6',
                        'flex-col text-center' => $settings->layout_type === 'grid'
                    ])>
                        <h2 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tighter italic">Publicações <span class="text-profile-primary">Recentes.</span></h2>
                        @if($settings->layout_type !== 'grid')
                            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
                        @endif
                    </div>

                    <div @class([
                        'grid grid-cols-1 gap-8',
                        'sm:grid-cols-2 lg:grid-cols-3' => $settings->layout_type !== 'grid',
                        'sm:grid-cols-2 lg:grid-cols-4' => $settings->layout_type === 'grid'
                    ])>
                        @forelse($this->posts as $post)
                            <div class="hover:-translate-y-2 transition-transform duration-500">
                                <x-public.post-card :post="$post" />
                            </div>
                        @empty
                            <div class="col-span-full py-20 text-center rounded-3xl border-2 border-dashed border-zinc-200">
                                <x-lucide-ghost class="mx-auto h-12 w-12 text-zinc-300" />
                                <p class="mt-4 text-lg text-zinc-500">Nenhum conteúdo publicado.</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-12">
                        {{ $this->posts->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
