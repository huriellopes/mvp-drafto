<footer class="border-t border-zinc-100 py-16 dark:border-zinc-800 transition-colors duration-300">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-8 md:flex-row">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/favicon/android-chrome-192x192.png') }}" class="h-8 w-auto grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all" alt="Drafto Logo">
            </div>

            <p class="text-sm text-zinc-400 dark:text-zinc-500 text-center">
                &copy; {{ date('Y') }} Drafto Platform. Feito para quem ama as palavras.
            </p>

            <div class="flex gap-6">
                <a href="#" class="text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition"><x-lucide-instagram class="h-5 w-5" /></a>
                <a href="#" class="text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition"><x-lucide-twitter class="h-5 w-5" /></a>
                <a href="#" class="text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition"><x-lucide-github class="h-5 w-5" /></a>
            </div>
        </div>
    </div>
</footer>
