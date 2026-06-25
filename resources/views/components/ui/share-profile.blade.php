@props(['user'])
@use(App\Actions\Profile\GenerateProfileQrCodeAction)
@php
    $username = $user->profile->username;
    $displayName = $user->display_name;
    $modalName = 'share-profile-' . $user->id;

    // Cacheia a URL de compartilhamento (lookup/insert de short link) e o SVG do QR
    // (render do BaconQrCode) por 60min, sob a mesma tag invalidada ao atualizar o perfil.
    $cacheTags = ['profiles', 'profile_' . $username];
    $shareUrl = cache()->tags($cacheTags)->remember(
        "profile_share_url_{$username}",
        now()->addMinutes(60),
        fn () => $user->getShareUrl(),
    );
    $qrCodeSvg = cache()->tags($cacheTags)->remember(
        "profile_qr_svg_{$username}",
        now()->addMinutes(60),
        fn () => app(GenerateProfileQrCodeAction::class)->svg($user),
    );
    $qrDownloadUrl = route('public.profile.qrcode', $username);

    $whatsappText = rawurlencode(__('public.share.whatsapp_text', ['name' => $displayName, 'url' => $shareUrl]));
    $whatsappUrl = "https://wa.me/?text={$whatsappText}";
@endphp

<div>
    <button
        @click="$dispatch('open-modal', { name: '{{ $modalName }}' })"
        type="button"
        class="flex h-12 w-12 items-center justify-center rounded-profile-button border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-profile-primary shadow-sm transition hover:bg-zinc-50 dark:hover:bg-zinc-800 active:scale-95"
        title="{{ __('public.share.title') }}"
    >
        <x-lucide-share-2 class="h-5 w-5 text-profile-primary" />
    </button>

    <x-ui.modal :name="$modalName" :title="__('public.share.title')">
        <div
            class="space-y-8"
            x-data="{
                copied: false,
                downloading: false,
                copyLink() {
                    navigator.clipboard.writeText('{{ $shareUrl }}');
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                    $dispatch('notify', { message: '{{ __('public.share.copy_success') }}', type: 'success' });
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
                        link.download = 'drafto-qrcode-{{ $username }}.png';
                        link.click();
                        URL.revokeObjectURL(url);
                    } catch (error) {
                        $dispatch('notify', { message: '{{ __('public.share.qr_error') }}', type: 'error' });
                    } finally {
                        this.downloading = false;
                    }
                }
            }"
        >
            <p class="text-sm font-medium text-zinc-500">
                {!! __('public.share.intro', ['username' => '<span class="font-bold text-zinc-900">@' . e($username) . '</span>']) !!}
            </p>

            {{-- QR Code --}}
            <div class="flex flex-col items-center gap-3">
                <div class="h-44 w-44 rounded-[2rem] border border-zinc-100 bg-white p-5 shadow-sm [&>svg]:h-full [&>svg]:w-full" role="img" aria-label="{{ __('public.share.qr_alt', ['name' => $displayName]) }}">
                    {!! $qrCodeSvg !!}
                </div>
                <button
                    type="button"
                    @click="downloadQr"
                    :disabled="downloading"
                    class="flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-xs font-black uppercase tracking-widest text-zinc-700 transition-all hover:bg-zinc-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <template x-if="!downloading">
                        <span class="flex items-center gap-1.5"><x-lucide-download class="h-3.5 w-3.5" /> {{ __('public.share.qr_download') }}</span>
                    </template>
                    <template x-if="downloading">
                        <span class="flex items-center gap-1.5"><x-lucide-loader-2 class="h-3.5 w-3.5 animate-spin" /> {{ __('public.share.qr_downloading') }}</span>
                    </template>
                </button>
                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('public.share.qr_hint') }}</span>
            </div>

            {{-- Link copiável --}}
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('public.share.link_label') }}</label>
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
                            <span class="flex items-center gap-1.5"><x-lucide-copy class="h-3.5 w-3.5" /> {{ __('public.share.copy') }}</span>
                        </template>
                        <template x-if="copied">
                            <span class="flex items-center gap-1.5"><x-lucide-check class="h-3.5 w-3.5" /> {{ __('public.share.copied') }}</span>
                        </template>
                    </button>
                </div>
            </div>

            {{-- WhatsApp --}}
            <a
                href="{{ $whatsappUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-[#25D366] px-6 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-emerald-500/20 transition-all hover:bg-[#1ebe57] active:scale-95"
            >
                <x-lucide-message-circle class="h-5 w-5" />
                {{ __('public.share.whatsapp') }}
            </a>
        </div>
    </x-ui.modal>
</div>
