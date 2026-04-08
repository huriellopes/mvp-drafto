@use(App\Enums\PostStatusEnum)
<div>
    {{ Breadcrumbs::render('dashboard.posts.index') }}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-zinc-900 leading-tight">Gerenciar Publicações</h2>
            <p class="text-sm text-zinc-500">Acompanhe e organize seus conteúdos publicados e arquivados.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
            {{-- Busca --}}
            <div class="min-w-[280px]">
                <x-ui.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar publicação..."
                    class="!py-2.5"
                >
                    <x-slot:iconLeft>
                        <x-lucide-search class="h-4 w-4 text-zinc-400" />
                    </x-slot:iconLeft>
                </x-ui.input>
            </div>

            <div class="min-w-[180px]">
                <x-ui.select wire:model.live="status" class="!py-2.5">
                    <option value="">Todos Status</option>
                    <option value="{{ PostStatusEnum::PUBLISHED->value }}">Publicados</option>
                    <option value="{{ PostStatusEnum::ARCHIVED->value }}">Arquivados</option>
                </x-ui.select>
            </div>
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
                label="Visualizações"
                column="views_count"
                align="center"
                :sort="$sort"
                :direction="$direction"
            />

            <x-ui.table.th label="Ações" align="right" />
        </x-slot:header>

        @forelse($this->posts as $post)
            <x-dashboard.posts.table-row :post="$post" wire:key="post-item-{{ $post->id }}" />
        @empty
            <tr>
                <td colspan="4" class="px-6 py-12">
                    <x-ui.empty-state
                        title="Nenhuma publicação encontrada"
                        description="{{ $search ? 'Não existem resultados para sua busca atual.' : 'Você ainda não possui publicações ativas.' }}"
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
        title="Excluir Conteúdo"
        content="Tem certeza que deseja excluir esta publicação? Ela será movida para a lixeira e não aparecerá mais no seu perfil público."
        buttonText="Sim, excluir agora"
        variant="danger"
        action="deletePost"
    />
</div>
