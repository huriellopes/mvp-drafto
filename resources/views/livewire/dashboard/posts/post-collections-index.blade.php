@use(App\Enums\PostCollectionVisibilityEnum)
@use(App\Enums\PostStatusEnum)
<div class="space-y-6">
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-zinc-100">Minhas Coleções</h1>
        <p class="text-sm text-zinc-500">Agrupe rascunhos e obras publicadas em séries, ensinamentos e temas.</p>
    </div>

    <div class="flex flex-col gap-8 lg:flex-row pb-20">
        {{-- Sidebar de coleções --}}
        <aside class="w-full lg:w-64 space-y-6">
            <div class="sticky top-24">
                <h3 class="mb-5 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400">Coleções</h3>
                <nav class="space-y-1.5">
                    <button
                        wire:click="$set('collection', null)"
                        @class([
                            'group flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-300',
                            'bg-zinc-900 text-white shadow-lg shadow-zinc-200/50' => is_null($collection),
                            'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900' => !is_null($collection),
                        ])
                    >
                        <x-lucide-layers @class(['h-4 w-4', 'text-white' => is_null($collection), 'text-zinc-400' => !is_null($collection)]) />
                        <span>Todas as obras</span>
                    </button>

                    @foreach($this->collections as $item)
                        <div class="group relative" wire:key="sidebar-collection-{{ $item->id }}">
                            <button
                                wire:click="$set('collection', '{{ $item->slug }}')"
                                @class([
                                    'flex w-full items-center justify-between rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-300',
                                    'bg-zinc-900 text-white shadow-lg shadow-zinc-200/50' => $collection === $item->slug,
                                    'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900' => $collection !== $item->slug,
                                ])
                            >
                                <span class="flex min-w-0 items-center gap-3">
                                    <x-lucide-folder @class(['h-4 w-4 shrink-0', 'text-white' => $collection === $item->slug, 'text-zinc-400' => $collection !== $item->slug]) />
                                    <span class="max-w-[100px] truncate">{{ $item->name }}</span>
                                </span>
                                <span @class([
                                    'shrink-0 rounded-full px-1.5 text-[10px] font-bold',
                                    'bg-white/20 text-white' => $collection === $item->slug,
                                    'bg-zinc-100 text-zinc-400' => $collection !== $item->slug,
                                ])>{{ $item->posts_count }}</span>
                            </button>

                            <div class="absolute right-9 top-1/2 flex -translate-y-1/2 items-center opacity-100 transition-opacity lg:opacity-0 lg:group-hover:opacity-100">
                                <button wire:click="openEditModal({{ $item->id }})" title="Editar" class="p-1.5 text-zinc-400 hover:text-zinc-900">
                                    <x-lucide-pencil class="h-3.5 w-3.5" />
                                </button>
                                <button wire:click="confirmDelete({{ $item->id }})" title="Excluir" class="p-1.5 text-zinc-400 hover:text-red-600">
                                    <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                </nav>

                <div class="mt-6 px-4">
                    <button
                        x-on:click="$dispatch('open-modal', { name: 'post-collection-modal' })"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-200 py-3 text-[11px] font-bold uppercase tracking-wider text-zinc-400 transition-all hover:border-zinc-400 hover:text-zinc-600 active:scale-95"
                    >
                        <x-lucide-plus class="h-3 w-3" />
                        Nova coleção
                    </button>
                </div>
            </div>
        </aside>

        {{-- Conteúdo --}}
        <div class="flex-1 space-y-8">
            {{-- Filtros (abaixo do header) --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-ui.input
                    class="w-full"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar em {{ $this->activeCollection?->name ?? 'todas as obras' }}..."
                >
                    <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
                </x-ui.input>

                <x-ui.select wire:model.live="statusFilter" class="w-full md:w-48 md:justify-self-end">
                    <option value="">Todos os status</option>
                    <option value="published">Publicados</option>
                    <option value="draft">Rascunhos</option>
                </x-ui.select>
            </div>

            @if($this->activeCollection?->description)
                <p class="-mt-4 text-sm text-zinc-500">{{ $this->activeCollection->description }}</p>
            @endif

            {{-- Grid de cards --}}
            <div wire:loading.class="opacity-50" class="grid min-h-[300px] grid-cols-1 gap-6 transition sm:grid-cols-2 xl:grid-cols-3">
                @forelse($this->posts as $post)
                    <div wire:key="collection-post-{{ $post->id }}" class="group flex flex-col overflow-hidden rounded-[2rem] border border-zinc-100 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-zinc-200/50 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="relative aspect-video w-full overflow-hidden bg-zinc-50 dark:bg-zinc-800">
                            @if($post->cover_image_url)
                                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <x-lucide-image class="h-8 w-8 text-zinc-200" />
                                </div>
                            @endif

                            <div class="absolute right-3 top-3 flex gap-2 opacity-100 transition-all duration-300 lg:translate-y-2 lg:opacity-0 lg:group-hover:translate-y-0 lg:group-hover:opacity-100">
                                <button
                                    wire:click="openCollections({{ $post->id }})"
                                    title="Organizar coleções"
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/95 text-zinc-600 shadow-xl backdrop-blur transition hover:text-indigo-600 active:scale-90"
                                >
                                    <x-lucide-folder-input class="h-4 w-4" />
                                </button>

                                @if($this->activeCollection)
                                    <button
                                        wire:click="removeFromActiveCollection({{ $post->id }})"
                                        title="Remover desta coleção"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/95 text-red-500 shadow-xl backdrop-blur transition hover:bg-red-50 active:scale-90"
                                    >
                                        <x-lucide-folder-minus class="h-4 w-4" />
                                    </button>
                                @endif
                            </div>

                            <div class="absolute left-3 top-3">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider backdrop-blur',
                                    'bg-amber-100/90 text-amber-700' => $post->status === PostStatusEnum::DRAFT,
                                    'bg-emerald-100/90 text-emerald-700' => $post->status === PostStatusEnum::PUBLISHED,
                                    'bg-indigo-100/90 text-indigo-700' => $post->status === PostStatusEnum::SCHEDULED,
                                    'bg-zinc-100/90 text-zinc-600' => $post->status === PostStatusEnum::ARCHIVED,
                                ])>
                                    {{ $post->status->label() }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col p-6">
                            @if($post->category)
                                <span class="mb-3 inline-block w-fit rounded-lg bg-zinc-100 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-zinc-500">
                                    {{ $post->category->name }}
                                </span>
                            @endif

                            <h3 class="mb-4 line-clamp-2 text-base font-bold leading-tight text-zinc-900 transition-colors group-hover:text-zinc-600 dark:text-zinc-100">
                                <a href="{{ route('dashboard.posts.edit', $post) }}" wire:navigate>{{ $post->title }}</a>
                            </h3>

                            <div class="mt-auto flex items-center justify-between border-t border-zinc-50 pt-4 dark:border-zinc-800">
                                <span class="text-[10px] font-medium italic text-zinc-400">{{ $post->created_at->diffForHumans() }}</span>
                                <a href="{{ route('dashboard.posts.edit', $post) }}" wire:navigate class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-700">
                                    Editar <x-lucide-arrow-right class="h-3 w-3" />
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-[3rem] border-2 border-dashed border-zinc-100 py-24 text-center dark:border-zinc-800">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-3xl bg-zinc-50">
                            <x-lucide-library class="h-8 w-8 text-zinc-200" />
                        </div>
                        <p class="text-sm font-medium text-zinc-400">
                            @if($this->activeCollection)
                                Esta coleção ainda não tem obras. Use "Organizar coleções" em qualquer obra para adicioná-la.
                            @else
                                Nenhuma obra encontrada.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $this->posts->links() }}
            </div>
        </div>
    </div>

    {{-- Modal: criar coleção --}}
    <x-ui.modal name="post-collection-modal" title="Nova coleção">
        <form wire:submit="createCollection" class="space-y-5">
            <x-ui.input wire:model="form.name" label="Nome" placeholder="Ex.: Contos de inverno" :error="$errors->first('form.name')" />
            <x-ui.input wire:model="form.slug" label="Slug (opcional)" placeholder="contos-de-inverno" :error="$errors->first('form.slug')" />
            <x-ui.textarea wire:model="form.description" label="Descrição (opcional)" rows="3" :error="$errors->first('form.description')" />

            <x-ui.select wire:model="form.visibility" label="Visibilidade" :error="$errors->first('form.visibility')">
                @foreach(PostCollectionVisibilityEnum::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }} — {{ $option->description() }}</option>
                @endforeach
            </x-ui.select>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', { name: 'post-collection-modal' })">Cancelar</x-ui.button>
                <x-ui.button type="submit" loading="createCollection">Criar coleção</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal: editar coleção --}}
    <x-ui.modal name="edit-post-collection-modal" title="Editar coleção">
        <form wire:submit="updateCollection" class="space-y-5">
            <x-ui.input wire:model="form.name" label="Nome" :error="$errors->first('form.name')" />
            <x-ui.input wire:model="form.slug" label="Slug" :error="$errors->first('form.slug')" />
            <x-ui.textarea wire:model="form.description" label="Descrição (opcional)" rows="3" :error="$errors->first('form.description')" />

            <x-ui.select wire:model="form.visibility" label="Visibilidade" :error="$errors->first('form.visibility')">
                @foreach(PostCollectionVisibilityEnum::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }} — {{ $option->description() }}</option>
                @endforeach
            </x-ui.select>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', { name: 'edit-post-collection-modal' })">Cancelar</x-ui.button>
                <x-ui.button type="submit" loading="updateCollection">Salvar alterações</x-ui.button>
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

    {{-- Modal de organizar (compartilhado com as listas) --}}
    @include('livewire.dashboard.posts.partials.collections-quick-modal')
</div>
