<div class="w-full max-w-xl mx-auto">
    <div class="bg-white dark:bg-zinc-900 rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-4 mb-6">
                <div class="h-12 w-12 rounded-2xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center">
                    <x-lucide-shield-alert class="h-6 w-6 text-amber-600" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Segurança da Conta</h2>
                    <p class="text-sm text-zinc-500">Sua senha foi resetada recentemente e precisa ser atualizada.</p>
                </div>
            </div>

            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-700/50">
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Para garantir a segurança dos seus dados e das suas publicações, exigimos que você escolha uma nova senha forte antes de prosseguir para o painel administrativo.
                </p>
            </div>
        </div>

        <form wire:submit="changePassword" class="p-8 space-y-6">
            <div class="grid gap-6">
                <x-ui.input
                    wire:model="password"
                    label="Nova Senha"
                    type="password"
                    placeholder="••••••••"
                    required
                    :error="$errors->first('password')"
                >
                    <x-slot:prefix><x-lucide-lock class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
                </x-ui.input>

                <x-ui.input
                    wire:model="password_confirmation"
                    label="Confirme a Nova Senha"
                    type="password"
                    placeholder="••••••••"
                    required
                >
                    <x-slot:prefix><x-lucide-check-circle class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
                </x-ui.input>
            </div>

            <div class="flex flex-col gap-4">
                <div class="text-xs text-zinc-500 space-y-2 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-zinc-100 dark:border-zinc-700/50">
                    <p class="font-bold uppercase tracking-widest text-[10px]">Requisitos de Segurança:</p>
                    <ul class="grid grid-cols-2 gap-2">
                        <li class="flex items-center gap-2">
                            <x-lucide-check class="h-3 w-3 text-emerald-500" />
                            Mínimo de 8 caracteres
                        </li>
                        <li class="flex items-center gap-2">
                            <x-lucide-check class="h-3 w-3 text-emerald-500" />
                            Letras maiúsculas e minúsculas
                        </li>
                        <li class="flex items-center gap-2">
                            <x-lucide-check class="h-3 w-3 text-emerald-500" />
                            Números e símbolos
                        </li>
                        <li class="flex items-center gap-2">
                            <x-lucide-check class="h-3 w-3 text-emerald-500" />
                            Diferente da senha padrão
                        </li>
                    </ul>
                </div>

                <x-ui.button type="submit" class="w-full py-4 rounded-2xl shadow-lg shadow-zinc-900/10" loading="changePassword">
                    Atualizar Senha e Acessar Dashboard
                </x-ui.button>
            </div>
        </form>
    </div>

    <div class="mt-8 text-center">
        <p class="text-sm text-zinc-500">
            Dúvidas? Entre em contato com o <a href="mailto:support@drafto.com" class="text-zinc-900 dark:text-white font-bold hover:underline">suporte técnico</a>.
        </p>
    </div>
</div>
