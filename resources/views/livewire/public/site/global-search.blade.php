<div x-data="{
        isOpen: @entangle('isOpen'),
        init() {
            // Atalhos de teclado
            window.addEventListener('keydown', e => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    this.isOpen = !this.isOpen;
                }
                if (e.key === 'Escape') this.isOpen = false;
            });

            // Lógica Sênior: Watcher para garantir foco toda vez que abrir
            this.$watch('isOpen', value => {
                if (value) {
                    // Pequeno timeout para garantir que o elemento esteja visível no DOM
                    setTimeout(() => this.$refs.searchInput.focus(), 100);
                }
            });
        }
    }">

    {{-- Trigger na Navbar --}}
    <button @click="isOpen = true"
            type="button"
            aria-label="Pesquisar..."
            class="flex items-center justify-center lg:justify-start gap-3 h-10 w-10 lg:w-auto lg:px-4 lg:py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all border border-transparent hover:border-zinc-200 dark:hover:border-zinc-800 group"
    >
        <x-lucide-search class="h-5 w-5 lg:h-4 lg:w-4 group-hover:scale-110 transition-transform" aria-hidden="true" />
        <span class="hidden lg:inline text-sm font-bold">Pesquisar...</span>
    </button>

    {{-- Modal Overlay --}}
    <div x-show="isOpen"
         x-transition.opacity
         class="fixed inset-0 z-[110] bg-zinc-950/40 backdrop-blur-sm p-4 md:p-20"
         style="display: none;">

        <div @click.away="isOpen = false"
             role="dialog"
             aria-modal="true"
             aria-label="Busca"
             class="mx-auto max-w-2xl transform overflow-hidden rounded-[2.5rem] bg-white dark:bg-zinc-900 shadow-2xl ring-1 ring-black/5 transition-all">

            {{-- Header de Busca com Loading --}}
            <div class="relative flex items-center p-6 border-b border-zinc-100 dark:border-zinc-800">
                <div class="relative flex items-center justify-center">
                    <x-lucide-search wire:loading.remove wire:target="search" class="h-6 w-6 text-zinc-400" />
                    <svg wire:loading wire:target="search" class="animate-spin h-6 w-6 text-profile-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <input type="text"
                       x-ref="searchInput"
                       wire:model.live.debounce.400ms="search"
                       aria-label="Buscar por título, tag ou categoria"
                       class="ml-4 flex-1 bg-transparent border-none text-lg text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-0 outline-none"
                       placeholder="Buscar por título, tag ou categoria..."
                       x-init="$el.focus()">

                <x-ui.tooltip text="Fechar">
                    <button @click="isOpen = false" type="button" aria-label="Fechar" class="p-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors">
                        <x-lucide-x class="h-5 w-5" aria-hidden="true" />
                    </button>
                </x-ui.tooltip>
            </div>

            {{-- Resultados --}}
            <div class="max-h-[60vh] overflow-y-auto p-4 scrollbar-hide" wire:loading.class="opacity-50 grayscale transition-all duration-500" wire:target="search">
                @if(strlen($search) < 2)
                    <div class="py-12 text-center">
                        <x-lucide-sparkles class="mx-auto h-12 w-12 text-zinc-200 dark:text-zinc-800 animate-pulse" />
                        <p class="mt-4 text-sm font-medium text-zinc-500 uppercase tracking-widest">Comece a digitar para descobrir universos.</p>
                    </div>
                @else
                    {{-- Escritores --}}
                    @if(count($authors) > 0)
                        <div class="mb-8">
                            <h3 class="px-4 text-[10px] font-black uppercase tracking-[0.3em] text-zinc-400 mb-4">Mestres da Escrita</h3>
                            <div class="space-y-1">
                                @foreach($authors as $author)
                                    <a href="{{ route('profile.show', $author->profile->username) }}" @click="isOpen = false" wire:navigate class="flex items-center gap-4 p-4 rounded-3xl hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-all group">
                                        <div class="relative">
                                            <x-ui.avatar
                                                :src="$author->profile->avatar_path ? Storage::url($author->profile->avatar_path) : null"
                                                :name="$author->display_name"
                                                size="lg"
                                                class="grayscale group-hover:grayscale-0 transition-all duration-500"
                                            />
                                            <div class="absolute -bottom-1 -right-1 h-3 w-3 rounded-full border-2 border-white dark:border-zinc-900 bg-emerald-500"></div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $author->display_name }}</p>
                                            <p class="text-[10px] font-medium text-zinc-500 italic truncate">@<span></span>{{ $author->profile->username }}</p>
                                        </div>
                                        <x-lucide-chevron-right class="h-4 w-4 text-zinc-300 group-hover:text-profile-primary transition-all" />
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Artigos --}}
                    @if(count($posts) > 0)
                        <div>
                            <h3 class="px-4 text-[10px] font-black uppercase tracking-[0.3em] text-zinc-400 mb-4">Resultados da Biblioteca</h3>
                            <div class="space-y-2">
                                @foreach($posts as $post)
                                    <a href="{{ route('posts.show', $post->slug) }}" @click="isOpen = false" wire:navigate class="flex items-center gap-4 p-4 rounded-3xl hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-all group">
                                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-zinc-100 dark:bg-zinc-800">
                                            @if($post->cover_image_url)
                                                <img src="{{ $post->cover_image_url }}" width="56" height="56" alt="{{ $post->title }}" class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <x-lucide-file-text class="h-5 w-5 text-zinc-300" />
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="flex-1 min-w-0">
                                                    <p @class([
                                                        'text-sm font-bold transition-colors',
                                                        'text-zinc-900 dark:text-white group-hover:text-profile-primary' => str_contains(strtolower($post->title), strtolower($search)),
                                                        'text-zinc-700 dark:text-zinc-300' => !str_contains(strtolower($post->title), strtolower($search))
                                                    ])>
                                                        {{ $post->title }}
                                                    </p>
                                                    <p class="text-[10px] text-zinc-500 mt-0.5 line-clamp-1 italic font-medium uppercase tracking-widest">Por {{ $post->author->name }}</p>
                                                </div>

                                                <span @class([
                                                    'shrink-0 text-[8px] font-black px-2 py-0.5 rounded-lg uppercase tracking-widest border transition-colors',
                                                    'bg-profile-primary/10 border-profile-primary text-profile-primary' => str_contains(strtolower($post->category->name), strtolower($search)),
                                                    'bg-zinc-100 dark:bg-zinc-800 border-transparent text-zinc-400' => !str_contains(strtolower($post->category->name), strtolower($search))
                                                ])>
                                                    {{ $post->category->name }}
                                                </span>
                                            </div>

                                            {{-- Exibição de Tags se houver match --}}
                                            @php
                                                $matchedTags = $post->tags->filter(fn($tag) => str_contains(strtolower($tag->name), strtolower($search)));
                                            @endphp

                                            @if($matchedTags->isNotEmpty())
                                                <div class="flex flex-wrap gap-1 mt-1.5">
                                                    @foreach($matchedTags as $tag)
                                                        <span class="inline-flex items-center gap-1 text-[8px] font-bold text-emerald-600 dark:text-emerald-400">
                                                            #{{ $tag->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @if(count($authors) === 0)
                            <div class="py-12">
                                <x-ui.empty-state title="Nenhum manuscrito" description="Tente outros termos ou navegue pelos escritores mais populares." />
                            </div>
                        @endif
                    @endif
                @endif
            </div>

            {{-- Dica de Atalho --}}
            <div class="p-4 bg-zinc-50/50 dark:bg-zinc-950/20 border-t border-zinc-100 dark:border-zinc-800 flex justify-center">
                <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
                    Pressione <span class="text-zinc-900 dark:text-white px-1">ESC</span> para fechar
                </p>
            </div>
        </div>
    </div>
</div>
