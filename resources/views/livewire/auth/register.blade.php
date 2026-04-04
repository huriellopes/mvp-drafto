<x-auth.card
    title="Comece a escrever"
    description="Crie sua conta e junte-se à nossa comunidade de escritores."
>
    <form wire:submit="register" class="space-y-5">
        <div class="grid grid-cols-2 gap-4">
            @foreach(['reader' => 'Ler', 'writer' => 'Escrever'] as $value => $label)
                <label class="relative flex cursor-pointer flex-col rounded-2xl border border-zinc-200 p-4 transition hover:bg-zinc-50 has-[:checked]:border-zinc-900 has-[:checked]:ring-1 has-[:checked]:ring-zinc-900">
                    <input type="radio" wire:model="form.role" value="{{ $value }}" class="sr-only">
                    <span class="text-xs font-medium text-zinc-500">Eu quero</span>
                    <span class="mt-1 text-sm font-semibold text-zinc-900">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('form.role') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

        <x-ui.input wire:model.blur="form.name" label="Nome completo" placeholder="Seu nome" :error="$errors->first('form.name')" />
        <x-ui.input wire:model.blur="form.email" label="E-mail" placeholder="voce@exemplo.com" :error="$errors->first('form.email')" />
        <x-ui.input wire:model.blur="form.password" label="Senha" type="password" placeholder="••••••••" :error="$errors->first('form.password')" />

        <x-ui.button loading="register">
            Criar minha conta
        </x-ui.button>

        <x-slot:footer>
            <div class="mt-6 text-center">
                <p class="text-sm text-zinc-600">
                    Já tem uma conta?
                    <a href="{{ route('login') }}" wire:navigate class="font-semibold text-zinc-900 hover:underline">Entrar</a>
                </p>
            </div>
        </x-slot:footer>
    </form>
</x-auth.card>
