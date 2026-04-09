<div class="space-y-12 pb-20">
    <header class="text-center space-y-4">
        <h2 class="text-5xl font-black tracking-tighter italic text-zinc-900 dark:text-white">
            Evolua sua <span class="text-indigo-600">Escrita.</span>
        </h2>
        <p class="text-zinc-500 font-medium max-w-2xl mx-auto">
            Escolha o plano ideal para o seu momento como escritor e desbloqueie ferramentas profissionais.
        </p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($plans as $plan)
            {{-- Importante: wire:key para isolar cada card no DOM do Livewire --}}
            <div wire:key="plan-card-{{ $plan->id }}" @class([
                'relative flex flex-col p-8 rounded-[3.5rem] border transition-all duration-500',
                'bg-zinc-950 text-white border-white/10 scale-105 shadow-2xl z-10' => $plan->slug === 'pro',
                'bg-white dark:bg-zinc-900 border-zinc-100 dark:border-zinc-800' => $plan->slug !== 'pro',
            ])>
                @if($plan->slug === 'pro')
                    <span class="absolute -top-4 left-1/2 -translate-x-1/2 bg-indigo-600 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Recomendado</span>
                @endif

                <div class="mb-8">
                    <h3 class="text-xl font-black italic">{{ $plan->name }}</h3>
                    <div class="mt-4 flex items-baseline gap-1">
                        <span class="text-4xl font-black tracking-tighter">R$ {{ number_format($plan->price, 2, ',', '.') }}</span>
                        <span class="text-xs font-bold opacity-50">/mês</span>
                    </div>
                </div>

                <ul class="space-y-4 mb-10 flex-1">
                    @foreach($plan->features ?? [] as $feature)
                        <li class="flex items-center gap-3 text-sm font-medium">
                            <x-lucide-check-circle-2 class="h-4 w-4 text-indigo-500" />
                            <span class="opacity-80">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                @if(auth()->user()->subscribed($plan->slug))
                    <x-ui.button disabled class="w-full !bg-zinc-800 !text-zinc-400 !cursor-default !rounded-2xl">
                        Plano Atual
                    </x-ui.button>
                @else
                    <x-ui.button
                        wire:click="checkout('{{ $plan->slug }}')"
                        {{-- Sênior: wire:target isola o loading apenas para o botão clicado --}}
                        wire:loading.attr="disabled"
                        wire:target="checkout('{{ $plan->slug }}')"
                        @class([
                            'w-full !rounded-2xl py-4 font-black uppercase text-[10px] tracking-widest transition-all active:scale-95',
                            '!bg-white !text-zinc-950 hover:!bg-zinc-100' => $plan->slug === 'pro',
                            '!bg-zinc-900 !text-white dark:!bg-white dark:!text-zinc-950 hover:opacity-90' => $plan->slug !== 'pro'
                        ])
                    >
                        {{-- Feedback de loading visual --}}
                        <span wire:loading.remove wire:target="checkout('{{ $plan->slug }}')">
                            Assinar Agora
                        </span>
                        <span wire:loading wire:target="checkout('{{ $plan->slug }}')" class="flex items-center justify-center gap-2">
                            Processando...
                        </span>
                    </x-ui.button>
                @endif
            </div>
        @endforeach
    </div>
</div>
