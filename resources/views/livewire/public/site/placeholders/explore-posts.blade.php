<div class="max-w-7xl mx-auto px-4 py-16 lg:py-24 animate-in fade-in duration-500">
    <div class="flex flex-col lg:flex-row gap-16">
        {{-- Sidebar Estática (Skeleton) --}}
        <aside class="w-full lg:w-72 space-y-12 opacity-50 pointer-events-none">
            <div class="h-20 w-full bg-zinc-100 dark:bg-zinc-900 rounded-3xl"></div>
            <div class="space-y-4">
                <div class="h-4 w-20 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                @for($i = 0; $i < 5; $i++)
                    <div class="h-10 w-full bg-zinc-100 dark:bg-zinc-900 rounded-2xl"></div>
                @endfor
            </div>
        </aside>

        {{-- Content Area --}}
        <div class="flex-1 space-y-12">
            <div class="flex flex-col sm:flex-row justify-between border-b border-zinc-100 dark:border-zinc-800 pb-8 gap-6">
                <div class="space-y-3">
                    <div class="h-12 w-48 bg-zinc-200 dark:bg-zinc-700 rounded-2xl"></div>
                    <div class="h-4 w-32 bg-zinc-100 dark:bg-zinc-800 rounded-full"></div>
                </div>
            </div>

            {{-- Grid de Skeletons --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                @for($i = 0; $i < 6; $i++)
                    <x-public.post-card-skeleton />
                @endfor
            </div>
        </div>
    </div>
</div>
