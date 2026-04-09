<x-auth.card
    :title="__('auth.register.title')"
    :description="__('auth.register.subtitle')"
>
    <form wire:submit="register" class="space-y-5">
        <div class="grid grid-cols-2 gap-4">
            @foreach(['reader' => 'Ler', 'writer' => 'Escrever'] as $value => $label)
                <label class="relative flex cursor-pointer flex-col rounded-2xl border border-zinc-200 p-4 transition hover:bg-zinc-50 has-[:checked]:border-indigo-600 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600 dark:border-zinc-800 dark:hover:bg-zinc-900">
                    <input type="radio" wire:model="form.role" value="{{ $value }}" class="sr-only">
                    <span class="text-xs font-medium text-zinc-500">Eu quero</span>
                    <span class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('form.role') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

        <x-ui.input wire:model.blur="form.name" :label="__('auth.register.name_label')" placeholder="Seu nome" :error="$errors->first('form.name')" />
        <x-ui.input wire:model.blur="form.email" :label="__('auth.register.email_label')" placeholder="voce@exemplo.com" :error="$errors->first('form.email')" />
        <x-ui.input wire:model.blur="form.password" :label="__('auth.register.password_label')" type="password" placeholder="••••••••" :error="$errors->first('form.password')" />

        <x-ui.button
            type="submit"
            loading="register"
            variant="primary"
            sizes="lg"
            class="w-full"
        >
            {{ __('auth.register.submit') }}
        </x-ui.button>

        <x-slot:footer>
            <div class="mt-6 text-center">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('auth.register.already_registered') }}
                    <a href="{{ route('login') }}" wire:navigate class="font-bold text-zinc-900 dark:text-white hover:text-indigo-600 transition underline decoration-zinc-200 dark:decoration-zinc-800 underline-offset-4">
                        {{ __('auth.register.login_link') }}
                    </a>
                </p>
            </div>
        </x-slot:footer>
    </form>
</x-auth.card>
