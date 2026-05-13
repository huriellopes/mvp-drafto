@use(App\Enums\RoleEnum)
@props(['user'])

<section {{ $attributes->merge(['class' => 'flex flex-col gap-4 rounded-[2.5rem] border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-8 transition-all']) }}>
    <div class="space-y-1">
        <div class="flex items-center gap-2">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-profile-primary/60 dark:text-zinc-500">
                {{ $user->greeting() }}
            </p>
            <span class="h-1 w-1 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
            <p class="text-xs font-bold text-zinc-400">Drafto Dashboard</p>
        </div>

        <h1 class="text-3xl font-black tracking-tighter text-zinc-900 dark:text-white sm:text-4xl">
            Olá, {{ $user->display_name }}!
        </h1>

        <p class="max-w-xl text-sm font-medium leading-relaxed text-zinc-500 dark:text-zinc-400">
            {{ __('dashboard.welcome.subtitle') }}
        </p>
    </div>

    @if($user->hasRole(RoleEnum::WRITER) || $user->hasRole(RoleEnum::SUPER_ADMIN))
        <div class="flex shrink-0 items-center gap-3">
            <x-ui.button href="{{ route('dashboard.posts.create') }}" variant="primary" size="lg" class="shadow-xl shadow-zinc-900/10">
                <x-lucide-square-pen class="h-4 w-4 mr-2" />
                {{ __('dashboard.welcome.new_content') }}
            </x-ui.button>
        </div>
    @endif
</section>
