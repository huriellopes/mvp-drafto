<div class="space-y-8 p-8 transition-all duration-500">
    {{-- Header --}}
    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
        <div>
            {{ Breadcrumbs::render('dashboard.modules.index') }}
            <header class="mt-4 space-y-1">
                <h1 class="text-4xl font-black tracking-tighter text-zinc-900 dark:text-white uppercase">Gestão de Módulos</h1>
                <p class="text-zinc-500 font-medium italic">Ative ou desative ferramentas do ecossistema Drafto.</p>
            </header>
        </div>

        {{-- Badge de Contagem Total --}}
        <div class="hidden md:block">
            <div class="bg-zinc-100 dark:bg-zinc-800 px-4 py-2 rounded-2xl border border-zinc-200 dark:border-zinc-700">
                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Total de Módulos: </span>
                <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $this->modules->total() }}</span>
            </div>
        </div>
    </div>

    {{-- Toolbar de Filtros --}}
    <section class="flex flex-col lg:flex-row gap-4 items-center bg-white dark:bg-zinc-900 p-4 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 shadow-sm transition-all">
        {{-- Busca --}}
        <div class="flex-1 w-full relative">
            <x-ui.input
                wire:model.live.debounce.400ms="search"
                placeholder="Filtrar por nome ou slug..."
                class="!bg-zinc-50 dark:!bg-zinc-950 rounded-full border-none shadow-none focus-within:ring-0"
            >
                <x-slot:prefix>
                    <div class="pl-2 pr-1">
                        <x-lucide-search class="h-4 w-4 text-zinc-400" />
                    </div>
                </x-slot:prefix>
            </x-ui.input>
        </div>

        {{-- Controles de Visualização --}}
        <div class="flex items-center gap-4 w-full lg:w-auto px-2">
            <div class="h-8 w-px bg-zinc-200 dark:bg-zinc-800 hidden lg:block"></div>

            {{-- Seletor de Quantidade --}}
            <div class="flex items-center gap-2 min-w-[140px]">
                <span class="text-[9px] font-black uppercase tracking-widest text-zinc-400">Exibir:</span>
                <x-ui.select wire:model.live="perPage" class="!bg-transparent border-none text-[10px] font-bold text-zinc-900 dark:text-white uppercase cursor-pointer">
                    <option value="6">06 Itens</option>
                    <option value="12">12 Itens</option>
                    <option value="24">24 Itens</option>
                    <option value="all">Ver Todos</option>
                </x-ui.select>
            </div>

            <div class="h-8 w-px bg-zinc-200 dark:bg-zinc-800 hidden lg:block"></div>

            {{-- Ordenação --}}
            <div class="flex items-center gap-2 min-w-[140px]">
                <span class="text-[9px] font-black uppercase tracking-widest text-zinc-400">Ordem:</span>
                <x-ui.select wire:model.live="sortDirection" class="!bg-transparent border-none text-[10px] font-bold text-zinc-900 dark:text-white uppercase cursor-pointer">
                    <option value="asc">A a Z</option>
                    <option value="desc">Z a A</option>
                </x-ui.select>
            </div>
        </div>
    </section>

    {{-- Grid de Módulos --}}
    <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
        wire:loading.class="opacity-50 blur-[2px] transition-all duration-500"
    >
        @forelse($this->modules as $module)
            <x-dashboard.admin.module-card :module="$module" />
        @empty
            <div class="col-span-full py-20">
                <x-ui.empty-state
                    title="Nenhum módulo encontrado"
                    description="Não encontramos nenhum módulo com o termo '{{ $search }}'."
                />
            </div>
        @endforelse
    </div>

    {{-- Paginação Estilizada --}}
    @if($this->modules->hasPages())
        <div class="pt-8 mt-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $this->modules->links() }}
        </div>
    @endif
</div>
