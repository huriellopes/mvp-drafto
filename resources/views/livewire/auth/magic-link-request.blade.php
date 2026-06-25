<x-auth.card
    :title="$sent ? __('auth.magic_link.sent_title') : __('auth.magic_link.title')"
    :description="$sent ? __('auth.magic_link.sent_desc', ['email' => $form->email]) : __('auth.magic_link.subtitle')"
    :showLogo="false"
>
    @if (!$sent)
        {{-- Estado 1: Formulário de Envio --}}
        <form wire:submit="sendLink" class="space-y-5">
            <x-ui.input
                wire:model="form.email"
                type="email"
                :label="__('auth.magic_link.email_label')"
                placeholder="voce@exemplo.com"
                :error="$errors->first('form.email')"
            />

            <x-ui.button
                type="submit"
                loading="sendLink"
                class="w-full"
            >
                {{ __('auth.magic_link.send_link') }}
            </x-ui.button>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" wire:navigate class="text-sm font-bold text-zinc-900 dark:text-white hover:text-profile-primary transition">
                    {{ __('auth.magic_link.back_to_login') }}
                </a>
            </div>
        </form>
    @else
        {{-- Estado 2: Confirmação de envio --}}
        <div class="flex flex-col items-center justify-center py-6 text-center space-y-6 animate-in fade-in zoom-in duration-300">
            <div class="h-20 w-20 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                <x-lucide-mail-check class="h-10 w-10" />
            </div>

            <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                {{ __('auth.magic_link.sent_help') }}
            </p>

            <x-ui.button
                href="{{ route('login') }}"
                variant="secondary"
                class="w-full"
                wire:navigate
            >
                <x-lucide-chevron-left class="h-4 w-4 mr-2" />
                {{ __('auth.magic_link.back_to_login') }}
            </x-ui.button>
        </div>
    @endif
</x-auth.card>
