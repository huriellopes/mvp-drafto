<div class="space-y-32 pb-20 transition-colors duration-500">
    {{-- 1. HERO & MANUAL SLIDER --}}
    {{-- Removido bg-zinc-950 fixo, agora alterna entre cinza muito claro e preto profundo --}}
    <section class="relative bg-zinc-50 dark:bg-zinc-950 pt-32 pb-44 overflow-hidden selection:bg-profile-primary/30 transition-colors duration-500">

        {{-- Gradiente adaptativo: sutil no light, profundo no dark --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-profile-primary/10 via-transparent to-transparent dark:from-zinc-800/10"></div>

        <div class="max-w-7xl mx-auto px-4 relative z-10 text-center mb-16">
            {{-- Texto alterna entre zinc-900 (light) e white (dark) --}}
            <h1 class="text-6xl md:text-8xl font-black text-zinc-900 dark:text-white tracking-tighter mb-8 leading-[0.9] animate-in fade-in slide-in-from-bottom-8 duration-1000">
                Escreva seu <br><span class="text-profile-primary drop-shadow-[0_0_15px_rgba(var(--profile-primary-rgb),0.3)]">Legado.</span>
            </h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-xl max-w-2xl mx-auto font-medium transition-colors">
                Conectamos mentes brilhantes através da escrita.
            </p>
        </div>

        {{-- Slider Manual --}}
        <div class="relative max-w-7xl mx-auto px-4" x-data="{
            prev() { this.$refs.executor.scrollBy({ left: -400, behavior: 'smooth' }) },
            next() { this.$refs.executor.scrollBy({ left: 400, behavior: 'smooth' }) }
        }">
            {{-- Controles Sênior adaptativos --}}
            <div class="flex items-center justify-between absolute top-1/2 -translate-y-1/2 w-full left-0 z-30 px-4 pointer-events-none">
                <button @click="prev" class="p-4 rounded-2xl bg-white/20 dark:bg-white/5 backdrop-blur-xl text-zinc-900 dark:text-white border border-zinc-200 dark:border-white/10 pointer-events-auto hover:bg-profile-primary hover:text-white hover:scale-110 transition-all duration-300 shadow-2xl">
                    <x-lucide-chevron-left class="h-6 w-6" />
                </button>
                <button @click="next" class="p-4 rounded-2xl bg-white/20 dark:bg-white/5 backdrop-blur-xl text-zinc-900 dark:text-white border border-zinc-200 dark:border-white/10 pointer-events-auto hover:bg-profile-primary hover:text-white hover:scale-110 transition-all duration-300 shadow-2xl">
                    <x-lucide-chevron-right class="h-6 w-6" />
                </button>
            </div>

            <div x-ref="executor" class="flex gap-8 overflow-x-hidden scroll-smooth snap-x snap-mandatory pb-8 hide-scrollbar">
                @foreach($data->featuredWriters as $writer)
                    {{-- Card adaptativo: bg-white no light, bg-white/0.03 no dark --}}
                    <div class="snap-center shrink-0 w-72 bg-white dark:bg-white/[0.03] border border-zinc-200 dark:border-white/10 p-10 rounded-[3.5rem] backdrop-blur-sm group transition-all duration-500 hover:shadow-2xl hover:border-profile-primary/30 dark:hover:bg-white/[0.08] dark:hover:border-white/20 hover:-translate-y-4">
                        <div class="relative h-28 w-28 mx-auto mb-8">
                            <div class="absolute inset-0 bg-profile-primary/20 blur-xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="relative h-full w-full rounded-[2.5rem] overflow-hidden ring-4 ring-zinc-100 dark:ring-white/5 group-hover:ring-profile-primary transition-all duration-500 shadow-inner">
                                <img src="{{ $writer->profile->avatar_path ? Storage::url($writer->profile->avatar_path) : 'https://ui-avatars.com/api/?name='.$writer->name }}" class="h-full w-full object-cover">
                            </div>
                        </div>
                        {{-- Nome do escritor: zinc-900 -> white --}}
                        <p class="text-zinc-900 dark:text-white font-black text-center text-xl truncate tracking-tight group-hover:text-profile-primary transition-colors">
                            {{ $writer->name }}
                        </p>
                        <a href="{{ route('profile.show', $writer->profile->username) }}" class="block text-zinc-400 dark:text-zinc-500 text-xs font-bold text-center mt-3 uppercase tracking-widest hover:text-profile-primary transition-colors">
                            @ {{$writer->profile->username}}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 2. CTA STATS - Refatorado para visibilidade total --}}
    <section class="max-w-5xl mx-auto px-4 -mt-32 relative z-20">
        {{-- Fundo alterna entre zinc-900 e zinc-950 no dark, no light mantemos escuro para destaque ou ajustamos --}}
        <div class="bg-zinc-900 border border-zinc-800 dark:bg-zinc-950 dark:border-zinc-800/50 rounded-[3.5rem] p-12 flex flex-col md:flex-row items-center justify-between gap-12 shadow-[0_50px_100px_-20px_rgba(0,0,0,0.4)]">
            <div class="flex gap-16">
                <div class="text-center md:text-left group">
                    <p class="text-6xl font-black text-white leading-none transition-transform group-hover:scale-110">{{ $data->totalPosts }}</p>
                    <p class="text-profile-primary text-[10px] font-black uppercase tracking-[0.2em] mt-3">Posts Criados</p>
                </div>
                <div class="h-16 w-px bg-zinc-800 hidden md:block"></div>
                <div class="text-center md:text-left group">
                    <p class="text-6xl font-black text-white leading-none transition-transform group-hover:scale-110">{{ $data->totalUsers }}</p>
                    <p class="text-profile-primary text-[10px] font-black uppercase tracking-[0.2em] mt-3">Escritores</p>
                </div>
            </div>
            <x-ui.button href="{{ route('register') }}" size="lg" class="px-10 py-6 text-lg hover:shadow-[0_0_30px_rgba(var(--profile-primary-rgb),0.4)] transition-all">
                Criar meu perfil
            </x-ui.button>
        </div>
    </section>

    {{-- 3. RECENT POSTS --}}
    <section class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-16 px-4">
            <div class="space-y-3">
                <h2 class="text-5xl font-black text-zinc-900 dark:text-zinc-100 tracking-tighter italic transition-colors">Destaques Recentes</h2>
                <div class="h-1.5 w-20 bg-profile-primary rounded-full"></div>
            </div>
            <a href="{{ route('posts.explore') }}" class="group flex items-center gap-2 text-sm font-bold text-zinc-500 hover:text-profile-primary dark:text-zinc-400 dark:hover:text-white transition-all">
                Explorar acervo completo
                <x-lucide-arrow-right class="h-4 w-4 group-hover:translate-x-2 transition-transform" />
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            @foreach($data->posts as $post)
                <x-public.post-card :post="$post" />
            @endforeach
        </div>
    </section>
</div>
