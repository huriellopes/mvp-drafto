<div class="max-w-4xl space-y-10 pb-20">
    {{ Breadcrumbs::render('dashboard.account') }}

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
        </x-ui.section-card>

        {{-- Assinatura --}}
        <x-ui.section-card title="Assinatura e Plano" description="Gerencie seu plano atual e informações de faturamento.">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <p class="text-sm font-bold text-zinc-900 dark:text-white">
                        Plano Atual: 
                        <span class="text-indigo-600 uppercase tracking-wider ml-1">
                            {{ auth()->user()->getPlanName() }}
                        </span>
                    </p>
                    <p class="text-xs text-zinc-500 mt-1">
                        @if(auth()->user()->is_lifetime)
                            Você possui acesso ilimitado para sempre ao Drafto.
                        @elseif(auth()->user()->subscribed('plus') || auth()->user()->subscribed('pro'))
                            Sua assinatura está ativa. Gerencie cobranças no portal do Stripe.
                        @else
                            Você está usando a versão gratuita com recursos limitados.
                        @endif
                    </p>
                </div>

                <div>
                    @if(auth()->user()->subscribed('plus') || auth()->user()->subscribed('pro'))
                        <x-ui.button 
                            type="button"
                            href="{{ route('dashboard.billing.portal') }}"
                            variant="outline"
                            sizes="sm"
                            class="!rounded-xl"
                        >
                            Gerenciar Assinatura
                        </x-ui.button>
                    @else
                        <x-ui.button 
                            type="button"
                            href="{{ route('dashboard.billing.plans') }}"
                            sizes="sm"
                            class="!rounded-xl"
                        >
                            Mudar de Plano
                        </x-ui.button>
                    @endif
                </div>
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
</div>
