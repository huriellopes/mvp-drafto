<div class="max-w-4xl space-y-10 pb-20">
    {{ Breadcrumbs::render('dashboard.support') }}

    <div class="space-y-6">
        <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tighter italic">
            {{ __('dashboard.support.page_title') }}
        </h1>
        <p class="text-zinc-500 dark:text-zinc-400 text-sm font-medium max-w-2xl">
            {{ __('dashboard.support.page_subtitle') }}
        </p>
    </div>

    {{-- Canais Diretos --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ $whatsappUrl }}" target="_blank" class="group p-8 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 transition-all hover:border-emerald-500 hover:shadow-xl hover:shadow-emerald-500/10">
            <div class="flex items-center gap-6">
                <div class="h-14 w-14 flex items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform duration-500">
                    <x-lucide-message-circle class="h-8 w-8" />
                </div>
                <div>
                    <h3 class="font-black text-zinc-900 dark:text-white">{{ __('dashboard.support.channels.whatsapp.title') }}</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('dashboard.support.channels.whatsapp.description') }}</p>
                </div>
            </div>
        </a>

        <a href="mailto:{{ $supportEmail }}" class="group p-8 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 transition-all hover:border-indigo-500 hover:shadow-xl hover:shadow-indigo-500/10">
            <div class="flex items-center gap-6">
                <div class="h-14 w-14 flex items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform duration-500">
                    <x-lucide-mail class="h-8 w-8" />
                </div>
                <div>
                    <h3 class="font-black text-zinc-900 dark:text-white">{{ __('dashboard.support.channels.email.title') }}</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $supportEmail }}</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Formulário de Contato --}}
    <form wire:submit="submit" class="space-y-10">
        <x-ui.section-card
            title="{{ __('dashboard.support.form.title') }}"
            description="{{ __('dashboard.support.form.description') }}"
        >

            @if(! $isFormEnabled)
                <div class="mb-8 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20">
                    <p class="text-amber-500 text-[10px] font-black uppercase tracking-widest text-center">
                        {{ __('dashboard.support.form.disabled') }}
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 @if(!$isFormEnabled) opacity-50 pointer-events-none grayscale @endif">
                <x-ui.input
                    wire:model="form.name"
                    label="{{ __('dashboard.support.form.labels.name') }}"
                    placeholder="{{ __('dashboard.support.form.placeholders.name') }}"
                    :error="$errors->first('form.name')"
                />

                <x-ui.input
                    wire:model="form.email"
                    label="{{ __('dashboard.support.form.labels.email') }}"
                    type="email"
                    placeholder="{{ __('dashboard.support.form.placeholders.email') }}"
                    :error="$errors->first('form.email')"
                />

                <div class="md:col-span-2">
                    <x-ui.input
                        wire:model="form.subject"
                        label="{{ __('dashboard.support.form.labels.subject') }}"
                        placeholder="{{ __('dashboard.support.form.placeholders.subject') }}"
                        :error="$errors->first('form.subject')"
                    />
                </div>

                <div class="md:col-span-2">
                    <x-ui.textarea
                        wire:model="form.message"
                        label="{{ __('dashboard.support.form.labels.message') }}"
                        placeholder="{{ __('dashboard.support.form.placeholders.message') }}"
                        rows="5"
                        :error="$errors->first('form.message')"
                    />
                </div>
            </div>
        </x-ui.section-card>

        <div class="flex justify-end gap-3 border-t border-zinc-200 dark:border-zinc-800 pt-8">
            <x-ui.button
                type="submit"
                loading="submit"
                class="px-12 w-full md:w-auto"
                sizes="lg"
                :disabled="!$isFormEnabled"
            >
                {{ __('dashboard.support.form.submit') }}
            </x-ui.button>
        </div>
    </form>
</div>
