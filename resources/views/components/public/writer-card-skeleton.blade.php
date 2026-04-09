<div class="group relative flex flex-col justify-between rounded-[3rem] border border-zinc-100 bg-white p-8 animate-pulse dark:bg-zinc-900 dark:border-zinc-800">
    <div>
        {{-- Avatar Skeleton --}}
        <div class="relative mx-auto w-24 h-24 mb-6">
            <div class="absolute inset-0 bg-zinc-100 dark:bg-zinc-800 rounded-[2rem] rotate-6"></div>
            <div class="relative h-full w-full rounded-[2rem] bg-zinc-200 dark:bg-zinc-700 ring-4 ring-white dark:ring-zinc-900 shadow-sm"></div>
        </div>

        {{-- Info Skeleton --}}
        <div class="text-center space-y-3">
            <div class="h-5 w-3/4 mx-auto rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
            <div class="h-3 w-1/2 mx-auto rounded-md bg-profile-primary/10"></div>
        </div>

        {{-- Bio Skeleton --}}
        <div class="mt-5 text-center space-y-2">
            <div class="h-3 w-full rounded-md bg-zinc-100 dark:bg-zinc-800"></div>
            <div class="h-3 w-5/6 mx-auto rounded-md bg-zinc-100 dark:bg-zinc-800"></div>
        </div>

        {{-- Stats Skeleton --}}
        <div class="mt-6 flex items-center justify-around py-5 border-t border-zinc-50 dark:border-zinc-800">
            <div class="space-y-2 text-center flex flex-col items-center">
                <div class="h-4 w-10 rounded bg-zinc-200 dark:bg-zinc-700"></div>
                <div class="h-2 w-12 rounded bg-zinc-100 dark:bg-zinc-800"></div>
            </div>
            <div class="h-6 w-px bg-zinc-100 dark:bg-zinc-800"></div>
            <div class="space-y-2 text-center flex flex-col items-center">
                <div class="h-4 w-10 rounded bg-zinc-200 dark:bg-zinc-700"></div>
                <div class="h-2 w-12 rounded bg-zinc-100 dark:bg-zinc-800"></div>
            </div>
        </div>
    </div>

    {{-- Ações Skeleton --}}
    <div class="mt-6 grid grid-cols-5 gap-2">
        <div class="col-span-4 h-11 rounded-xl bg-zinc-200 dark:bg-zinc-700"></div>
        <div class="col-span-1 h-11 rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
    </div>
</div>
