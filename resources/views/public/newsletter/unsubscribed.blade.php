<x-layouts.guest title="Inscrição Cancelada">
    <div class="flex min-h-[60vh] flex-col items-center justify-center px-4 text-center">
        <div class="inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 mb-8 animate-in fade-in zoom-in duration-500">
            <x-lucide-mail-x class="h-10 w-10" />
        </div>

        <h1 class="text-4xl font-black text-zinc-900 dark:text-white tracking-tighter italic">Inscrição <span class="text-red-600">Cancelada.</span></h1>
        <p class="mt-4 text-zinc-500 font-medium max-w-sm leading-relaxed">
            Seu e-mail foi removido da nossa lista. Sentiremos sua falta, mas você pode voltar quando quiser!
        </p>

        <div class="mt-10">
            <a href="/" class="px-8 py-4 rounded-2xl bg-zinc-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl">
                Voltar ao Início
            </a>
        </div>
    </div>
</x-layouts.guest>
