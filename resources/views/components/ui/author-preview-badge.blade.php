<div
    x-data="{ show: true }"
    x-show="show"
    class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[99] w-[calc(100%-2rem)] max-w-lg"
>
    <div class="bg-zinc-900/90 dark:bg-white/90 text-white dark:text-zinc-900 rounded-[2.5rem] p-2 pl-7 shadow-2xl ring-1 ring-white/20 dark:ring-zinc-900/10 backdrop-blur-2xl flex items-center justify-between gap-6 animate-in fade-in slide-in-from-bottom-10 duration-700">
        <div class="flex items-center gap-4 py-2">
            <div class="relative">
                <div class="h-10 w-10 rounded-full bg-indigo-500/20 dark:bg-indigo-500/10 flex items-center justify-center">
                    <x-lucide-user-check class="h-5 w-5 text-indigo-500" />
                </div>
                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                </span>
            </div>

            <div class="flex flex-col min-w-0 text-left">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-500 leading-none mb-1">Preview do Autor</span>
                <h4 class="text-xs font-bold truncate tracking-tight">Você está vendo sua própria estante</h4>
            </div>
        </div>

        <a href="{{ route('dashboard.profile') }}"
           wire:navigate
           class="group relative flex h-12 items-center justify-center overflow-hidden rounded-[1.8rem] bg-indigo-600 px-6 sm:px-8 text-[10px] font-black uppercase tracking-widest text-white transition-all hover:bg-indigo-700 active:scale-95 shadow-lg shadow-indigo-600/20"
        >
            <div class="relative z-10 flex items-center gap-2">
                <x-lucide-pencil class="h-3.5 w-3.5" />
                <span class="hidden sm:inline">Editar Perfil</span>
                <span class="inline sm:hidden">Editar</span>
            </div>
        </a>
    </div>
</div>
