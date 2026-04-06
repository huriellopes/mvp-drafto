@use(App\Enums\ThemePlatformEnum)
@use(App\Enums\ProfileVisibilityEnum)
@use(Illuminate\Support\Facades\Storage)
@use(Carbon\Carbon)

<div class="min-h-screen">
    {{-- 1. Verificação de Perfil Incompleto para visitantes --}}
    @if($this->user->profile->visibility === ProfileVisibilityEnum::PRIVATE && !$this->isOwner)
        <div class="flex min-h-[80vh] flex-col items-center justify-center px-4 text-center">
            <x-lucide-lock class="h-16 w-16 text-zinc-300 mb-6" />
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white">Perfil Privado</h1>
            <p class="mt-4 text-zinc-500">Este escritor optou por manter sua estante privada.</p>
            <a href="/" class="mt-8 text-profile-primary font-bold hover:underline">Voltar ao Início</a>
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

            <a href="/" class="mt-10 font-bold text-profile-primary hover:underline flex items-center gap-2">
                <x-lucide-arrow-left class="h-4 w-4" />
                Explorar outros escritores
            </a>
        </div>
    @else
        {{-- 2. Perfil Ativo ou Preview do Dono --}}
        <div class="pb-20" wire:key="profile-active-state">
            {{-- Banner de Alerta para o Dono --}}
            @if((!$this->isProfileComplete || $this->user->profile->visibility === ProfileVisibilityEnum::PRIVATE) && $this->isOwner)
                <div class="sticky top-0 z-50 flex items-center justify-between bg-zinc-900 px-6 py-3 text-white shadow-xl">
                    <div class="flex items-center gap-3 text-xs font-bold uppercase tracking-widest">
                        <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                        Modo de Visualização Privada
                    </div>
                    <a href="{{ route('dashboard.profile') }}" class="text-[10px] font-black underline hover:text-profile-primary transition">
                        EDITAR CONFIGURAÇÕES
                    </a>
                </div>
            @elseif($this->isOwner)
                <div class="sticky top-0 z-0 flex items-center justify-between bg-zinc-900 px-6 py-3 text-white shadow-xl">
                    <div class="flex items-center gap-3 text-xs font-bold uppercase tracking-widest">
                        <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                        Modo de Visualização
                    </div>
                    <a href="{{ route('dashboard.profile') }}" class="text-[10px] font-black underline hover:text-profile-primary transition">
                        EDITAR CONFIGURAÇÕES
                    </a>
                </div>
            @endif

            {{-- Cover Image --}}
            <div class="relative h-64 w-full bg-zinc-200 dark:bg-zinc-800 md:h-96">
                @if($this->user->profile->cover_path)
                    <img src="{{ Storage::url($this->user->profile->cover_path) }}" class="h-full w-full object-cover" alt="Capa">
                @else
                    <div class="h-full w-full bg-profile-primary/10"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="relative -mt-32 z-10">
                    <div class="flex flex-col items-center md:flex-row md:items-end md:justify-between md:gap-8">

                        {{-- Avatar --}}
                        <div class="h-44 w-44 overflow-hidden rounded-[2.5rem] border-[6px] border-zinc-50 dark:border-zinc-950 bg-white dark:bg-zinc-900 shadow-2xl">
                            @if($this->user->profile->avatar_path)
                                <img src="{{ Storage::url($this->user->profile->avatar_path) }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-5xl font-bold text-zinc-300">
                                    {{ substr($this->user->profile->name ?? $this->user->profile->username, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="mt-8 flex items-center gap-3 md:mb-4">
                            <x-ui.share-profile :username="$this->user->profile->username" />

                            @if($this->user->profile->website_url)
                                <a href="{{ $this->user->profile->website_url }}" target="_blank" class="flex h-12 w-12 items-center justify-center rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-600 transition hover:text-profile-primary">
                                    <x-lucide-globe class="h-5 w-5" />
                                </a>
                            @endif

                            @if (!$this->isOwner)
                                <livewire:actions.follow-button :user="$this->user" :key="'follow-'.$this->user->id" />
                            @else
                                <a href="{{ route('dashboard.profile') }}" class="inline-flex h-12 px-6 items-center justify-center rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm font-bold text-zinc-900 shadow-sm transition hover:bg-zinc-50">
                                    <x-lucide-settings class="mr-2 h-4 w-4" />
                                    Configurar
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Info Grid --}}
                    <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-12">
                        <div class="lg:col-span-2">
                            <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white md:text-5xl">
                                {{ $this->user->profile->name ?? $this->user->profile->username }}
                            </h1>
                            <p class="mt-2 text-xl font-semibold text-profile-primary">
                                {{ $this->user->profile->handle ?? '@' . $this->user->profile->username }}
                            </p>

                            @if($this->user->profile->bio)
                                <p class="mt-6 text-lg leading-relaxed text-zinc-600 dark:text-zinc-400 max-w-3xl">
                                    {{ $this->user->profile->bio }}
                                </p>
                            @endif

                            <div class="mt-8 flex flex-wrap items-center gap-4 text-sm font-medium text-zinc-500">
                                <span class="flex items-center gap-2 bg-white dark:bg-zinc-900 px-4 py-2 rounded-xl border border-zinc-200 shadow-sm">
                                    <x-lucide-users class="h-4 w-4 text-profile-primary" />
                                    <strong class="text-zinc-900 dark:text-white">{{ $this->user->followers->count() }}</strong> seguidores
                                </span>
                            </div>
                        </div>

                        <div class="hidden lg:block">
                            <div class="rounded-3xl border border-zinc-200 bg-white dark:bg-zinc-900 p-6 shadow-sm sticky top-24">
                                <h3 class="font-bold text-zinc-900 dark:text-white mb-4">Sobre o autor</h3>
                                <p class="text-sm text-zinc-500 leading-relaxed">
                                    Membro desde {{ $this->user->created_at->translatedFormat('F Y') }}.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Posts Grid --}}
                <div class="mt-20 space-y-10">
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-white border-b border-zinc-200 pb-6">Publicações Recentes</h2>
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse($this->posts as $post)
                            <x-public.post-card :post="$post" />
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
