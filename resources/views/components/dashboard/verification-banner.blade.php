@if (! auth()->user()->hasVerifiedEmail())
    <x-ui.banner
        title="Confirme seu e-mail"
        variant="warning"
        class="mb-8"
    >
        Você tem mais <strong>{{ auth()->user()->daysLeftToVerify() }} dias</strong> para verificar sua conta antes que o acesso seja restrito.

        <x-slot:actions>
            <livewire:auth.resend-verification />
        </x-slot:actions>
    </x-ui.banner>
@endif
