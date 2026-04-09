<x-auth.card
    :title="__('auth.password_reset.reset_title')"
    :description="__('auth.password_reset.reset_subtitle')"
    :showLogo="false"
>
    <form wire:submit="resetPassword" class="space-y-5">
        <x-ui.input
            wire:model="form.email"
            :label="__('auth.password_reset.email_label')"
            placeholder="voce@exemplo.com"
            :error="$errors->first('form.email')"
        />
        <x-ui.input
            wire:model="form.password"
            :label="__('auth.register.password_label')"
            type="password"
            placeholder="••••••••"
            :error="$errors->first('form.password')"
        />
        <x-ui.input
            wire:model="form.password_confirmation"
            :label="__('auth.register.password_confirmation_label')"
            type="password" placeholder="••••••••"
        />

        <x-ui.button
            type="submit"
            loading="resetPassword"
            class="w-full"
        >
            {{ __('auth.password_reset.submit') }}
        </x-ui.button>
    </form>
</x-auth.card>
