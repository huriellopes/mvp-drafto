<div class="space-y-10 pb-20">
    {{-- Header com Breadcrumbs e Stats --}}
    <div class="flex flex-col gap-8 md:flex-row md:items-end justify-between">
        <div class="space-y-2">
            {{ Breadcrumbs::render('dashboard.short-links.index') }}
            <h2 class="text-3xl font-black italic text-zinc-900 dark:text-white uppercase tracking-tighter">Performance de Links</h2>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:flex sm:items-center">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm min-w-[140px]">
                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Total Cliques</p>
                <p class="text-2xl font-black text-indigo-600">{{ number_format($this->stats['total_clicks']) }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm min-w-[140px]">
                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Links Criados</p>
                <p class="text-2xl font-black text-zinc-900 dark:text-white">{{ number_format($this->stats['total_links']) }}</p>
            </div>
        </div>
    </div>

    {{-- Seção de Configuração Granular --}}
    <x-ui.section-card title="Configurações do Encurtador" description="Escolha onde deseja aplicar o encurtamento automático.">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div @class([
                'flex items-center justify-between p-6 rounded-[2rem] border transition-all duration-500',
                $enableForProfile ? 'bg-zinc-900 border-zinc-800 text-white shadow-xl shadow-zinc-200/50' : 'bg-zinc-50 border-zinc-100 text-zinc-500'
            ])>
                <div class="flex items-center gap-4">
                    <div @class([
                        'p-3 rounded-2xl',
                        $enableForProfile ? 'bg-white/10 text-white' : 'bg-white text-zinc-300'
                    ])>
                        <x-lucide-user-circle class="h-6 w-6" />
                    </div>
                    <div>
                        <span class="block text-sm font-black uppercase tracking-tight">Links de Perfil</span>
                        <p @class(['text-[10px] font-medium', $enableForProfile ? 'text-zinc-400' : 'text-zinc-400'])>Encurtar link ao compartilhar perfil.</p>
                    </div>
                </div>
                <x-ui.checkbox wire:model.live="enableForProfile" />
            </div>

            <div @class([
                'flex items-center justify-between p-6 rounded-[2rem] border transition-all duration-500',
                $enableForPosts ? 'bg-zinc-900 border-zinc-800 text-white shadow-xl shadow-zinc-200/50' : 'bg-zinc-50 border-zinc-100 text-zinc-500'
            ])>
                <div class="flex items-center gap-4">
                    <div @class([
                        'p-3 rounded-2xl',
                        $enableForPosts ? 'bg-white/10 text-white' : 'bg-white text-zinc-300'
                    ])>
                        <x-lucide-file-text class="h-6 w-6" />
                    </div>
                    <div>
                        <span class="block text-sm font-black uppercase tracking-tight">Links de Posts</span>
                        <p @class(['text-[10px] font-medium', $enableForPosts ? 'text-zinc-400' : 'text-zinc-400'])>Encurtar links em todos os seus artigos.</p>
                    </div>
                </div>
                <x-ui.checkbox wire:model.live="enableForPosts" />
            </div>
        </div>
    </x-ui.section-card>

    {{-- Tabela de Links e Performance --}}
    <x-ui.section-card title="Meus Links Ativos" description="Acompanhe o engajamento de cada link compartilhado.">
        <div class="overflow-hidden">
            <div class="mb-6">
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Buscar por código...">
                    <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
                </x-ui.input>
            </div>

            <div class="overflow-x-auto -mx-6 sm:mx-0">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400">
                            <th class="px-6 py-4">Link Curto</th>
                            <th class="px-6 py-4">Destino Original</th>
                            <th class="px-6 py-4 text-center">Cliques</th>
                            <th class="px-6 py-4 text-right">Criado em</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                        @forelse($this->links as $link)
                            <tr class="group transition-all hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="bg-indigo-50 text-indigo-600 p-2 rounded-xl font-mono font-bold text-xs">
                                            {{ $link->code }}
                                        </div>
                                        <button 
                                            x-on:click="navigator.clipboard.writeText('{{ route('shortlink.redirect', $link->code) }}'); $dispatch('toaster:success', { message: 'Copiado para o clipboard!' })"
                                            class="p-2 rounded-lg text-zinc-400 hover:bg-white hover:text-zinc-900 shadow-sm transition opacity-0 group-hover:opacity-100"
                                        >
                                            <x-lucide-copy class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        @if($link->shortable_type === \App\Models\Post::class)
                                            <x-lucide-file-text class="h-3.5 w-3.5 text-zinc-300" />
                                        @else
                                            <x-lucide-user-circle class="h-3.5 w-3.5 text-zinc-300" />
                                        @endif
                                        <span class="text-zinc-600 dark:text-zinc-400 font-medium truncate max-w-[200px]">
                                            {{ $link->shortable?->title ?? $link->shortable?->profile?->username ?? 'Link Destino' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="text-sm font-black text-zinc-900 dark:text-white">{{ number_format($link->clicks) }}</span>
                                </td>
                                <td class="px-6 py-5 text-right text-xs text-zinc-400">
                                    {{ $link->created_at->translatedFormat('d M, Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2 text-zinc-400">
                                        <x-lucide-link-2 class="h-8 w-8 opacity-20" />
                                        <p class="text-xs font-bold uppercase tracking-widest">Nenhum link gerado ainda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $this->links->links() }}
            </div>
        </div>
    </x-ui.section-card>
</div>
