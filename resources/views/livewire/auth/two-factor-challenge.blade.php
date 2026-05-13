<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[2rem] bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 shadow-xl shadow-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 mb-6">
            <x-lucide-shield-check class="h-8 w-8" />
        </div>
        
        <h2 class="text-3xl font-black italic tracking-tight text-zinc-900 dark:text-white">Verificação de Segurança</h2>
        <p class="mt-3 text-sm font-medium text-zinc-500 dark:text-zinc-400 max-w-[280px] mx-auto leading-relaxed">
            @if (! $recovery)
                Abra seu aplicativo autenticador e insira o código de 6 dígitos para continuar.
            @else
                Insira um de seus códigos de recuperação de emergência para acessar sua conta.
            @endif
        </p>
    </div>

    <form wire:submit="verify" class="space-y-6">
        <div class="space-y-2">
            <label for="code" class="text-[10px] font-black uppercase tracking-widest text-zinc-400 px-1">
                {{ $recovery ? 'Código de Recuperação' : 'Código de Autenticação' }}
            </label>
            
            <x-ui.input 
                wire:model="code" 
                id="code" 
                type="text" 
                inputmode="numeric"
                autocomplete="one-time-code"
                placeholder="{{ $recovery ? 'XXXXX-XXXXX' : '000000' }}"
                class="text-center {{ ! $recovery ? 'tracking-[0.5em]' : '' }} font-black text-lg py-4 !rounded-3xl shadow-sm"
                :error="$errors->first('code')"
                autofocus
            />
        </div>

        <x-ui.button type="submit" class="w-full py-4 !rounded-3xl shadow-lg shadow-indigo-500/20" sizes="lg">
            Verificar e Entrar
        </x-ui.button>

        <div class="text-center">
            <button 
                type="button" 
                wire:click="toggleRecovery" 
                class="text-[10px] font-black uppercase tracking-widest text-zinc-400 hover:text-indigo-600 transition-colors"
            >
                {{ $recovery ? 'Usar código do aplicativo' : 'Perdeu o acesso? Usar código de recuperação' }}
            </button>
        </div>
    </form>
</div>
