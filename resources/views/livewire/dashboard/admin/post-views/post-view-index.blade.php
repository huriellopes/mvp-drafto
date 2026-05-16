<div class="space-y-6 pb-20">
    {{ Breadcrumbs::render('dashboard.posts.views') }}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <x-ui.input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por post ou IP..."
            class="max-w-md"
        >
            <x-slot:prefix><x-lucide-search class="h-4 w-4" /></x-slot:prefix>
        </x-ui.input>

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
                        Baixar Excel
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
                <x-ui.button
                    variant="primary"
                    wire:click="export"
                    class="!rounded-2xl"
                    size="sm"
                >
                    <x-lucide-file-spreadsheet class="h-4 w-4 mr-2" /> Excel
                </x-ui.button>
            @endif
        </div>
    </div>

    <x-ui.table>
        <x-slot:header>
            <x-ui.table.th label="Data/Hora" column="viewed_at" :$sort :$direction />
            <x-ui.table.th label="Post" />
            <x-ui.table.th label="Viewer" />
            <x-ui.table.th label="Endereço IP" />
            <x-ui.table.th label="Ações" align="right" />
        </x-slot:header>

        @foreach($this->views as $view)
            <tr wire:key="view-{{ $view->id }}" class="hover:bg-zinc-50/50 transition">
                <td class="px-6 py-4 text-zinc-600 font-medium">
                    {{ $view->viewed_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-6 py-4">
                    <span class="font-bold text-zinc-900 truncate block max-w-xs">{{ $view->post->title }}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-col">
                        <span class="text-zinc-900 font-medium">{{ $view->user->name ?? 'Anônimo' }}</span>
                        <span class="text-[10px] text-zinc-400 truncate max-w-[150px]">{{ $view->user_agent }}</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="rounded-lg bg-zinc-100 px-2 py-1 font-mono text-[11px] text-zinc-600">
                        {{ $view->ip_hash }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <button wire:click="confirmDelete({{ $view->id }})" class="text-zinc-400 hover:text-red-600 transition">
                        <x-lucide-trash-2 class="h-4 w-4" />
                    </button>
                </td>
            </tr>
        @endforeach

        <x-slot:footer>
            {{ $this->views->links() }}
        </x-slot:footer>
    </x-ui.table>

    <x-ui.confirm-modal
        name="confirm-delete-view"
        title="Remover Log"
        content="Esta ação é irreversível e removerá apenas o registro individual de log."
        buttonText="Confirmar Exclusão"
        variant="danger"
        action="delete"
    />
</div>
