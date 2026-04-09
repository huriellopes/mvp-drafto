<div class="space-y-6 pb-20">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <x-ui.input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por post ou IP..."
            class="max-w-md"
        >
            <x-slot:prefix><x-lucide-search class="h-4 w-4" /></x-slot:prefix>
        </x-ui.input>

        <x-ui.export-button
            variant="primary"
            wire:click="export"
            class="flex items-center gap-2"
            loading="export"
        >
            <x-lucide-file-spreadsheet class="h-4 w-4" /> Excel
        </x-ui.export-button>
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
