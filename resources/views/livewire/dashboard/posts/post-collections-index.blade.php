@use(App\Enums\PostCollectionVisibilityEnum)
@use(App\Enums\PostStatusEnum)
<div class="mx-auto w-full max-w-7xl px-4 pb-20 space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-zinc-100">Minhas Coleções</h1>
            <p class="mt-1 text-sm text-zinc-500">Agrupe rascunhos e obras publicadas em séries, ensinamentos e temas.</p>
        </div>

        <x-ui.button
            type="button"
            @click="$dispatch('open-modal', { name: 'post-collection-modal' })"
            class="!w-auto px-6 shrink-0"
        >
            <x-lucide-plus class="mr-2 h-4 w-4" />
            Nova coleção
        </x-ui.button>
    </div>

    {{-- Grade de coleções --}}
    @if($this->collections->isEmpty())
        <div class="rounded-3xl border border-dashed border-zinc-200 bg-zinc-50/50 p-12 text-center dark:border-zinc-800 dark:bg-zinc-900/30">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-zinc-100">
                <x-lucide-library class="h-6 w-6 text-zinc-400" />
            </div>
            <h3 class="mt-4 text-sm font-bold text-zinc-800 dark:text-zinc-100">Nenhuma coleção ainda</h3>
            <p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-zinc-500">
                Crie sua primeira coleção para organizar suas obras — por exemplo "Contos", "Tutoriais" ou uma série específica.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($this->collections as $collection)
                <div
                    wire:key="collection-{{ $collection->id }}"
                    @class([
                        'group rounded-3xl border bg-white p-6 shadow-sm transition dark:bg-zinc-900',
                        'border-indigo-300 ring-2 ring-indigo-100' => $collectionId === $collection->id,
                        'border-zinc-200 hover:border-zinc-300 dark:border-zinc-800' => $collectionId !== $collection->id,
                    ])
                >
                    <div class="flex items-start justify-between gap-3">
                        <button type="button" wire:click="select({{ $collection->id }})" class="min-w-0 flex-1 text-left">
                            <h3 class="truncate text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $collection->name }}</h3>
                            @if($collection->description)
                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-zinc-500">{{ $collection->description }}</p>
                            @endif
                        </button>

                        <span @class([
                            'shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                            'bg-emerald-50 text-emerald-600' => $collection->visibility === PostCollectionVisibilityEnum::PUBLIC,
                            'bg-zinc-100 text-zinc-500' => $collection->visibility === PostCollectionVisibilityEnum::PRIVATE,
                        ])>
                            {{ $collection->visibility->label() }}
                        </span>
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500">
                            <x-lucide-file-text class="h-3.5 w-3.5" />
                            {{ $collection->posts_count }} {{ Str::plural('obra', $collection->posts_count) }}
                        </span>

                        <div class="flex items-center gap-1">
                            <button type="button" wire:click="select({{ $collection->id }})" title="Gerenciar obras" class="rounded-lg p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700">
                                <x-lucide-list-checks class="h-4 w-4" />
                            </button>
                            <button type="button" wire:click="openEditModal({{ $collection->id }})" title="Editar" class="rounded-lg p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700">
                                <x-lucide-pencil class="h-4 w-4" />
                            </button>
                            <button type="button" wire:click="confirmDelete({{ $collection->id }})" title="Excluir" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600">
                                <x-lucide-trash-2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Painel de gestão de obras da coleção selecionada --}}
    @if($this->activeCollection)
        <x-ui.section-card
            title="Obras em “{{ $this->activeCollection->name }}”"
            description="Marque suas publicações e rascunhos para incluí-los nesta coleção."
        >
            <div class="mb-5 flex items-center justify-between gap-3">
                <x-ui.input
                    wire:model.live.debounce.300ms="postSearch"
                    placeholder="Buscar nas suas obras..."
                    class="max-w-sm"
                />
                <button type="button" wire:click="clearSelection" class="shrink-0 text-xs font-semibold text-zinc-500 underline hover:text-zinc-800">
                    Fechar
                </button>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($this->attachablePosts as $post)
                    <label wire:key="attach-{{ $post->id }}" class="flex cursor-pointer items-center justify-between gap-4 py-3">
                        <div class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $post->title }}</span>
                            <span @class([
                                'mt-0.5 inline-block rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                                'bg-amber-50 text-amber-600' => $post->status === PostStatusEnum::DRAFT,
                                'bg-emerald-50 text-emerald-600' => $post->status !== PostStatusEnum::DRAFT,
                            ])>
                                {{ $post->status === PostStatusEnum::DRAFT ? 'Rascunho' : 'Publicado' }}
                            </span>
                        </div>

                        <input
                            type="checkbox"
                            @checked($post->getAttribute('in_collection'))
                            wire:click="togglePost({{ $post->id }})"
                            class="h-5 w-5 shrink-0 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500"
                        >
                    </label>
                @empty
                    <p class="py-6 text-center text-sm text-zinc-500">Nenhuma obra encontrada.</p>
                @endforelse
            </div>
        </x-ui.section-card>
    @endif

    {{-- Modal: criar coleção --}}
    <x-ui.modal name="post-collection-modal" title="Nova coleção">
        <form wire:submit="createCollection" class="space-y-5 p-6">
            <x-ui.input wire:model="form.name" label="Nome" placeholder="Ex.: Contos de inverno" :error="$errors->first('form.name')" />
            <x-ui.input wire:model="form.slug" label="Slug (opcional)" placeholder="contos-de-inverno" :error="$errors->first('form.slug')" />
            <x-ui.textarea wire:model="form.description" label="Descrição (opcional)" rows="3" :error="$errors->first('form.description')" />

            <x-ui.select wire:model="form.visibility" label="Visibilidade" :error="$errors->first('form.visibility')">
                @foreach(PostCollectionVisibilityEnum::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }} — {{ $option->description() }}</option>
                @endforeach
            </x-ui.select>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button type="button" @click="$dispatch('close-modal', { name: 'post-collection-modal' })" class="!bg-white !text-zinc-500 !w-auto px-6 border border-zinc-200">
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit" loading="createCollection" class="!w-auto px-8">
                    Criar coleção
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal: editar coleção --}}
    <x-ui.modal name="edit-post-collection-modal" title="Editar coleção">
        <form wire:submit="updateCollection" class="space-y-5 p-6">
            <x-ui.input wire:model="form.name" label="Nome" :error="$errors->first('form.name')" />
            <x-ui.input wire:model="form.slug" label="Slug" :error="$errors->first('form.slug')" />
            <x-ui.textarea wire:model="form.description" label="Descrição (opcional)" rows="3" :error="$errors->first('form.description')" />

            <x-ui.select wire:model="form.visibility" label="Visibilidade" :error="$errors->first('form.visibility')">
                @foreach(PostCollectionVisibilityEnum::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }} — {{ $option->description() }}</option>
                @endforeach
            </x-ui.select>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button type="button" @click="$dispatch('close-modal', { name: 'edit-post-collection-modal' })" class="!bg-white !text-zinc-500 !w-auto px-6 border border-zinc-200">
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit" loading="updateCollection" class="!w-auto px-8">
                    Salvar alterações
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Confirmação de exclusão --}}
    <x-ui.confirm-modal
        name="confirm-delete-post-collection"
        title="Excluir coleção"
        content="Tem certeza? A coleção será removida, mas suas obras (posts) serão mantidas — apenas o agrupamento é desfeito."
        buttonText="Sim, excluir coleção"
        variant="danger"
        action="deleteCollection"
    />
</div>
