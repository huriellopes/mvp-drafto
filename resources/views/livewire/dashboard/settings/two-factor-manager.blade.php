<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-black italic text-zinc-900 dark:text-white">Autenticação de Dois Fatores (2FA)</h3>
            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-1">
                Adicione uma camada extra de segurança à sua conta usando um aplicativo autenticador.
            </p>
        </div>

        <div>
            @if ($user->hasTwoFactorEnabled())
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                    <x-lucide-shield-check class="h-3 w-3" />
                    Ativo
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-zinc-500">
                    <x-lucide-shield-off class="h-3 w-3" />
                    Inativo
                </span>
            @endif
        </div>
    </div>

    @if (! $user->hasTwoFactorEnabled())
        @if ($showingQrCode)
            <div class="rounded-3xl bg-zinc-50 dark:bg-zinc-900 p-6 border border-zinc-100 dark:border-zinc-800 space-y-6 animate-in fade-in zoom-in duration-300">
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-zinc-100">
                        {!! $this->twoFactorQrCodeSvg !!}
                    </div>

                    <div class="flex-1 space-y-4 text-center md:text-left">
                        <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300">
                            Para habilitar o 2FA, escaneie o QR Code acima com seu aplicativo autenticador (Google Authenticator, Authy, etc).
                        </p>
                        
                        <div class="space-y-2">
                            <label for="code" class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Código de Confirmação</label>
                            <div class="flex gap-3 justify-center md:justify-start">
                                <x-ui.input 
                                    wire:model="code" 
                                    id="code" 
                                    placeholder="000000" 
                                    class="max-w-[150px] text-center tracking-[0.5em] font-black"
                                    maxlength="6"
                                />
                                <x-ui.button wire:click="confirm" class="!rounded-2xl">
                                    Confirmar
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-zinc-200 dark:border-zinc-800 p-8 text-center space-y-4">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600">
                    <x-lucide-shield-plus class="h-6 w-6" />
                </div>
                <div class="max-w-xs mx-auto">
                    <p class="text-sm font-bold text-zinc-900 dark:text-white">Ainda não configurado</p>
                    <p class="text-xs text-zinc-500 mt-1">Habilite para proteger sua conta contra acessos não autorizados.</p>
                </div>
                <x-ui.button wire:click="enable" class="!px-8 !rounded-2xl">
                    Habilitar 2FA
                </x-ui.button>
            </div>
        @endif
    @else
        <div class="rounded-3xl bg-zinc-50 dark:bg-zinc-900 p-6 border border-zinc-100 dark:border-zinc-800 space-y-6">
            <div class="flex flex-wrap gap-4">
                <x-ui.button wire:click="showRecoveryCodes" variant="secondary" class="!rounded-2xl text-[10px]">
                    {{ $showingRecoveryCodes ? 'Ocultar Códigos' : 'Ver Códigos de Recuperação' }}
                </x-ui.button>

                <x-ui.button 
                    wire:confirm="Tem certeza que deseja desativar o 2FA? Sua conta ficará menos protegida."
                    wire:click="disable" 
                    variant="danger" 
                    class="!rounded-2xl text-[10px]"
                >
                    Desativar 2FA
                </x-ui.button>
            </div>

            @if ($showingRecoveryCodes)
                <div class="space-y-4 animate-in slide-in-from-top-4 duration-300">
                    <div class="rounded-2xl bg-amber-50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 p-4">
                        <p class="text-[10px] font-bold text-amber-700 dark:text-amber-400 leading-tight">
                            ⚠️ Guarde estes códigos em um local seguro. Eles são a única forma de acessar sua conta caso você perca o acesso ao seu dispositivo 2FA.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($user->two_factor_recovery_codes as $recoveryCode)
                            <code class="bg-white dark:bg-zinc-950 p-3 rounded-xl border border-zinc-100 dark:border-zinc-800 text-[10px] font-black text-zinc-600 dark:text-zinc-400 text-center select-all">
                                {{ $recoveryCode }}
                            </code>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
