@use(App\Enums\RoleEnum)
<div class="space-y-8">
    <x-dashboard.verification-banner />

    <section class="flex flex-col gap-4 rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-8">
        <div>
            <p class="text-sm font-medium text-zinc-500">Bem-vindo de volta</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-zinc-900">Olá, {{ $this->user->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600">Acompanhe sua produção e atividade em um só lugar.</p>
        </div>

        @if($this->user->hasRole(RoleEnum::WRITER))
            <div class="flex flex-wrap items-center gap-3">
                <a href="#" class="inline-flex items-center gap-2 rounded-2xl bg-zinc-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800">
                    <x-lucide-square-pen class="h-4 w-4" /> Novo conteúdo
                </a>
            </div>
        @endif
    </section>

    <livewire:dashboard.widgets.stats-overview lazy />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <livewire:dashboard.widgets.recent-activity lazy />
        </div>

        <div>
            <livewire:dashboard.widgets.profile-info />
        </div>
    </div>
</div>
