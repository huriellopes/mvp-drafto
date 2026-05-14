@if (! auth()->user()->hasVerifiedEmail() && auth()->user()->hasVerificationExpired())
    <x-ui.banner
        title="Verificação Pendente"
        variant="warning"
        class="mb-8"
    >
        Percebemos que você ainda não confirmou seu e-mail. Para garantir a segurança da sua conta e continuar aproveitando todos os recursos do Drafto, por favor, realize a verificação.

        <x-slot:actions>
            <livewire:auth.resend-verification />
        </x-slot:actions>
    </x-ui.banner>
@endif
