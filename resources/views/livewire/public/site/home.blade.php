@php
    $data = $data ?? null;
@endphp

<div class="space-y-32 pb-20 transition-colors duration-500 bg-white dark:bg-zinc-950 animate-in fade-in duration-1000">
    {{-- 1. HERO & MODERN SLIDER --}}
    <section class="relative pt-32 pb-44 overflow-hidden">
        <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-[800px] h-[800px] bg-indigo-500/5 dark:bg-indigo-500/10 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-[600px] h-[600px] bg-zinc-100/50 dark:bg-zinc-900/20 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 relative z-10 text-center mb-24">
            <h1 class="text-6xl md:text-8xl font-black text-zinc-900 dark:text-white tracking-tighter mb-8 leading-[0.85] animate-in fade-in slide-in-from-bottom-8 duration-1000">
                Escreva seu <br>
                <span class="relative inline-block italic text-indigo-600 dark:text-indigo-400">Legado.</span>
            </h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-lg md:text-xl max-w-2xl mx-auto font-medium leading-relaxed">
                A plataforma onde grandes ideias ganham vida. Conecte-se, inspire e seja lido por quem importa.
            </p>
        </div>

        @php
            $writersCount = count($data->featuredWriters ?? []);
        @endphp

        <div class="relative w-full" x-data="{
            next() { this.$refs.container.scrollBy({ left: 450, behavior: 'smooth' }) },
            prev() { this.$refs.container.scrollBy({ left: -450, behavior: 'smooth' }) }
        }">
            @if($writersCount > 1)
                <div class="absolute inset-y-0 left-0 w-32 bg-gradient-to-r from-white dark:from-zinc-950 to-transparent z-20 pointer-events-none hidden lg:block"></div>
                <div class="absolute inset-y-0 right-0 w-32 bg-gradient-to-l from-white dark:from-zinc-950 to-transparent z-20 pointer-events-none hidden lg:block"></div>
            @endif

            @if($writersCount > 1)
                <div class="max-w-7xl mx-auto px-4 relative">
                    <div class="flex items-center gap-3 absolute -top-12 right-4 z-30">
                        <button @click="prev" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                            <x-lucide-chevron-left class="h-5 w-5" />
                        </button>
                        <button @click="next" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                            <x-lucide-chevron-right class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            @endif

            <div x-ref="container" class="flex gap-8 overflow-x-auto scroll-smooth snap-x snap-mandatory px-4 lg:px-[calc((100vw-1280px)/2+1rem)] py-10 hide-scrollbar">
                @foreach($data->featuredWriters as $writer)
                    <x-public.writer-card 
                        :writer="$writer" 
                        class="snap-start transition-all duration-300 min-w-[320px] max-w-[380px]"
                    />
                @endforeach
            </div>
        </div>
    </section>

    {{-- 2. STATS CTA --}}
    <section class="max-w-6xl mx-auto px-4 -mt-24 relative z-30">
        <div class="bg-zinc-900 border border-white/5 rounded-[4rem] p-12 md:p-16 flex flex-col md:flex-row items-center justify-between gap-12 shadow-2xl overflow-hidden relative group">
            <div class="absolute inset-0 opacity-10 pointer-events-none bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:20px_20px]"></div>

            <div class="flex flex-wrap justify-center md:justify-start gap-12 md:gap-20 text-center md:text-left">
                <div class="space-y-1">
                    <p class="text-6xl font-black text-white tracking-tighter">{{ number_format($data->totalPosts) }}</p>
                    <p class="text-indigo-400 text-[10px] font-black uppercase tracking-[0.3em]">Manuscritos Publicados</p>
                </div>
                <div class="space-y-1 border-zinc-800 md:border-l md:pl-20">
                    <p class="text-6xl font-black text-white tracking-tighter">{{ number_format($data->totalUsers) }}</p>
                    <p class="text-indigo-400 text-[10px] font-black uppercase tracking-[0.3em]">Mentes Conectadas</p>
                </div>
            </div>

            <x-ui.button href="{{ route('register') }}" size="lg" class="relative z-10 px-12 py-7 !rounded-3xl bg-white text-zinc-900 hover:bg-indigo-500 hover:text-white transition-all duration-500 shadow-xl font-black uppercase text-xs tracking-widest border-none">
                Começar minha jornada
            </x-ui.button>
        </div>
    </section>

    {{-- 3. RECENT POSTS --}}
    <section class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col sm:flex-row items-end justify-between mb-20 px-4 gap-6">
            <div class="space-y-4 text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-black uppercase tracking-widest">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    Fresquinho na banca
                </div>
                <h2 class="text-5xl md:text-6xl font-black text-zinc-900 dark:text-zinc-100 tracking-tighter italic">Destaques <span class="text-indigo-600 dark:text-indigo-400">Recentes.</span></h2>
            </div>
            <a href="{{ route('posts.explore') }}" wire:navigate class="group flex items-center gap-3 text-xs font-black uppercase tracking-widest text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all">
                Ver todo o acervo
                <div class="h-10 w-10 flex items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-800 group-hover:border-zinc-900 dark:group-hover:border-white transition-colors">
                    <x-lucide-chevron-right class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            @foreach($data->posts as $post)
                <x-public.post-card :post="$post" />
            @endforeach
        </div>
    </section>

    {{-- 4. EXPLORE BY CATEGORY --}}
    <section class="max-w-7xl mx-auto px-4 pb-20">
        <div class="bg-zinc-50 dark:bg-zinc-900/30 rounded-[4rem] p-12 md:p-20 border border-zinc-100 dark:border-zinc-800/50">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-4xl md:text-5xl font-black text-zinc-900 dark:text-zinc-100 tracking-tighter">Navegue por <span class="italic text-indigo-600 dark:text-indigo-400">Interesses</span></h2>
                <p class="text-zinc-500 dark:text-zinc-400 text-sm font-medium max-w-lg mx-auto leading-relaxed">
                    De tecnologia a poesia, encontre o nicho perfeito para sua curiosidade.
                </p>
            </div>

            <div class="flex flex-wrap justify-center gap-4">
                @foreach($data->categories as $category)
                    <a href="{{ route('posts.explore', ['category' => $category->slug]) }}"
                       wire:navigate
                       class="group relative flex items-center gap-4 px-10 py-5 rounded-[2rem] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-all hover:border-indigo-500 hover:shadow-xl hover:shadow-indigo-500/10 hover:-translate-y-1.5">
                        <div class="h-2.5 w-2.5 rounded-full bg-indigo-500 group-hover:scale-125 transition-transform duration-500"></div>
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-zinc-900 dark:text-white">{{ $category->name }}</span>
                            <span class="text-[9px] font-bold text-zinc-400 uppercase tracking-widest italic">{{ $category->posts_count }} Publicações</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</div>
