{{--
    Banner de consentimento de cookies (LGPD).
    Cookies necessários estão sempre ativos. Analytics (Google Analytics) e
    Marketing (Google Ads / Meta Pixel) só são habilitados com consentimento.
--}}
<div
    x-data="{
        open: false,
        showPrefs: false,
        analytics: false,
        marketing: false,
        init() {
            const decided = window.DraftoConsent && window.DraftoConsent.decided();
            const current = window.DraftoConsent ? window.DraftoConsent.get() : null;

            if (current) {
                this.analytics = !!current.analytics;
                this.marketing = !!current.marketing;
            }

            this.open = !decided;

            window.addEventListener('drafto:open-consent', () => {
                this.showPrefs = true;
                this.open = true;
            });
        },
        acceptAll() { this.persist(true, true); },
        rejectAll() { this.persist(false, false); },
        savePrefs() { this.persist(this.analytics, this.marketing); },
        persist(analytics, marketing) {
            window.DraftoConsent.set({ analytics, marketing });
            this.open = false;
            this.showPrefs = false;
        }
    }"
    x-show="open"
    x-cloak
    x-transition.opacity.duration.300ms
    class="fixed inset-x-0 bottom-0 z-[100] p-4 sm:p-6"
    role="dialog"
    aria-live="polite"
    aria-label="Aviso de cookies"
>
    <style>[x-cloak]{display:none!important}</style>

    <div class="mx-auto max-w-3xl rounded-3xl border border-zinc-200 bg-white/95 p-6 shadow-2xl backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95">
        <div class="flex items-start gap-3">
            <div class="hidden shrink-0 rounded-2xl bg-indigo-50 p-2.5 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 sm:block">
                <x-lucide-cookie class="h-5 w-5" />
            </div>

            <div class="min-w-0 flex-1">
                <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Nós valorizamos sua privacidade</h2>
                <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                    Usamos cookies necessários para o funcionamento do site e, com sua autorização,
                    cookies de análise e de marketing para entender o uso e personalizar anúncios.
                    Você pode aceitar, recusar ou escolher. Saiba mais na nossa
                    <a href="{{ route('pages.privacy') }}" class="font-semibold text-indigo-600 underline hover:text-indigo-700 dark:text-indigo-400">Política de Privacidade</a>.
                </p>

                {{-- Preferências granulares --}}
                <div x-show="showPrefs" x-collapse class="mt-4 space-y-2">
                    <label class="flex cursor-not-allowed items-center justify-between gap-4 rounded-2xl bg-zinc-50 p-3 ring-1 ring-zinc-100 dark:bg-zinc-800/40 dark:ring-zinc-800">
                        <span class="min-w-0">
                            <span class="block text-xs font-bold text-zinc-800 dark:text-zinc-100">Necessários</span>
                            <span class="block text-[11px] text-zinc-500">Essenciais para login, segurança e preferências. Sempre ativos.</span>
                        </span>
                        <input type="checkbox" checked disabled class="h-4 w-4 shrink-0 rounded border-zinc-300 text-zinc-400">
                    </label>

                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl bg-zinc-50 p-3 ring-1 ring-zinc-100 dark:bg-zinc-800/40 dark:ring-zinc-800">
                        <span class="min-w-0">
                            <span class="block text-xs font-bold text-zinc-800 dark:text-zinc-100">Análise</span>
                            <span class="block text-[11px] text-zinc-500">Google Analytics e métricas de uso (visitas) para melhorar a plataforma.</span>
                        </span>
                        <input type="checkbox" x-model="analytics" class="h-4 w-4 shrink-0 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                    </label>

                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl bg-zinc-50 p-3 ring-1 ring-zinc-100 dark:bg-zinc-800/40 dark:ring-zinc-800">
                        <span class="min-w-0">
                            <span class="block text-xs font-bold text-zinc-800 dark:text-zinc-100">Marketing</span>
                            <span class="block text-[11px] text-zinc-500">Google Ads e Meta Pixel para anúncios e medição de campanhas.</span>
                        </span>
                        <input type="checkbox" x-model="marketing" class="h-4 w-4 shrink-0 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                    </label>
                </div>

                {{-- Ações --}}
                <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                    <button
                        type="button"
                        x-show="!showPrefs"
                        x-on:click="showPrefs = true"
                        class="order-3 inline-flex items-center justify-center rounded-2xl px-4 py-2.5 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800 sm:order-1 sm:mr-auto"
                    >
                        Personalizar
                    </button>

                    <button
                        type="button"
                        x-show="showPrefs"
                        x-on:click="savePrefs()"
                        class="order-3 inline-flex items-center justify-center rounded-2xl px-4 py-2.5 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800 sm:order-1 sm:mr-auto"
                    >
                        Salvar preferências
                    </button>

                    <button
                        type="button"
                        x-on:click="rejectAll()"
                        class="order-2 inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        Rejeitar
                    </button>

                    <button
                        type="button"
                        x-on:click="acceptAll()"
                        class="order-1 inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-indigo-700 sm:order-3"
                    >
                        Aceitar todos
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
