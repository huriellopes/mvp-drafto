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
    $isLifetime = (bool) $this->user->is_lifetime;
    $isPrivileged = $isAdmin || $isLifetime;
    $planSlug = $this->user->plan?->slug;
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
                    </div>
                </div>
            </x-ui.section-card>

            {{-- Plan Status --}}
            <div @class([
                "rounded-[2.5rem] p-8 border-2 transition-all duration-500",
                "bg-emerald-50 border-emerald-100 dark:bg-emerald-500/5 dark:border-emerald-500/20" => $isPrivileged,
                "bg-indigo-50 border-indigo-100 dark:bg-indigo-500/5 dark:border-indigo-500/20" => !$isPrivileged && $planSlug === 'pro',
                "bg-zinc-50 border-zinc-100 dark:bg-zinc-900 dark:border-zinc-800" => !$isPrivileged && $planSlug !== 'pro'
            ])>
                <div class="flex items-center gap-4 mb-4">
                    <div @class([
                        "h-12 w-12 rounded-2xl flex items-center justify-center text-white shadow-lg",
                        "bg-emerald-600 shadow-emerald-500/20" => $isPrivileged,
                        "bg-indigo-600 shadow-indigo-500/20" => !$isPrivileged && $planSlug === 'pro',
                        "bg-zinc-400" => !$isPrivileged && $planSlug !== 'pro'
                    ])>
                        <x-lucide-award class="h-6 w-6" />
                    </div>
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-tighter text-zinc-900 dark:text-white">Status do Crachá</h4>
                        <p class="text-xs text-zinc-500 font-medium">
                            @if($isPrivileged)
                                Acesso Irrestrito (Super-HD + Verificado)
                            @elseif($planSlug === 'pro' && !$this->user->onTrial())
                                Ultra-HD (Impressão) + Verificado
                            @elseif($planSlug === 'plus' && !$this->user->onTrial())
                                High-Definition (Web)
                            @else
                                Qualidade Standard com Marca d'água
                            @endif
                        </p>
                    </div>
                </div>
                @if(!$isPrivileged && ($planSlug !== 'pro' || $this->user->onTrial()))
                    <x-ui.button href="{{ route('dashboard.billing.plans') }}" variant="outline" sizes="sm" class="w-full !rounded-xl">
                        {{ $this->user->onTrial() ? 'Efetivar Assinatura' : 'Fazer Upgrade para Pro' }}
                    </x-ui.button>
                @endif
            </div>

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
                        &lt;iframe src="{{ route('public.profile.badge', $this->user->profile->username) }}?theme={{ $form->theme }}&showStats={{ $form->showStats ? 'true' : 'false' }}&showBio={{ $form->showBio ? 'true' : 'false' }}&embed=true" width="450" height="280" frameborder="0"&gt;&lt;/iframe&gt;
                    </code>
                </div>
            </div>
        </aside>

        {{-- Coluna de Preview --}}
        <main class="lg:col-span-7 flex flex-col items-center justify-center bg-zinc-50 dark:bg-zinc-900/50 rounded-[4rem] border-2 border-dashed border-zinc-200 dark:border-zinc-800 p-12 transition-colors duration-500">
            <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-400 mb-12 italic">{{ __('dashboard.badge.preview_title') }}</h4>

            {{-- O CARD REFINADO --}}
            <div id="badge-preview"
                 class="w-full max-w-md p-12 transition-all duration-500 shadow-[0_32px_64px_-15px_rgba(0,0,0,0.3)] relative overflow-hidden rounded-[3.5rem]"
                 style="
                    @if($form->theme === 'dark') background-color: #09090b; color: #ffffff; @endif
                    @if($form->theme === 'light') background-color: #ffffff; color: #09090b; border: 1px solid #f4f4f5; @endif
                    @if($form->theme === 'brand') background-color: {{ $this->user->profile->primary_color }}; color: #ffffff; @endif
                ">

                {{-- Watermark Decorativo --}}
                <div class="absolute -top-10 -right-10 opacity-[0.03] rotate-12 pointer-events-none">
                    <x-application-logo class="h-64 w-auto fill-current" />
                </div>

                <div class="absolute top-10 right-10 opacity-30">
                    <x-application-logo class="h-6 w-auto fill-current" />
                </div>

                {{-- Top Section --}}
                <div class="relative flex items-center gap-8">
                    <div class="relative shrink-0">
                        <div class="absolute -inset-2 bg-current opacity-10 blur-xl rounded-full"></div>
                        <img
                            src="{{ $this->user->profile->avatar_path ? Storage::url($this->user->profile->avatar_path) : 'https://ui-avatars.com/api/?name='.$this->user->display_name }}"
                            class="relative h-28 w-28 rounded-[2.5rem] object-cover ring-4 ring-current/5 shadow-2xl"
                            alt="Foto {{ $this->user->display_name }}"
                            crossorigin="anonymous"
                        />
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-3xl font-black tracking-tighter italic leading-none">{{ $this->user->display_name }}</h3>
                        <p class="text-sm font-bold opacity-50 tracking-tight">@<span></span>{{ $this->user->profile->username }}</p>
                    </div>
                </div>

                {{-- Bio Section --}}
                @if($form->showBio && $this->user->profile->bio)
                    <div class="relative mt-10">
                        <p class="text-base line-clamp-2 opacity-80 leading-relaxed italic font-medium tracking-tight">
                            "{{ $this->user->profile->bio }}"
                        </p>
                    </div>
                @endif

                {{-- Bottom Section --}}
                <div class="mt-12 pt-8 border-t border-current/10 flex items-center justify-between">
                    @if($form->showStats)
                        <div class="flex gap-10">
                            <div class="space-y-1">
                                <p class="text-3xl font-black leading-none tracking-tighter">{{ number_format($this->user->followers()->count()) }}</p>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] opacity-40">{{ __('dashboard.badge.stats.followers') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-3xl font-black leading-none tracking-tighter">{{ number_format($this->user->posts()->published()->count()) }}</p>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] opacity-40">{{ __('dashboard.badge.stats.posts') }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-2 px-6 py-4 bg-current/5 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-current/5 shadow-inner">
                        {{ __('dashboard.badge.action_read') }} <x-lucide-arrow-right class="h-3 w-3" />
                    </div>
                </div>
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
