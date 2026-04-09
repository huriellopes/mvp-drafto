<div class="group relative overflow-hidden rounded-[3.5rem] border border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-1 shadow-sm backdrop-blur-sm transition-all duration-500 hover:shadow-xl">

    {{-- Glow Effect - Agora adaptativo e sutil --}}
    <div class="absolute -right-10 -top-10 h-32 w-32 bg-indigo-500/5 dark:bg-indigo-500/10 blur-[50px] group-hover:bg-indigo-500/20 transition-all duration-700"></div>

    <div class="relative p-10 space-y-8 text-center">
        {{-- Header --}}
        <div class="space-y-2">
            <h4 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tighter italic">Radar <span class="text-indigo-600 dark:text-indigo-400">Drafto.</span></h4>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 leading-relaxed italic">
                O melhor desta categoria no seu e-mail.
            </p>
        </div>

        {{-- Form --}}
        <form wire:submit.prevent="subscribe" class="space-y-4">
            <div class="relative">
                <input type="email"
                       wire:model="email"
                       placeholder="seu@email.com"
                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-100 dark:border-zinc-800 rounded-[1.5rem] px-6 py-4 text-sm text-zinc-900 dark:text-white placeholder:text-zinc-400 dark:placeholder:text-zinc-600 outline-none focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all">

                @error('email')
                <span class="text-[10px] font-bold text-red-500 dark:text-red-400 mt-2 block animate-in fade-in slide-in-from-top-1">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            {{-- Botão: Segue o padrão do Follow Button, mas com destaque --}}
            <x-ui.button
                type="submit"
                loading="subscribe"
                class="w-full "
            >
                Assinar Agora
            </x-ui.button>
        </form>

        {{-- Footer --}}
        <div class="pt-2 border-t border-zinc-50 dark:border-zinc-800/50">
            <p class="text-[9px] text-zinc-400 dark:text-zinc-600 font-bold uppercase tracking-tighter italic">
                Sem spam. Apenas conhecimento puro.
            </p>
        </div>
    </div>
</div>
