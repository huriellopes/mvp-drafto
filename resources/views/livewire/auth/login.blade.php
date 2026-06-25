<x-auth.card
    :title="__('auth.login.title')"
    :description="__('auth.login.subtitle')"
>
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-5">
        <x-ui.input
            wire:model.blur="form.email"
            type="email"
            :label="__('auth.login.email_label')"
            placeholder="voce@exemplo.com"
            :error="$errors->first('form.email')"
        />

        <div>
            <div class="mb-2 flex items-center justify-between">
                <label for="password" class="block text-sm font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-widest text-[10px]">{{ __('auth.login.password_label') }}</label>
                <a href="{{ route('password.request') }}" wire:navigate class="text-[10px] font-black uppercase tracking-widest text-zinc-400 hover:text-indigo-600 transition">
                    {{ __('auth.login.forgot_password') }}
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

        <label class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400 cursor-pointer group">
            <input wire:model="form.remember" type="checkbox" class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 transition cursor-pointer">
            <span class="group-hover:text-zinc-900 dark:group-hover:text-white transition">{{ __('auth.login.remember_me') }}</span>
        </label>

        <x-ui.button
            type="submit"
            variant="primary"
            loading="login"
            size="lg"
            class="w-full"
        >
            {{ __('auth.login.submit') }}
        </x-ui.button>

        <div class="relative py-1 text-center">
            <span class="relative bg-white dark:bg-zinc-900 px-3 text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('auth.login.or') }}</span>
            <div class="absolute inset-x-0 top-1/2 -z-10 h-px bg-zinc-100 dark:bg-zinc-800"></div>
        </div>

        <a href="{{ route('auth.magic.request') }}" wire:navigate
           class="flex w-full items-center justify-center gap-2 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-6 py-3 text-sm font-bold text-zinc-900 dark:text-white shadow-sm transition hover:bg-zinc-50 dark:hover:bg-zinc-800 active:scale-95">
            <x-lucide-wand-sparkles class="h-4 w-4 text-profile-primary" />
            {{ __('auth.login.magic_link') }}
        </a>

        <div class="mt-6 border-t border-zinc-100 dark:border-zinc-800 pt-6 text-center">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('auth.login.no_account') }}
                <a href="{{ route('register') }}" wire:navigate class="font-bold text-zinc-900 dark:text-white hover:text-indigo-600 transition underline decoration-zinc-200 dark:decoration-zinc-800 underline-offset-4">
                    {{ __('auth.login.register_link') }}
                </a>
            </p>
        </div>
    </form>
</x-auth.card>
