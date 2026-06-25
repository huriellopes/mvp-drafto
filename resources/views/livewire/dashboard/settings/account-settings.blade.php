@use(App\Enums\RoleEnum)
<div class="max-w-4xl space-y-10 pb-20">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="min-w-0">
            {{ Breadcrumbs::render('dashboard.account') }}
        </div>

        @if(auth()->user()->role === RoleEnum::READER)
            <div class="shrink-0">
                <x-ui.button
                    wire:click="openBecomeWriterModal"
                    variant="primary"
                    sizes="sm"
                    class="!rounded-xl shadow-sm w-full sm:w-auto"
                >
                    <x-lucide-pen-tool class="mr-2 h-4 w-4" />
                    Quero ser Escritor
                </x-ui.button>
            </div>
        @endif
    </div>

    <form wire:submit="save" class="space-y-10">
        {{-- Dados Pessoais --}}
        <x-ui.section-card title="{{ __('dashboard.account.settings.info_section.title') }}" description="{{ __('dashboard.account.settings.info_section.description') }}">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-ui.input
                    wire:model="form.name"
                    label="{{ __('dashboard.account.settings.info_section.name_label') }}"
                    placeholder="Seu nome"
                    :error="$errors->first('form.name')"
                />

                <x-ui.input
                    wire:model="form.email"
                    label="{{ __('dashboard.account.settings.info_section.email_label') }}"
                    type="email"
                    placeholder="seu@email.com"
                    :error="$errors->first('form.email')"
                />
            </div>

            @if(auth()->user()->email_verified_at === null)
                <div class="mt-4 flex items-center gap-2 rounded-2xl bg-amber-50 dark:bg-amber-500/10 p-4 text-sm text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20">
                    <x-lucide-alert-circle class="h-4 w-4" />
                    {{ __('dashboard.account.settings.info_section.not_verified') }}
                </div>
            @endif
        </x-ui.section-card>

        {{-- Segurança --}}
        <x-ui.section-card title="{{ __('dashboard.account.settings.security_section.title') }}" description="{{ __('dashboard.account.settings.security_section.description') }}">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-ui.input
                    wire:model="form.password"
                    label="{{ __('dashboard.account.settings.security_section.password_label') }}"
                    type="password"
                    placeholder="••••••••"
                    :error="$errors->first('form.password')"
                />

                <x-ui.input
                    wire:model="form.password_confirmation"
                    label="{{ __('dashboard.account.settings.security_section.confirm_password_label') }}"
                    type="password"
                    placeholder="••••••••"
                />
            </div>

            <div class="mt-8 pt-8 border-t border-zinc-100 dark:border-zinc-800">
                <livewire:dashboard.settings.two-factor-manager />
            </div>
        </x-ui.section-card>

        <x-ui.section-card title="Preferências de e-mail" description="Escolha quais comunicações você quer receber por e-mail.">
            <div class="space-y-4">
                <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl bg-zinc-50 p-4 ring-1 ring-zinc-100 dark:bg-zinc-800/40 dark:ring-zinc-800">
                    <div class="min-w-0">
                        <span class="block text-sm font-bold text-zinc-800 dark:text-zinc-100">Lembretes de retorno</span>
                        <span class="block text-xs text-zinc-500">E-mails nos convidando a voltar quando você ficar um tempo sem escrever.</span>
                    </div>
                    <input type="checkbox" wire:model="form.wants_reengagement_emails" class="h-5 w-5 shrink-0 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                </label>

                <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl bg-zinc-50 p-4 ring-1 ring-zinc-100 dark:bg-zinc-800/40 dark:ring-zinc-800">
                    <div class="min-w-0">
                        <span class="block text-sm font-bold text-zinc-800 dark:text-zinc-100">Novidades da plataforma</span>
                        <span class="block text-xs text-zinc-500">Avisos sobre melhorias e novidades do Drafto.</span>
                    </div>
                    <input type="checkbox" wire:model="form.wants_product_updates" class="h-5 w-5 shrink-0 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                </label>
            </div>
        </x-ui.section-card>

        <div class="flex justify-end gap-3 border-t border-zinc-200 dark:border-zinc-800 pt-8">
            <x-ui.button
                type="submit"
                loading="save"
                class="px-12 w-full"
                sizes="lg"
            >
                {{ __('dashboard.account.settings.submit_button') }}
            </x-ui.button>
        </div>
    </form>

    {{-- Privacidade e dados --}}
    <x-ui.section-card
        title="Privacidade e dados"
        description="Gerencie seus dados pessoais e suas preferências de privacidade."
    >
        <div class="flex flex-col gap-4 rounded-2xl bg-zinc-50 p-5 ring-1 ring-zinc-100 dark:bg-zinc-800/40 dark:ring-zinc-800 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <span class="block text-sm font-bold text-zinc-800 dark:text-zinc-100">Baixar meus dados</span>
                <span class="mt-1 block text-xs leading-5 text-zinc-500">
                    Exporte uma cópia em JSON dos seus dados (conta, perfil, posts, comentários e coleções), conforme seu direito de portabilidade (LGPD).
                </span>
            </div>

            <x-ui.button
                type="button"
                wire:click="exportData"
                loading="exportData"
                variant="secondary"
                size="sm"
                class="w-full shrink-0 !rounded-xl sm:w-auto"
            >
                <x-lucide-download class="mr-2 h-4 w-4" />
                Exportar dados
            </x-ui.button>
        </div>

        <div class="mt-4 text-xs text-zinc-500">
            Leia nossa
            <a href="{{ route('pages.privacy') }}" target="_blank" class="font-semibold text-indigo-600 underline hover:text-indigo-700 dark:text-indigo-400">Política de Privacidade</a>
            ou
            <button type="button" onclick="window.draftoOpenConsent && window.draftoOpenConsent()" class="font-semibold text-indigo-600 underline hover:text-indigo-700 dark:text-indigo-400">gerencie suas preferências de cookies</button>.
        </div>
    </x-ui.section-card>

    {{-- Zona de Perigo --}}
    <x-ui.section-card
        title="Zona de perigo"
        description="Ações irreversíveis relacionadas à sua conta."
    >
        <div class="flex flex-col gap-4 rounded-2xl border border-red-100 bg-red-50 p-5 dark:border-red-500/20 dark:bg-red-500/10 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <span class="block text-sm font-bold text-red-700 dark:text-red-400">Deletar minha conta</span>
                <span class="mt-1 block text-xs leading-5 text-red-600/80 dark:text-red-400/70">
                    Sua conta e todos os dados associados (perfil, posts, comentários, coleções, curtidas e seguidores) serão removidos. Esta ação não pode ser desfeita.
                </span>
            </div>

            <x-ui.button
                type="button"
                wire:click="openDeleteAccountModal"
                variant="danger"
                size="sm"
                class="w-full shrink-0 !rounded-xl sm:w-auto"
            >
                <x-lucide-trash-2 class="mr-2 h-4 w-4" />
                Deletar conta
            </x-ui.button>
        </div>
    </x-ui.section-card>

    <x-ui.confirm-modal
        name="confirm-delete-account"
        title="Deletar conta"
        content="Tem certeza que deseja deletar sua conta? Todos os seus dados (perfil, posts, comentários, coleções, curtidas e seguidores) serão removidos permanentemente. Esta ação não pode ser desfeita."
        buttonText="Sim, deletar minha conta"
        variant="danger"
        action="deleteAccount"
    />

    <x-ui.confirm-modal
        name="confirm-become-writer"
        title="Tornar-se Escritor"
        content="Você tem certeza que deseja se tornar um Escritor? Ao confirmar, você terá acesso a ferramentas de criação, rascunhos e gestão de artigos."
        buttonText="Sim, quero ser Escritor"
        variant="primary"
        action="becomeWriter"
    />
</div>
