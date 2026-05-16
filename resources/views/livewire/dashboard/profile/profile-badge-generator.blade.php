<div class="space-y-12 pb-20" x-data="{
    loading: false,
    copyEmbed() {
        const code = $refs.embedCode.innerText.trim();
        window.navigator.clipboard.writeText(code);
        $dispatch('notify', { message: '{{ __('dashboard.badge.messages.copy_success') }}' });
    },
    async downloadBadge() {
        if (this.loading) return;

        const card = document.getElementById('badge-preview');
        if (!card) return;

        if (typeof htmlToImage === 'undefined') {
            $dispatch('notify', { message: '{{ __('dashboard.badge.messages.loading_motor') }}', type: 'warning' });
            return;
        }

        this.loading = true;
        $dispatch('notify', { message: '{{ __('dashboard.badge.messages.preparing') }}', type: 'info' });

        try {
            const dataUrl = await htmlToImage.toPng(card, {
                pixelRatio: 4, // Qualidade extrema para redes sociais
                backgroundColor: null,
                cacheBust: true,
                style: {
                    borderRadius: '3.5rem',
                    transform: 'scale(1)'
                }
            });

            const link = document.createElement('a');
            link.download = 'drafto-badge-{{ $this->user->profile->username }}.png';
            link.href = dataUrl;
            link.click();

            $dispatch('notify', { message: '{{ __('dashboard.badge.messages.success') }}', type: 'success' });
        } catch (error) {
            console.error('Erro ao gerar crachá:', error);
            $dispatch('notify', { message: '{{ __('dashboard.badge.messages.error') }}', type: 'error' });
        } finally {
            this.loading = false;
        }
    }
}">
    {{ Breadcrumbs::render('dashboard.profile.badge') }}
    <header class="space-y-2">
        <h2 class="text-4xl font-black tracking-tighter text-zinc-900 dark:text-white italic">
            {{ explode('.', __('dashboard.badge.title'))[0] }} <span class="text-indigo-600 dark:text-indigo-400">{{ explode('.', __('dashboard.badge.title'))[1] ?? '' }}</span>
        </h2>
        <p class="text-zinc-500 dark:text-zinc-400 font-medium">{{ __('dashboard.badge.subtitle') }}</p>
    </header>

@php
    $isAdmin = $this->user->isAdmin();
@endphp

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
        {{-- Coluna de Configuração --}}
        <aside class="lg:col-span-5 space-y-8">
            <x-ui.section-card :title="__('dashboard.badge.customization')" :description="__('dashboard.badge.customization_desc')">
                <div class="space-y-6">
                    <x-ui.select :label="__('dashboard.badge.theme_label')" wire:model.live="form.theme">
                        <option value="dark">{{ __('dashboard.badge.themes.dark') }}</option>
                        <option value="light">{{ __('dashboard.badge.themes.light') }}</option>
                        <option value="brand">Identidade Visual (Sua Marca)</option>
                    </x-ui.select>

                    <div class="grid grid-cols-1 gap-4">
                        <x-ui.checkbox wire:model.live="form.showStats" :label="__('dashboard.badge.show_stats')" />
                        <x-ui.checkbox wire:model.live="form.showBio" :label="__('dashboard.badge.show_bio')" />
                        <x-ui.checkbox wire:model.live="form.showLocation" label="Mostrar Localização" />
                    </div>
                </div>
            </x-ui.section-card>


            {{-- Embed Code --}}
            <div class="rounded-[2.5rem] bg-zinc-900 border border-white/5 p-8 space-y-4 shadow-2xl" x-data="{ copied: false }">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-black uppercase tracking-widest text-indigo-400">{{ __('dashboard.badge.embed_title') }}</h4>
                    <button @click="
                        const code = $refs.embedCode.innerText.trim();
                        window.navigator.clipboard.writeText(code);
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                        $dispatch('notify', { message: '{{ __('dashboard.badge.messages.copy_success') }}' });
                    " type="button" class="text-[10px] font-black transition-all uppercase tracking-widest" :class="copied ? 'text-emerald-400 scale-105' : 'text-white/50 hover:text-white'">
                        <span x-text="copied ? 'Copiado!' : '{{ __('dashboard.badge.copy_code') }}'"></span>
                    </button>
                </div>
                <div class="bg-black/40 p-4 rounded-2xl border border-white/5">
                    <code x-ref="embedCode" class="text-[10px] text-zinc-400 font-mono block break-all leading-relaxed">
                        &lt;iframe src="{{ route('public.profile.badge', $this->user->profile->username) }}?theme={{ $form->theme }}&showStats={{ $form->showStats ? 'true' : 'false' }}&showBio={{ $form->showBio ? 'true' : 'false' }}&showLocation={{ $form->showLocation ? 'true' : 'false' }}&embed=true" width="420" height="600" frameborder="0"&gt;&lt;/iframe&gt;
                    </code>
                </div>
            </div>
        </aside>

        {{-- Coluna de Preview --}}
        <main class="lg:col-span-7 flex flex-col items-center justify-center bg-zinc-50 dark:bg-zinc-900/50 rounded-[4rem] border-2 border-dashed border-zinc-200 dark:border-zinc-800 p-12 transition-colors duration-500">
            <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-400 mb-12 italic">{{ __('dashboard.badge.preview_title') }}</h4>

            {{-- O NOVO CARD UNIFICADO --}}
            <div class="w-[420px] max-w-full">
                <x-public.author-badge 
                    :user="$this->user" 
                    mode="embed" 
                    :theme="$form->theme" 
                    :showStats="$form->showStats" 
                    :showBio="$form->showBio" 
                    :showLocation="$form->showLocation"
                    class="shadow-[0_32px_64px_-15px_rgba(0,0,0,0.3)]"
                />
            </div>

            {{-- Action Button --}}
            <x-ui.button
                @click="downloadBadge"
                type="button"
                class="mt-12 !w-auto px-12 !rounded-[2rem] !bg-zinc-900 dark:!bg-white !text-white dark:!text-zinc-900 font-black uppercase text-xs tracking-widest py-5 shadow-2xl active:scale-95 transition-all"
                x-bind:disabled="loading"
            >
                <template x-if="!loading">
                    <span class="flex items-center gap-2">
                        <x-lucide-download class="h-4 w-4" /> {{ __('dashboard.badge.download_button') }}
                    </span>
                </template>
                <template x-if="loading">
                    <span class="flex items-center gap-2">
                        <x-lucide-loader-2 class="h-4 w-4 animate-spin" /> <span>{{ __('dashboard.badge.downloading') }}</span>
                    </span>
                </template>
            </x-ui.button>
        </main>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js"></script>
