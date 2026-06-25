<footer class="border-t border-zinc-100 py-16 dark:border-zinc-800 transition-colors duration-300">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-8 md:flex-row">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/favicon/android-chrome-192x192.png') }}" class="h-8 w-auto grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all" alt="Drafto Logo">
            </div>

            <p class="text-sm text-zinc-400 dark:text-zinc-500 text-center">
                &copy; {{ date('Y') }} Drafto Platform. Feito para quem ama as palavras.
            </p>

            <x-ui.social-links context="footer" />
        </div>

        <nav class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-zinc-400 dark:text-zinc-500">
            <a href="{{ route('pages.privacy') }}" class="transition hover:text-zinc-700 dark:hover:text-zinc-300">Política de Privacidade</a>
            <a href="{{ route('pages.terms') }}" class="transition hover:text-zinc-700 dark:hover:text-zinc-300">Termos de Uso</a>
            <a href="{{ route('pages.guidelines') }}" class="transition hover:text-zinc-700 dark:hover:text-zinc-300">Diretrizes</a>
            <button type="button" onclick="window.draftoOpenConsent && window.draftoOpenConsent()" class="transition hover:text-zinc-700 dark:hover:text-zinc-300">Preferências de cookies</button>
        </nav>
    </div>
</footer>
