<div class="max-w-4xl space-y-10 pb-20">
    {{ Breadcrumbs::render('dashboard.account') }}

    <form wire:submit="save" class="space-y-10">
        {{-- Dados Pessoais --}}
        <x-ui.section-card title="Informações da Conta" description="Atualize seu nome de exibição e endereço de e-mail institucional.">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-ui.input
                    wire:model="form.name"
                    label="Nome Completo"
                    placeholder="Seu nome"
                    :error="$errors->first('form.name')"
                />

                <x-ui.input
                    wire:model="form.email"
                    label="E-mail"
                    type="email"
                    placeholder="seu@email.com"
                    :error="$errors->first('form.email')"
                />
            </div>

            @if(auth()->user()->email_verified_at === null)
                <div class="mt-4 flex items-center gap-2 rounded-2xl bg-amber-50 p-4 text-sm text-amber-700 border border-amber-100">
                    <x-lucide-alert-circle class="h-4 w-4" />
                    Seu e-mail ainda não foi verificado. Verifique sua caixa de entrada.
                </div>
            @endif
        </x-ui.section-card>

        {{-- Segurança --}}
        <x-ui.section-card title="Segurança" description="Altere sua senha periodicamente para manter sua conta protegida. Deixe em branco se não desejar alterar.">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-ui.input
                    wire:model="form.password"
                    label="Nova Senha"
                    type="password"
                    placeholder="••••••••"
                    :error="$errors->first('form.password')"
                />

                <x-ui.input
                    wire:model="form.password_confirmation"
                    label="Confirmar Nova Senha"
                    type="password"
                    placeholder="••••••••"
                />
            </div>
        </x-ui.section-card>

        <div class="flex justify-end gap-3 border-t border-zinc-200 pt-8">
            <x-ui.button
                type="submit"
                loading="save"
                class="px-12 w-full"
                sizes="lg"
            >
                Salvar Alterações
            </x-ui.button>
        </div>
    </form>
</div>
