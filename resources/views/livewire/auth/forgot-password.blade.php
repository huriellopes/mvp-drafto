<x-auth.card
    title="Recuperar senha"
    description="Enviaremos um link para redefinição."
    :showLogo="false"
>
    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="sendResetLink" class="space-y-5">
        <x-ui.input
            wire:model="email"
            label="E-mail cadastrado"
            placeholder="voce@exemplo.com"
            :error="$errors->first('email')"
        />

        <x-ui.button loading="sendResetLink">
            Enviar link de recuperação
        </x-ui.button>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" wire:navigate class="text-sm font-semibold text-zinc-900 hover:underline">
                Voltar para o login
            </a>
        </div>
    </form>
</x-auth.card>
