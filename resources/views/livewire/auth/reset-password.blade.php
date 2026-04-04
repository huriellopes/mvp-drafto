<x-auth.card
    title="Nova senha"
    description="Escolha uma senha forte para proteger sua conta."
    :showLogo="false"
>
    <form wire:submit="resetPassword" class="space-y-5">
        <x-ui.input wire:model="email" label="Confirme seu e-mail" placeholder="voce@exemplo.com" :error="$errors->first('email')" />
        <x-ui.input wire:model="password" label="Nova senha" type="password" placeholder="••••••••" :error="$errors->first('password')" />
        <x-ui.input wire:model="password_confirmation" label="Confirme a nova senha" type="password" placeholder="••••••••" />

        <x-ui.button loading="resetPassword">
            Redefinir senha
        </x-ui.button>
    </form>
</x-auth.card>
