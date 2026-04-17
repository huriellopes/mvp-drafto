<div class="max-w-7xl mx-auto px-4 py-16 lg:py-24">
    <div class="flex flex-col lg:flex-row gap-16">

        {{-- Sidebar de Filtros --}}
        <aside class="w-full lg:w-72">
            <div class="sticky top-32 space-y-12">

                {{-- Busca --}}
                <div class="space-y-5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 italic">Pesquisar Obra</h4>
                    <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Buscar título..." class="rounded-2xl border-zinc-200 dark:border-zinc-800">
                        <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
                    </x-ui.input>
                </div>

                {{-- NOVO: Filtro por Formato (Post vs Artigo) --}}
                <div class="space-y-5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 italic">Formato</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="$set('type', 'article')" @class([
                            'flex flex-col items-center gap-2 p-3 cursor-pointer rounded-2xl border transition-all text-[10px] font-black uppercase tracking-tighter',
                            $type === 'article' ? 'bg-emerald-50 border-emerald-200 text-emerald-600 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : 'bg-white border-zinc-100 text-zinc-400 dark:bg-zinc-900 dark:border-zinc-800'
                        ])>
                            <x-lucide-scroll class="h-4 w-4" /> Artigos
                        </button>
                        <button wire:click="$set('type', 'post')" @class([
                            'flex flex-col items-center gap-2 p-3 cursor-pointer rounded-2xl border transition-all text-[10px] font-black uppercase tracking-tighter',
                            $type === 'post' ? 'bg-blue-50 border-blue-200 text-blue-600 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400' : 'bg-white border-zinc-100 text-zinc-400 dark:bg-zinc-900 dark:border-zinc-800'
                        ])>
                            <x-lucide-file-text class="h-4 w-4" /> Posts
                        </button>
                    </div>
                </div>

                {{-- Categorias --}}
                <div class="space-y-5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 italic">Categorias</h4>
                    <div class="flex flex-wrap lg:flex-col gap-1.5">
                        <button wire:click="$set('category', '')" @class([
                            'flex items-center justify-between px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all',
                            $category === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-900'
                        ])>Geral</button>

                        @foreach($this->categories as $cat)
                            <button wire:click="$set('category', '{{ $cat->slug }}')" @class([
                                'flex items-center justify-between px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all text-left',
                                $category === $cat->slug ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/20' : 'text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-900'
                            ])>
                                <span class="truncate pr-2">{{ $cat->name }}</span>
                                <span class="text-[9px] font-bold opacity-40">{{ $cat->posts_count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Tags --}}
                <div class="space-y-5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 italic">Tags</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->tags as $t)
                            <button wire:click="$set('tag', '{{ $t->slug }}')" @class([
                                'px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-tighter border transition-all',
                                $tag === $t->slug ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-transparent border-zinc-200 text-zinc-400 dark:border-zinc-800'
                            ])>#{{ $t->name }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

        {{-- Grid de Conteúdo --}}
        <div class="flex-1 space-y-12">
            {{-- Header da Listagem --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-zinc-100 dark:border-zinc-800/50 pb-8 gap-6">
                <div>
                    <h2 class="text-5xl font-black text-zinc-900 dark:text-white tracking-tighter italic">Biblioteca.</h2>
                    <div class="mt-2 flex items-center gap-3">
                        <div class="h-1.5 w-12 bg-indigo-500 rounded-full"></div>
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
                            {{ $posts->total() }} {{ str('Obra')->plural($posts->total()) }} encontradas
                        </span>
                    </div>
                </div>

                {{-- Ordenação --}}
                <div class="flex items-center gap-3 bg-zinc-50 dark:bg-zinc-900 p-1.5 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                    <button wire:click="$set('sort', 'latest')" @class(['px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all', $sort === 'latest' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-400 hover:text-zinc-600'])>Recentes</button>
                    <button wire:click="$set('sort', 'popular')" @class(['px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all', $sort === 'popular' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-400 hover:text-zinc-600'])>Populares</button>
                </div>
            </div>

            {{-- Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 min-h-[800px] transition-all duration-500" 
                 wire:loading.class="opacity-40 grayscale pointer-events-none"
            >
                @forelse($posts as $post)
                    <x-public.post-card :$post wire:key="post-{{ $post->id }}" />
                @empty
                    <div class="col-span-full py-40 text-center rounded-[4rem] border-2 border-dashed border-zinc-100 dark:border-zinc-900">
                        <x-lucide-wind class="h-10 w-10 text-zinc-200 mx-auto mb-6" />
                        <p class="text-zinc-900 dark:text-white font-black italic text-xl tracking-tight">Nada por aqui...</p>
                        <button wire:click="resetFilters" class="mt-8 px-8 py-4 rounded-2xl bg-zinc-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all">Limpar Filtros</button>
                    </div>
                @endforelse
            </div>

            <div class="pt-12">{{ $posts->links() }}</div>
        </div>
    </div>
</div>
