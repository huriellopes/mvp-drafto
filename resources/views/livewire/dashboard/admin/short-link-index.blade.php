<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            {{ Breadcrumbs::render('dashboard.admin.short-links.index') }}
        </div>

        <div class="flex items-center gap-3">
            @if($this->isFileReady)
                <div class="flex items-center gap-2 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 px-4 py-2 rounded-2xl animate-in fade-in slide-in-from-right-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Pronto!</span>
                    <a 
                        href="{{ route('dashboard.temporary-file.download', ['path' => $generatedPath]) }}" 
                        wire:click="clearGeneratedFile"
                        class="flex h-8 items-center gap-2 rounded-xl bg-emerald-600 px-3 text-[10px] font-bold text-white transition hover:bg-emerald-700 shadow-sm"
                    >
                        <x-lucide-download class="h-3 w-3" />
                        Baixar
                    </a>
                    <button wire:click="clearGeneratedFile" class="text-emerald-400 hover:text-emerald-600">
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>
            @elseif($generatedPath)
                <div wire:poll.1s class="flex items-center gap-3 px-4 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 animate-pulse">
                    <x-lucide-loader-2 class="h-4 w-4 animate-spin text-zinc-400" />
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500">Gerando...</span>
                </div>
            @else
                <x-ui.button wire:click="export" class="sm:w-auto px-6" variant="secondary">
                    <x-lucide-download class="mr-2 h-4 w-4" />
                    Exportar Excel
                </x-ui.button>
            @endif
        </div>
    </div>

    {{-- Stats Globais --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="relative overflow-hidden rounded-[2.5rem] border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-lucide-link class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Total de Links</p>
                    <p class="text-2xl font-black text-zinc-900 dark:text-white">{{ number_format($this->globalStats['total_links']) }}</p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-5">
                <x-lucide-link class="h-24 w-24" />
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[2.5rem] border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <x-lucide-mouse-pointer-click class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Cliques Globais</p>
                    <p class="text-2xl font-black text-zinc-900 dark:text-white">{{ number_format($this->globalStats['total_clicks']) }}</p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-5">
                <x-lucide-mouse-pointer-click class="h-24 w-24" />
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <x-ui.section-card title="Filtros de Pesquisa">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <x-ui.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por código ou usuário..."
                >
                    <x-slot:prefix>
                        <x-lucide-search class="h-4 w-4 text-zinc-400" />
                    </x-slot:prefix>
                </x-ui.input>
            </div>
        </div>
    </x-ui.section-card>

    {{-- Tabela --}}
    <div class="overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-800/50">
                        <th class="px-6 py-4 font-black uppercase tracking-widest text-zinc-500 text-[10px]">Código</th>
                        <th class="px-6 py-4 font-black uppercase tracking-widest text-zinc-500 text-[10px]">Usuário</th>
                        <th class="px-6 py-4 font-black uppercase tracking-widest text-zinc-500 text-[10px]">Tipo / Destino</th>
                        <th class="px-6 py-4 font-black uppercase tracking-widest text-zinc-500 text-[10px] text-center">Cliques</th>
                        <th class="px-6 py-4 font-black uppercase tracking-widest text-zinc-500 text-[10px] text-right">Criado em</th>
                        <th class="px-6 py-4 font-black uppercase tracking-widest text-zinc-500 text-[10px] text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($this->links as $link)
                        <tr class="group transition-colors hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" wire:key="link-{{ $link->id }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $link->code }}</span>
                                    <button 
                                        x-on:click="navigator.clipboard.writeText('{{ route('shortlink.redirect', $link->code) }}'); $dispatch('toaster:success', { message: 'Link copiado!' })"
                                        class="opacity-0 group-hover:opacity-100 p-1 text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition"
                                    >
                                        <x-lucide-copy class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-bold text-zinc-500">
                                        {{ substr($link->user->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-zinc-900 dark:text-white">{{ $link->user->name }}</span>
                                        <span class="text-[10px] text-zinc-500">@ {{ $link->user->profile->username }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5">
                                        @if($link->shortable_type === \App\Models\Post::class)
                                            <x-lucide-file-text class="h-3.5 w-3.5 text-zinc-400" />
                                            <span class="text-[11px] font-bold text-zinc-600 dark:text-zinc-400 uppercase">Post</span>
                                        @else
                                            <x-lucide-user class="h-3.5 w-3.5 text-zinc-400" />
                                            <span class="text-[11px] font-bold text-zinc-600 dark:text-zinc-400 uppercase">Perfil</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-zinc-500 truncate max-w-[200px]" title="{{ $link->shortable?->title ?? $link->shortable?->profile?->username }}">
                                        {{ $link->shortable?->title ?? $link->shortable?->profile?->username }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span @class([
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold',
                                    'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-400' => $link->clicks > 0,
                                    'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-500' => $link->clicks === 0
                                ])>
                                    {{ number_format($link->clicks) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-zinc-500">
                                {{ $link->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button 
                                    wire:click="confirmDeletion({{ $link->id }})"
                                    class="p-2 text-zinc-400 hover:text-red-600 transition-colors"
                                >
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                                Nenhum link encurtado encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->links->hasPages())
            <div class="border-t border-zinc-100 bg-zinc-50/30 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                {{ $this->links->links() }}
            </div>
        @endif
    </div>

    <x-ui.confirm-modal
        name="confirm-link-deletion"
        title="Excluir Link Encurtado"
        content="Tem certeza que deseja excluir este link? O redirecionamento deixará de funcionar imediatamente para todos que utilizarem este código."
        buttonText="Sim, Excluir"
        variant="danger"
        action="delete"
    />
</div>
