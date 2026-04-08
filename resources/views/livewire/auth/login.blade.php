<x-auth.card
    title="Entrar na plataforma"
    description="Acesse sua conta para escrever, publicar e acompanhar seu conteúdo."
>
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-5">
        <x-ui.input
            wire:model.blur="form.email"
            type="email"
            label="E-mail"
            placeholder="voce@exemplo.com"
            :error="$errors->first('form.email')"
        />

        <div>
            <div class="mb-2 flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-zinc-700">Senha</label>
                <a href="{{ route('password.request') }}" wire:navigate class="text-xs font-medium text-zinc-500 hover:text-zinc-900">
                    Esqueceu a senha?
                </a>
            </div>
            <x-ui.input
                wire:model.blur="form.password"
                id="password"
                type="password"
                placeholder="••••••••"
                :error="$errors->first('form.password')"
            />
        </div>

        <label class="flex items-center gap-3 text-sm text-zinc-600">
            <input wire:model="form.remember" type="checkbox" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
            <span>Lembrar de mim</span>
        </label>

        <x-ui.button
            type="submit"
            variant="primary"
            loading="login"
            size="lg"
            class="w-full"
        >
            Entrar
        </x-ui.button>

        <div class="mt-6 border-t border-zinc-100 pt-6 text-center">
            <p class="text-sm text-zinc-600">
                Não tem uma conta?
                <a href="{{ route('register') }}" wire:navigate class="font-semibold text-zinc-900 hover:underline">
                    Criar conta gratuitamente
                </a>
            </p>
        </div>
    </form>
</x-auth.card>
