@if(auth()->user()->onTrial())
    <div class="relative overflow-hidden rounded-[2.5rem] bg-indigo-600 p-6 shadow-xl shadow-indigo-500/20 animate-in fade-in slide-in-from-top duration-700">
        {{-- Background Decoration --}}
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-indigo-400/20 blur-3xl"></div>

        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-3xl bg-white/20 backdrop-blur-md border border-white/30 text-white shadow-inner">
                    <x-lucide-rocket class="h-7 w-7 animate-bounce" />
                </div>
                <div>
                    <h3 class="text-xl font-black italic tracking-tighter text-white">
                        Você está no <span class="text-indigo-200 uppercase">Modo Pro</span> (Degustação)
                    </h3>
                    <p class="text-sm font-medium text-indigo-100/80">
                        Aproveite 15 dias de recursos ilimitados. Restam <span class="text-white font-bold">{{ (int) now()->diffInDays(auth()->user()->trial_ends_at) }} dias</span> para expirar.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-ui.button 
                    href="{{ route('dashboard.billing.plans') }}"
                    class="!bg-white !text-indigo-600 hover:!bg-indigo-50 !rounded-2xl font-black uppercase text-[10px] tracking-widest px-8 py-3"
                >
                    Assinar Agora
                </x-ui.button>
            </div>
        </div>
    </div>
@endif
