<div class="relative flex flex-col overflow-hidden rounded-3xl border border-zinc-200/70 bg-white animate-pulse dark:border-zinc-800 dark:bg-zinc-900">
    {{-- Mídia (mesma proporção e cantos do card real, evita layout shift) --}}
    <div class="aspect-[16/10] w-full rounded-t-3xl rounded-b-none bg-zinc-100 dark:bg-zinc-800"></div>

    <div class="flex flex-1 flex-col p-6 sm:p-8">
        {{-- Badges (categoria + tipo) --}}
        <div class="mb-4 flex items-center justify-between">
            <div class="h-5 w-20 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
            <div class="h-5 w-16 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
        </div>

        {{-- Título --}}
        <div class="mb-3 space-y-2">
            <div class="h-6 w-5/6 rounded-xl bg-zinc-200 dark:bg-zinc-700"></div>
        </div>

        {{-- Excerpt --}}
        <div class="mb-6 space-y-2">
            <div class="h-4 w-full rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
            <div class="h-4 w-4/6 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
        </div>

        {{-- Rodapé: autor + tempo de leitura --}}
        <div class="mt-auto flex items-center justify-between border-t border-zinc-100 pt-6 dark:border-zinc-800/60">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-2xl bg-zinc-200 dark:bg-zinc-700"></div>
                <div class="space-y-2">
                    <div class="h-3 w-20 rounded bg-zinc-200 dark:bg-zinc-700"></div>
                    <div class="h-2 w-12 rounded bg-zinc-100 dark:bg-zinc-800"></div>
                </div>
            </div>
            <div class="h-3 w-12 rounded bg-zinc-100 dark:bg-zinc-800"></div>
        </div>
    </div>
</div>
