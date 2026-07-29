@use(App\Enums\ThemePlatformEnum)
@use(App\Enums\ProfileVisibilityEnum)
@use(Illuminate\Support\Facades\Storage)
@use(Carbon\Carbon)

@php
    $settings = $this->user->profile->settings;
    $translucent = (bool) $settings->translucent_background;

    // Fundo translúcido (padrão): vidro fosco com blur. Sólido: bloco opaco.
    //
    // No escuro usamos um tingimento BRANCO bem sutil (bg-white/[0.07]), não
    // outro cinza — testado visualmente e confirmado: qualquer tom de zinc
    // sobre a página (que já é zinc-950) fica quase idêntico ao fundo,
    // "sumindo" mesmo com blur (não há nada colorido atrás pra desfocar).
    // Um tingimento claro sobre o escuro é o padrão real de glass do
    // iOS/macOS em dark mode e se destaca por si só, sem depender do que
    // está por trás. O ring substitui a borda nas variantes translúcidas
    // pelo mesmo motivo — contorno visível independente do fundo.
    $cardClasses = match(true) {
        $settings->card_style === 'shadow' && $translucent => 'shadow-xl shadow-black/10 dark:shadow-black/50 border-0 ring-1 ring-black/10 dark:ring-white/25 bg-white/70 dark:bg-white/[0.14] backdrop-blur-xl',
        $settings->card_style === 'shadow' => 'shadow-xl shadow-zinc-900/5 border-0 bg-white dark:bg-zinc-900',
        $settings->card_style === 'flat' && $translucent => 'border-0 ring-1 ring-black/10 dark:ring-white/20 bg-white/50 dark:bg-white/10 backdrop-blur-xl',
        $settings->card_style === 'flat' => 'border-0 bg-zinc-100/50 dark:bg-zinc-900/50',
        $translucent => 'border border-black/10 dark:border-white/25 bg-white/70 dark:bg-white/[0.14] backdrop-blur-xl',
        default => 'border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900',
    };

    $layoutClasses = match($settings->layout_type) {
        'centered' => 'flex flex-col items-center text-center',
        default => ''
    };

    $avatarShapeClasses = match($settings->avatar_shape) {
        'circle' => 'rounded-full',
        'square' => 'rounded-2xl',
        default => 'rounded-[2rem] sm:rounded-[2.5rem]',
    };

    $coverPositionClass = match($settings->cover_position) {
        'top' => 'object-top',
        'bottom' => 'object-bottom',
        default => 'object-center',
    };

    $isCompact = $settings->density === 'compact';
    $sectionSpacing = $isCompact ? 'mt-16 space-y-8' : 'mt-32 space-y-12';
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
        <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-profile-button mb-12 shadow-sm animate-in fade-in slide-in-from-top-4 duration-700">
                <div class="absolute top-0 right-0 p-4">
                    <div class="text-[40px] font-black text-zinc-50 dark:text-zinc-800/50 leading-none">
                        {{ $this->user->profile->getCompletionPercentage() }}%
                    </div>
                </div>

                <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 shrink-0 rounded-2xl bg-profile-primary/10 flex items-center justify-center text-profile-primary">
                            <x-lucide-sparkles class="h-6 w-6" />
                        </div>
                        <div>
                            <h4 class="text-sm font-black uppercase tracking-tighter text-profile-text">Perfil em Construção</h4>
                            <p class="text-xs text-zinc-500 font-medium">Complete seu perfil para desbloquear todo o potencial da sua estante.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="hidden md:block w-32 h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                            <div class="h-full bg-profile-primary transition-all duration-1000" style="width: {{ $this->user->profile->getCompletionPercentage() }}%"></div>
                        </div>
                        <a href="{{ route('dashboard.profile') }}" wire:navigate class="shrink-0 px-6 py-3 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-[10px] font-black uppercase tracking-widest rounded-profile-button hover:scale-105 transition-all shadow-xl shadow-zinc-900/10">
                            Completar Perfil
                        </a>
                    </div>
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
            <h1 class="text-5xl font-black text-profile-text tracking-tighter italic">Privacidade <span class="text-profile-primary">Absoluta.</span></h1>
            <p class="mt-4 text-zinc-500 font-medium max-w-sm">Este escritor optou por manter sua estante privada no momento.</p>
            <a href="/" wire:navigate class="mt-8 px-8 py-3 rounded-2xl bg-zinc-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-profile-primary transition-all shadow-xl">Voltar ao Início</a>
        </div>

    @elseif(!$this->isProfileEssentialComplete && !$this->isOwner)
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
                <x-lucide-chevron-left class="h-4 w-4" />
                Explorar outros escritores
            </a>
        </div>
    @else
        {{-- 2. Perfil Ativo ou Preview do Dono --}}
        <div class="pb-20" wire:key="profile-active-state">
            {{-- Aviso Flutuante para o Dono --}}
            @if($this->isOwner)
                <x-ui.author-preview-badge />
            @endif

            {{-- Cover Image --}}
            <div class="relative h-64 w-full bg-zinc-200 dark:bg-zinc-800 md:h-96">
                @if($this->user->profile->cover_path)
                    <img
                        src="{{ Storage::url($this->user->profile->cover_path) }}"
                        class="h-full w-full object-cover {{ $coverPositionClass }}"
                        alt="Capa {{ $this->user->display_name }}"
                    />
                @else
                    <div class="h-full w-full bg-profile-primary/10"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="relative -mt-20 sm:-mt-28 lg:-mt-32 z-10">
                    <div @class([
                        'flex items-end justify-between gap-4 md:gap-8',
                        'flex-col items-center text-center' => $settings->layout_type === 'centered'
                    ])>

                        {{-- Avatar --}}
                        <div class="h-32 w-32 shrink-0 overflow-hidden {{ $avatarShapeClasses }} border-[5px] border-zinc-50 dark:border-zinc-950 bg-white dark:bg-zinc-900 shadow-2xl transition-transform hover:scale-105 sm:h-40 sm:w-40 sm:border-[6px] lg:h-44 lg:w-44">
                            @if($this->user->profile->avatar_path)
                                <img src="{{ Storage::url($this->user->profile->avatar_path) }}" class="h-full w-full object-cover" alt="{{ $this->user->display_name }}" />
                            @else
                                <div class="flex h-full items-center justify-center bg-profile-primary/10 text-5xl font-bold text-profile-primary">
                                    {{ substr($this->user->display_name, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="mt-8 mb-3 flex flex-wrap items-center gap-3 md:mb-5">
                            <x-ui.share-profile :user="$this->user" />

                            @if($this->user->profile->website_url)
                                <a href="{{ $this->user->profile->website_url }}"
                                   target="_blank"
                                   class="inline-flex h-12 w-12 items-center justify-center rounded-profile-button bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-sm font-bold text-zinc-900 dark:text-white shadow-sm transition hover:bg-zinc-50 dark:hover:bg-zinc-800 active:scale-95"
                                   title="Website"
                                >
                                    <x-lucide-globe class="h-4 w-4 text-profile-primary" />
                                </a>
                            @endif

                            @if($this->user->profile->show_email_publicly && $this->user->profile->email)
                                <a href="mailto:{{ $this->user->profile->email }}"
                                   class="flex h-12 w-12 items-center justify-center rounded-profile-button bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-300 transition hover:text-profile-primary dark:hover:text-profile-primary shadow-sm active:scale-95"
                                   title="Enviar E-mail"
                                >
                                    <x-lucide-mail class="h-5 w-5" />
                                </a>
                            @endif

                            @if (!$this->isOwner)
                                <livewire:actions.follow-button :user="$this->user" :inline="true" :key="'follow-'.$this->user->id" />
                            @else
                                <a href="{{ route('dashboard.profile') }}" wire:navigate title="Configurar" class="inline-flex h-12 w-12 md:w-auto md:px-6 items-center justify-center rounded-profile-button bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-sm font-bold text-zinc-900 dark:text-white shadow-sm transition hover:bg-zinc-50 dark:hover:bg-zinc-800 active:scale-95">
                                    <x-lucide-settings class="h-4 w-4 md:mr-2 text-profile-primary" />
                                    <span class="hidden md:inline">Configurar</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Redes sociais e links do perfil --}}
                    <x-public.profile-links :profile="$this->user->profile" />

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
                            <h1 @class([
                                'text-4xl sm:text-5xl md:text-6xl font-black tracking-tighter text-profile-text italic flex flex-wrap items-center gap-x-3 gap-y-1 break-words',
                                'justify-center' => $settings->layout_type === 'centered',
                            ])>
                                {{ $this->user->display_name }}
                                @if($this->user->isVerified())
                                    <x-lucide-badge-check class="h-7 w-7 shrink-0 text-blue-500 fill-blue-500/10 sm:h-8 sm:w-8" />
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
                                    <span class="flex items-center gap-1.5 text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">
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
                                       class="text-lg leading-relaxed text-profile-text/70 font-medium transition-all duration-500 whitespace-pre-line">
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
                                <span class="flex items-center gap-2 px-4 py-2 rounded-profile-button {{ $cardClasses }} shadow-sm transition-all hover:shadow-md">
                                    <x-lucide-users class="h-4 w-4 text-profile-primary" />
                                    <span class="font-bold text-profile-text">{{ number_format($this->user->followers_count) }}</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest opacity-70">seguidores</span>
                                </span>
                                @endif

                                {{-- Views Count --}}
                                @if($settings->show_view_count)
                                <span class="flex items-center gap-2 px-4 py-2 rounded-profile-button {{ $cardClasses }} shadow-sm transition-all hover:shadow-md">
                                    <x-lucide-eye class="h-4 w-4 text-profile-primary" />
                                    <span class="font-bold text-profile-text">{{ number_format($this->user->profile->profile_views) }}</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest opacity-70">visitas</span>
                                </span>
                                @endif

                                {{-- Posts Count --}}
                                <span class="flex items-center gap-2 px-4 py-2 rounded-profile-button {{ $cardClasses }} shadow-sm transition-all hover:shadow-md">
                                    <x-lucide-pen-tool class="h-4 w-4 text-profile-primary" />
                                    <span class="font-bold text-profile-text">{{ number_format($this->user->published_posts_count ?? 0) }}</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest opacity-70">publicações</span>
                                </span>
                            </div>
                        </div>

                        @if($settings->layout_type === 'default')
                        <aside class="hidden lg:block animate-in fade-in slide-in-from-right-8 duration-1000 delay-300">
                            <div class="rounded-3xl {{ $cardClasses }} p-8 shadow-sm sticky top-32">
                                <h3 class="font-bold text-profile-text mb-4 italic tracking-tighter text-xl">Sobre o autor</h3>
                                <p class="text-sm text-zinc-500 leading-relaxed font-medium">
                                    Membro desde <span class="text-profile-text">{{ $this->user->created_at->translatedFormat('F Y') }}</span>.
                                </p>
                            </div>
                        </aside>
                        @endif
                    </div>
                </div>

                {{-- Coleções públicas --}}
                @if($this->publicCollections->isNotEmpty())
                    <div class="{{ $sectionSpacing }} animate-in fade-in slide-in-from-bottom-12 duration-1000 delay-300">
                        <div @class([
                            'flex items-center gap-6',
                            'flex-col text-center' => $settings->layout_type === 'grid'
                        ])>
                            <h2 class="text-3xl font-black text-profile-text tracking-tighter italic"><span class="text-profile-primary">Coleções.</span></h2>
                            @if($settings->layout_type !== 'grid')
                                <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($this->publicCollections as $collection)
                                <a href="{{ route('profile.collection', ['username' => $this->user->profile->username, 'collection' => $collection->slug]) }}"
                                   wire:navigate
                                   class="group relative flex flex-col gap-3 p-6 rounded-3xl {{ $cardClasses }} hover:border-profile-primary hover:-translate-y-1 transition-all duration-500">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-profile-primary/10 text-profile-primary">
                                            <x-lucide-folder-tree class="h-5 w-5" />
                                        </span>
                                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                            {{ $collection->published_posts_count }} {{ \Illuminate\Support\Str::plural('obra', $collection->published_posts_count) }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-black text-profile-text tracking-tight group-hover:text-profile-primary transition-colors">{{ $collection->name }}</h3>
                                    @if($collection->description)
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">{{ $collection->description }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Posts Grid --}}
                <div class="{{ $sectionSpacing }} animate-in fade-in slide-in-from-bottom-12 duration-1000 delay-500">
                    <div @class([
                        'flex items-center gap-6',
                        'flex-col text-center' => $settings->layout_type === 'grid'
                    ])>
                        <h2 class="text-3xl font-black text-profile-text tracking-tighter italic">Publicações <span class="text-profile-primary">Recentes.</span></h2>
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
