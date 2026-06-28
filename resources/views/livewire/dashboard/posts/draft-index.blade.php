<div>
    {{ Breadcrumbs::render('dashboard.posts.draft') }}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-zinc-900">Gerenciar Rascunhos</h2>
            <p class="text-sm text-zinc-500">Textos que ainda estão em fase de criação.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="min-w-[300px]">
                <x-ui.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar rascunho..."
                    class="!py-2.5"
                >
                    <x-slot:iconLeft>
                        <x-lucide-search class="h-4 w-4 text-zinc-400" />
                    </x-slot:iconLeft>
                </x-ui.input>
            </div>

            <a href="{{ route('dashboard.posts.create') }}" class="inline-flex h-11 items-center justify-center rounded-2xl bg-zinc-900 px-6 text-sm font-bold text-white transition hover:bg-zinc-800 active:scale-95">
                Escrever Novo
            </a>
        </div>
    </div>

    <x-ui.table>
        <x-slot:header>
            <x-ui.table.th
                label="Conteúdo"
                column="title"
                :sort="$sort"
                :direction="$direction"
            />

            <x-ui.table.th label="Status" align="center" />

            <x-ui.table.th
                label="Criado em"
                column="created_at"
                align="center"
                :sort="$sort"
                :direction="$direction"
            />

            <x-ui.table.th label="Ações" align="right" />
        </x-slot:header>

        @forelse($this->posts as $post)
            <tr class="group hover:bg-zinc-50/50 transition" wire:key="draft-{{ $post->id }}">
                <td class="px-6 py-4 text-left">
                    <div class="flex flex-col">
                        <span class="font-bold text-zinc-900 leading-tight">{{ $post->title }}</span>
                        <span class="text-xs text-zinc-500 mt-1">{{ $post->category?->name ?? 'Sem categoria' }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-yellow-100 text-yellow-700">
                        {{ $post->status->label() }}
                    </span>
                </td>
                <td class="px-6 py-4 text-center font-medium text-zinc-500 text-xs">
                    {{ $post->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-1">
                        <x-ui.tooltip text="Editar rascunho">
                            <a href="{{ route('dashboard.posts.edit', $post) }}"
                               class="block p-2 text-zinc-400 hover:text-zinc-900 transition rounded-xl hover:bg-zinc-100">
                                <x-lucide-pencil class="h-4 w-4" />
                            </a>
                        </x-ui.tooltip>
                        <x-ui.tooltip text="Adicionar a coleção">
                            <button wire:click="openCollections({{ $post->id }})"
                                    class="p-2 text-zinc-400 hover:text-indigo-600 transition rounded-xl hover:bg-indigo-50">
                                <x-lucide-folder-plus class="h-4 w-4" />
                            </button>
                        </x-ui.tooltip>
                        <x-ui.tooltip text="Excluir">
                            <button wire:click="confirmDelete({{ $post->id }})"
                                    class="p-2 text-zinc-400 hover:text-red-600 transition rounded-xl hover:bg-red-50">
                                <x-lucide-trash-2 class="h-4 w-4" />
                            </button>
                        </x-ui.tooltip>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-6 py-12">
                    <x-ui.empty-state
                        title="Nenhum rascunho encontrado"
                        description="{{ $search ? 'Não encontramos rascunhos para sua busca.' : 'Você não possui textos salvos como rascunho no momento.' }}"
                    />
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            {{ $this->posts->links() }}
        </x-slot:footer>
    </x-ui.table>

    <x-ui.confirm-modal
        name="confirm-post-deletion"
        title="Excluir Rascunho"
        content="Tem certeza que deseja excluir permanentemente este rascunho? Esta ação não pode ser desfeita."
        buttonText="Sim, excluir permanentemente"
        variant="danger"
        action="deletePost"
    />

    @include('livewire.dashboard.posts.partials.collections-quick-modal')
</div>
