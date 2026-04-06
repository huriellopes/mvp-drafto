<div class="max-w-7xl mx-auto px-4 py-16 lg:py-24">
    <div class="flex flex-col lg:flex-row gap-16">

        {{-- Sidebar de Filtros - High Contrast --}}
        <aside class="w-full lg:w-72">
            <div class="sticky top-32 space-y-12">
                {{-- Busca --}}
                <div class="space-y-5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">Pesquisar Obra</h4>
                    <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Buscar título..." class="rounded-2xl border-zinc-200 dark:border-zinc-800 focus:ring-profile-primary" />
                </div>

                {{-- Categorias --}}
                <div class="space-y-5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">Categorias</h4>
                    <div class="flex flex-wrap lg:flex-col gap-1.5">
                        <button wire:click="$set('category', '')"
                            @class(['text-left px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all duration-300',
                                    $category === '' ? 'bg-zinc-900 text-white shadow-xl dark:bg-white dark:text-zinc-900' : 'text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-900'])>
                            Geral
                        </button>
                        @foreach($this->categories as $cat)
                            <button wire:click="$set('category', '{{ $cat->slug }}')"
                                @class(['text-left px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all duration-300',
                                        $category === $cat->slug ? 'bg-zinc-900 text-white shadow-xl dark:bg-white dark:text-zinc-900' : 'text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-900'])>
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Tags --}}
                <div class="space-y-5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400">Tags</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->tags as $t)
                            <button wire:click="$set('tag', '{{ $t->slug }}')"
                                @class(['px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-tighter border transition-all',
                                        $tag === $t->slug ? 'bg-profile-primary border-profile-primary text-white' : 'bg-transparent border-zinc-200 text-zinc-400 hover:border-zinc-900 dark:border-zinc-800 dark:hover:border-zinc-400'])>
                                #{{ $t->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

        {{-- Grid --}}
        <div class="flex-1 space-y-12">
            <div class="flex items-baseline justify-between border-b border-zinc-100 dark:border-zinc-800/50 pb-8">
                <h2 class="text-5xl font-black text-zinc-900 dark:text-white tracking-tighter">Artigos</h2>
                <div class="px-4 py-1 rounded-full bg-zinc-100 dark:bg-zinc-900 text-[10px] font-bold text-zinc-500 uppercase tracking-widest">
                    {{ $posts->total() }} Obras
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12" wire:loading.class="opacity-30 blur-sm transition-all duration-500">
                @forelse($posts as $post)
                    <x-public.post-card :post="$post" />
                @empty
                    <div class="col-span-full py-40 text-center rounded-[4rem] border-2 border-dashed border-zinc-100 dark:border-zinc-900">
                        <x-lucide-wind class="mx-auto h-12 w-12 text-zinc-200 mb-6" />
                        <p class="text-zinc-400 font-bold italic text-lg">O silêncio ecoa por aqui...</p>
                        <button wire:click="$set('search', ''); $set('category', ''); $set('tag', '');" class="mt-6 text-profile-primary font-black uppercase text-xs tracking-widest hover:underline">Resetar Filtros</button>
                    </div>
                @endforelse
            </div>

            <div class="pt-12">{{ $posts->links() }}</div>
        </div>
    </div>
</div>
