{{-- Imita o layout exato da página real, mas sem dados dinâmicos --}}
<div class="max-w-7xl mx-auto px-4 py-12 lg:py-20 space-y-16 animate-in fade-in duration-500">
    {{-- Header Estático (Já renderiza pronto) --}}
    <div class="text-center space-y-6 max-w-2xl mx-auto">
        <h1 class="text-5xl md:text-7xl font-black text-zinc-900 dark:text-white tracking-tighter italic opacity-60">
            Descubra novos Escritores.
        </h1>
        <p class="text-zinc-400 font-medium">Carregando mentes brilhantes...</p>

        {{-- Input desabilitado no placeholder --}}
        <div class="pt-4 opacity-50 pointer-events-none">
            <x-ui.input placeholder="Buscar por nome ou @username..." disabled>
                <x-slot:prefix><x-lucide-search class="h-5 w-5 text-zinc-300" /></x-slot:prefix>
            </x-ui.input>
        </div>
    </div>

    {{-- Grid de Skeletons --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        {{-- Sênior: Renderizamos 8 skeletons para preencher a tela inicial --}}
        @for($i = 0; $i < 8; $i++)
            <x-public.writer-card-skeleton />
        @endfor
    </div>
</div>
