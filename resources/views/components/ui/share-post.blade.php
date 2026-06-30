@props(['post'])
@use(App\Actions\Profile\GenerateProfileQrCodeAction)
@php
    $title = $post->title;
    $modalName = 'share-post-' . $post->id;

    // Cacheia a URL de compartilhamento (lookup/insert de short link) e o SVG do QR
    // (render do BaconQrCode) por 60min, sob a mesma tag invalidada ao atualizar o post.
    $cacheTags = ['posts', 'post_' . $post->id];
    $shareUrl = cache()->tags($cacheTags)->remember(
        "post_share_url_{$post->id}",
        now()->addMinutes(60),
        fn () => $post->getShareUrl(),
    );
    $qrCodeSvg = cache()->tags($cacheTags)->remember(
        "post_qr_svg_{$post->id}",
        now()->addMinutes(60),
        fn () => app(GenerateProfileQrCodeAction::class)->svgFromUrl($shareUrl),
    );
    $qrDownloadUrl = route('public.posts.qrcode', $post->slug);

    $whatsappText = rawurlencode(__('public.share_post.whatsapp_text', ['url' => $shareUrl]));
    $whatsappUrl = "https://wa.me/?text={$whatsappText}";
    $facebookUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($shareUrl);
@endphp

<div>
    <x-ui.tooltip :text="__('public.share_post.title')">
        <button
            @click="$dispatch('open-modal', { name: '{{ $modalName }}' })"
            type="button"
            aria-label="{{ __('public.share_post.title') }}"
            class="group flex h-11 w-11 items-center justify-center rounded-2xl border border-zinc-200 bg-white shadow-sm transition-all hover:border-profile-primary hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-profile-primary dark:hover:bg-zinc-800"
        >
            <x-lucide-share-2 class="h-5 w-5 text-zinc-600 transition-colors group-hover:text-profile-primary dark:text-zinc-400" aria-hidden="true" />
        </button>
    </x-ui.tooltip>

    <x-ui.modal :name="$modalName" :title="__('public.share_post.title')">
        <div
            class="space-y-8"
            x-data="{
                copied: false,
                downloading: false,
                copyLink() {
                    navigator.clipboard.writeText('{{ $shareUrl }}');
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                    $dispatch('notify', { message: '{{ __('public.share_post.copy_success') }}', type: 'success' });
                },
                shareInstagram() {
                    // O Instagram não possui URL de compartilhamento web; copiamos o link
                    // para o usuário colar no story/bio e tentamos o share nativo no mobile.
                    if (navigator.share) {
                        navigator.share({ title: @js($title), url: '{{ $shareUrl }}' }).catch(() => {});
                        return;
                    }
                    navigator.clipboard.writeText('{{ $shareUrl }}');
                    $dispatch('notify', { message: '{{ __('public.share_post.instagram_copy') }}', type: 'success' });
                    window.open('https://www.instagram.com/', '_blank', 'noopener');
                },
                async downloadQr() {
                    if (this.downloading) return;
                    this.downloading = true;
                    try {
                        const response = await fetch('{{ $qrDownloadUrl }}');
                        if (!response.ok) throw new Error('request failed');

                        const blob = await response.blob();
                        const url = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'drafto-qrcode-{{ $post->slug }}.png';
                        link.click();
                        URL.revokeObjectURL(url);
                    } catch (error) {
                        $dispatch('notify', { message: '{{ __('public.share_post.qr_error') }}', type: 'error' });
                    } finally {
                        this.downloading = false;
                    }
                }
            }"
        >
            <p class="text-sm font-medium text-zinc-500">
                {!! __('public.share_post.intro', ['title' => '<span class="font-bold text-zinc-900">' . e($title) . '</span>']) !!}
            </p>

            {{-- QR Code --}}
            <div class="flex flex-col items-center gap-3">
                <div class="h-44 w-44 rounded-[2rem] border border-zinc-100 bg-white p-5 shadow-sm [&>svg]:h-full [&>svg]:w-full" role="img" aria-label="{{ __('public.share_post.qr_alt', ['title' => $title]) }}">
                    {!! $qrCodeSvg !!}
                </div>
                <button
                    type="button"
                    @click="downloadQr"
                    :disabled="downloading"
                    class="flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-xs font-black uppercase tracking-widest text-zinc-700 transition-all hover:bg-zinc-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <template x-if="!downloading">
                        <span class="flex items-center gap-1.5"><x-lucide-download class="h-3.5 w-3.5" /> {{ __('public.share_post.qr_download') }}</span>
                    </template>
                    <template x-if="downloading">
                        <span class="flex items-center gap-1.5"><x-lucide-loader-2 class="h-3.5 w-3.5 animate-spin" /> {{ __('public.share_post.qr_downloading') }}</span>
                    </template>
                </button>
                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('public.share_post.qr_hint') }}</span>
            </div>

            {{-- Link copiável --}}
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('public.share_post.link_label') }}</label>
                <div class="flex items-center gap-2 rounded-2xl border border-zinc-200 bg-zinc-50 p-2 pl-4">
                    <input
                        type="text"
                        readonly
                        value="{{ $shareUrl }}"
                        @click="$el.select()"
                        class="min-w-0 flex-1 truncate bg-transparent text-sm font-medium text-zinc-700 outline-none"
                    />
                    <button
                        type="button"
                        @click="copyLink"
                        class="flex shrink-0 items-center gap-1.5 rounded-xl px-4 py-2.5 text-xs font-black uppercase tracking-widest transition-all active:scale-95"
                        :class="copied ? 'bg-emerald-500 text-white' : 'bg-zinc-900 text-white hover:bg-zinc-700'"
                    >
                        <template x-if="!copied">
                            <span class="flex items-center gap-1.5"><x-lucide-copy class="h-3.5 w-3.5" /> {{ __('public.share_post.copy') }}</span>
                        </template>
                        <template x-if="copied">
                            <span class="flex items-center gap-1.5"><x-lucide-check class="h-3.5 w-3.5" /> {{ __('public.share_post.copied') }}</span>
                        </template>
                    </button>
                </div>
            </div>

            {{-- Redes sociais --}}
            <div class="space-y-3">
                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('public.share_post.social_label') }}</span>
                <div class="grid grid-cols-3 gap-3">
                    {{-- WhatsApp --}}
                    <a
                        href="{{ $whatsappUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="{{ __('public.share_post.whatsapp_full') }}"
                        class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-[#25D366] px-4 py-4 text-[11px] font-black uppercase tracking-widest text-white shadow-lg shadow-emerald-500/20 transition-all hover:bg-[#1ebe57] active:scale-95"
                    >
                        <x-lucide-message-circle class="h-6 w-6" />
                        {{ __('public.share_post.whatsapp') }}
                    </a>

                    {{-- Facebook --}}
                    <a
                        href="{{ $facebookUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="{{ __('public.share_post.facebook') }}"
                        class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-[#1877F2] px-4 py-4 text-[11px] font-black uppercase tracking-widest text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-[#0f63d6] active:scale-95"
                    >
                        <x-lucide-facebook class="h-6 w-6" />
                        {{ __('public.share_post.facebook') }}
                    </a>

                    {{-- Instagram --}}
                    <button
                        type="button"
                        @click="shareInstagram"
                        aria-label="{{ __('public.share_post.instagram') }}"
                        class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-[#feda75] via-[#d62976] to-[#4f5bd5] px-4 py-4 text-[11px] font-black uppercase tracking-widest text-white shadow-lg shadow-pink-500/20 transition-all hover:opacity-90 active:scale-95"
                    >
                        <x-lucide-instagram class="h-6 w-6" />
                        {{ __('public.share_post.instagram') }}
                    </button>
                </div>
            </div>
        </div>
    </x-ui.modal>
</div>
