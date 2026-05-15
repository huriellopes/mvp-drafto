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

    <x-ui.confirm-modal
        name="confirm-become-writer"
        title="Tornar-se Escritor"
        content="Você tem certeza que deseja se tornar um Escritor? Ao confirmar, você terá acesso a ferramentas de criação, rascunhos e gestão de artigos."
        buttonText="Sim, quero ser Escritor"
        variant="primary"
        action="becomeWriter"
    />
</div>
