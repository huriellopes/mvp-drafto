<div class="space-y-32 pb-20 bg-white dark:bg-zinc-950 animate-pulse">
    {{-- 1. HERO ESTÁTICO (Renderiza quase igual ao real para evitar shift) --}}
    <section class="relative pt-32 pb-44 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 text-center mb-24">
            <h1 class="text-6xl md:text-8xl font-black text-zinc-100 dark:text-zinc-900 tracking-tighter mb-8 leading-[0.85]">
                Escreva seu <br> Legado.
            </h1>
            <div class="h-6 w-2/3 mx-auto bg-zinc-50 dark:bg-zinc-900 rounded-full"></div>
        </div>

        {{-- Slider Skeleton --}}
        <div class="flex gap-8 overflow-hidden px-4 lg:px-[calc((100vw-1280px)/2+1rem)]">
            @for($i = 0; $i < 4; $i++)
                <x-public.writer-card-skeleton />
            @endfor
        </div>
    </section>

    {{-- 2. STATS CTA SKELETON --}}
    <section class="max-w-6xl mx-auto px-4 -mt-24 relative z-30">
        <div class="bg-zinc-100 dark:bg-zinc-900 h-48 rounded-[4rem] border border-zinc-200 dark:border-zinc-800"></div>
    </section>

    {{-- 3. RECENT POSTS SKELETON --}}
    <section class="max-w-7xl mx-auto px-4">
        <div class="h-20 w-1/3 bg-zinc-100 dark:bg-zinc-900 rounded-3xl mb-20"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            @for($i = 0; $i < 3; $i++)
                <x-public.post-card-skeleton />
            @endfor
        </div>
    </section>
</div>
