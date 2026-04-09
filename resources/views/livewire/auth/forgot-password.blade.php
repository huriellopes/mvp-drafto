<x-auth.card
    :title="$sent ? 'Verifique seu e-mail' : __('auth.password_reset.title')"
    :description="$sent ? 'Enviamos as instruções de recuperação para ' . $form->email : __('auth.password_reset.subtitle')"
    :showLogo="false"
>
    @if (!$sent)
        {{-- Estado 1: Formulário de Envio --}}
        <form wire:submit="sendResetLink" class="space-y-5">
            <x-ui.input
                wire:model="form.email"
                :label="__('auth.password_reset.email_label')"
                placeholder="voce@exemplo.com"
                :error="$errors->first('form.email')"
            />

            <x-ui.button
                type="submit"
                loading="sendResetLink"
                class="w-full"
            >
                {{ __('auth.password_reset.send_link') }}
            </x-ui.button>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" wire:navigate class="text-sm font-bold text-zinc-900 dark:text-white hover:text-profile-primary transition">
                    {{ __('auth.password_reset.back_to_login') }}
                </a>
            </div>
        </form>
    @else
        {{-- Estado 2: Mensagem de Sucesso (Check Mail) --}}
        <div class="flex flex-col items-center justify-center py-6 text-center space-y-6 animate-in fade-in zoom-in duration-300">
            <div class="h-20 w-20 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                <x-lucide-mail-check class="h-10 w-10" />
            </div>

            <div class="space-y-2">
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Não recebeu o e-mail? Verifique sua pasta de **spam** ou aguarde alguns instantes antes de tentar novamente.
                </p>
            </div>

            <x-ui.button
                href="{{ route('login') }}"
                variant="secondary"
                class="w-full"
                wire:navigate
            >
                <x-lucide-arrow-left class="h-4 w-4 mr-2" />
                Voltar para o Login
            </x-ui.button>
        </div>
    @endif
</x-auth.card>
