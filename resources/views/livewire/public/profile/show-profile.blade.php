<div
    style="--profile-primary: {{ $this->user->profile->primary_color }}; --profile-accent: {{ $this->user->profile->accent_color }};"
    class="min-h-screen"
>
    {{-- Header / Cover --}}
    <div class="relative h-64 w-full bg-zinc-200 md:h-96">
        @if($this->user->profile->cover_path)
            <img src="{{ Storage::url($this->user->profile->cover_path) }}" class="h-full w-full object-cover">
        @else
            <div class="h-full w-full bg-[var(--profile-primary)] opacity-20"></div>
        @endif

        {{-- Overlay Gradiente para legibilidade --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
    </div>

    {{-- Profile Info Container --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative -mt-32 z-10">
            <div class="flex flex-col items-center md:flex-row md:items-end md:justify-between md:gap-8">

                {{-- Avatar --}}
                <div class="relative group">
                    <div class="h-44 w-44 overflow-hidden rounded-[2.5rem] border-[6px] border-zinc-50 bg-white shadow-2xl transition duration-300 group-hover:scale-[1.02]">
                        @if($this->user->profile->avatar_path)
                            <img src="{{ Storage::url($this->user->profile->avatar_path) }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center bg-zinc-100 text-5xl font-bold text-zinc-300">
                                {{ substr($this->user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Ações --}}
                <div class="mt-8 flex items-center gap-3 md:mb-4">
                    @if($this->user->profile->website_url)
                        <a href="{{ $this->user->profile->website_url }}" target="_blank" class="flex h-12 w-12 items-center justify-center rounded-2xl border border-zinc-200 bg-white text-zinc-600 shadow-sm transition hover:bg-zinc-50 hover:text-zinc-900">
                            <x-lucide-globe class="h-5 w-5" />
                        </a>
                    @endif

                    @auth
                        @if(auth()->id() !== $this->user->id)
                            <button class="inline-flex h-12 items-center justify-center rounded-2xl bg-zinc-900 px-8 text-sm font-bold text-white shadow-lg transition hover:bg-zinc-800 active:scale-95">
                                Seguir
                            </button>
                        @else
                            <a href="{{ route('dashboard.profile') }}" class="inline-flex h-12 items-center justify-center rounded-2xl bg-white px-6 text-sm font-bold text-zinc-900 shadow-sm border border-zinc-200 hover:bg-zinc-50 transition">
                                <x-lucide-settings class="mr-2 h-4 w-4" />
                                Configurações
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            {{-- Nome e Bio --}}
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2">
                    <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 md:text-5xl">
                        {{ $this->user->name }}
                    </h1>
                    <p class="mt-2 text-xl font-semibold text-[var(--profile-primary)]">
                        {{ $this->user->profile->handle }}
                    </p>

                    @if($this->user->profile->bio)
                        <p class="mt-6 text-lg leading-relaxed text-zinc-600 max-w-3xl">
                            {{ $this->user->profile->bio }}
                        </p>
                    @endif

                    {{-- Metadados --}}
                    <div class="mt-8 flex flex-wrap items-center gap-6 text-sm font-medium text-zinc-500">
                        <span class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-zinc-200 shadow-sm">
                            <x-lucide-users class="h-4 w-4 text-zinc-400" />
                            <strong class="text-zinc-900">{{ $this->user->followers->count() }}</strong> seguidores
                        </span>

                        @if($this->user->profile->location)
                            <span class="flex items-center gap-2">
                                <x-lucide-map-pin class="h-4 w-4 text-zinc-400" />
                                {{ $this->user->profile->location }}
                            </span>
                        @endif

                        @if($this->user->profile->show_email_publicly)
                            <span class="flex items-center gap-2">
                                <x-lucide-mail class="h-4 w-4 text-zinc-400" />
                                {{ $this->user->email }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Sidebar lateral do perfil (Espaço para badges, patrocinadores ou widgets futuros) --}}
                <div class="hidden lg:block">
                    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-zinc-900 mb-4">Sobre o autor</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed">
                            Membro desde {{ $this->user->created_at->translatedFormat('F Y') }}.
                            Já publicou {{ $this->user->posts()->published()->count() }} conteúdos na Drafto.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seção de Posts --}}
        <div class="mt-20 space-y-10">
            <div class="flex items-center justify-between border-b border-zinc-200 pb-6">
                <h2 class="text-2xl font-bold text-zinc-900">Publicações Recentes</h2>
            </div>

            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($this->posts as $post)
                    <x-public.post-card :post="$post" />
                @empty
                    <div class="col-span-full py-20 text-center rounded-3xl border-2 border-dashed border-zinc-200">
                        <x-lucide-ghost class="mx-auto h-12 w-12 text-zinc-300" />
                        <p class="mt-4 text-lg text-zinc-500 font-medium">Este autor ainda está preparando seu primeiro grande conteúdo.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-12">
                {{ $this->posts->links() }}
            </div>
        </div>
    </div>
</div>
