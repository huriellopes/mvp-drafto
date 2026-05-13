@use(Carbon\Carbon)

<div class="space-y-6">
    {{ Breadcrumbs::render('dashboard.posts.saved') }}

    <div class="flex flex-col gap-8 lg:flex-row pb-20">
        {{-- Sidebar de Navegação --}}
        <aside class="w-full lg:w-64 space-y-6">
            <div class="sticky top-24">
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 mb-5 px-4">Minha Biblioteca</h3>
                <nav class="space-y-1.5">
                    <button
                        wire:click="$set('collection', null)"
                        @class([
                            'group flex w-full items-center justify-between px-4 py-3 text-sm font-semibold rounded-2xl transition-all duration-300',
                            'bg-zinc-900 text-white shadow-lg shadow-zinc-200/50' => is_null($collection),
                            'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900' => !is_null($collection)
                        ])
                    >
                        <div class="flex items-center gap-3">
                            <x-lucide-layers @class(['h-4 w-4', 'text-white' => is_null($collection), 'text-zinc-400' => !is_null($collection)]) />
                            <span>Todos os itens</span>
                        </div>
                    </button>

                    @foreach($this->collections as $item)
                        <div class="group relative" wire:key="sidebar-coll-{{ $item->id }}">
                            <button
                                wire:click="$set('collection', '{{ $item->slug }}')"
                                @class([
                                    'flex w-full items-center justify-between px-4 py-3 text-sm font-semibold rounded-2xl transition-all duration-300',
                                    'bg-zinc-900 text-white shadow-lg shadow-zinc-200/50' => $collection === $item->slug,
                                    'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900' => $collection !== $item->slug
                                ])
                            >
                                <div class="flex items-center gap-3">
                                    <x-lucide-folder @class(['h-4 w-4', 'text-white' => $collection === $item->slug, 'text-zinc-400' => $collection !== $item->slug]) />
                                    <span class="truncate max-w-[110px]">{{ $item->name }}</span>
                                </div>
                            </button>

                            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="openEditCollectionModal({{ $item->id }})" class="p-1.5 text-zinc-400 hover:text-zinc-900">
                                    <x-lucide-pencil class="h-3.5 w-3.5" />
                                </button>
                                <button wire:click="confirmDeleteCollection({{ $item->id }})" class="p-1.5 text-zinc-400 hover:text-red-600">
                                    <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                </nav>

                <div class="mt-6 px-4">
                    <button
                        x-on:click="$dispatch('open-modal', { name: 'new-collection-modal' })"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-200 py-3 text-[11px] font-bold uppercase tracking-wider text-zinc-400 transition-all hover:border-zinc-400 hover:text-zinc-600 active:scale-95"
                    >
                        <x-lucide-plus class="h-3 w-3" />
                        Nova Coleção
                    </button>
                </div>
            </div>
        </aside>

        {{-- Área de Conteúdo --}}
        <div class="flex-1 space-y-8">
            {{-- Filtros Superior --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:flex-row md:items-center">
                <div class="flex-1">
                    <x-ui.input
                        class="w-full"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar em {{ $collection ? 'esta coleção' : 'todos os salvos' }}..."
                    >
                        <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
                    </x-ui.input>
                </div>

                <x-ui.select wire:model.live="categoryId" class="w-48">
                    <option value="">Todas as categorias</option>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="rounded-3xl bg-zinc-900 p-1 pl-4 pr-1 flex items-center justify-between text-white text-[10px] font-bold uppercase tracking-widest shadow-xl">
                    <div class="flex items-center gap-2">
                        <x-lucide-shield-alert class="h-3.5 w-3.5 text-amber-400" />
                        Visão Geral da Plataforma (Admin)
                    </div>
                    <div class="bg-white/10 rounded-2xl px-3 py-1.5 backdrop-blur-md">
                        Modo Auditoria
                    </div>
                </div>
            @endif

            {{-- Grid --}}
            <div
                wire:loading.class="opacity-50"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 transition-all duration-500 min-h-[400px]"
            >
                @forelse($this->savedPosts as $post)
                    <div wire:key="saved-post-{{ $post->id }}" class="group flex flex-col overflow-hidden rounded-[2rem] border border-zinc-100 bg-white transition-all duration-300 hover:shadow-2xl hover:shadow-zinc-200/50 hover:-translate-y-1">
                        <div class="aspect-video w-full overflow-hidden bg-zinc-50 relative">
                            @if($post->cover_image_url)
                                <img src="{{ $post->cover_image_url }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-zinc-50">
                                    <x-lucide-image class="h-8 w-8 text-zinc-200" />
                                </div>
                            @endif

                            <div class="absolute right-3 top-3 flex gap-2 translate-y-2 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                                <button
                                    wire:click="openMoveModal({{ $post->id }}, {{ $post->pivot?->collection_id ?? ($post->collection_id ?? 'null') }})"
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/95 text-zinc-600 shadow-xl backdrop-blur transition hover:text-zinc-900 active:scale-90"
                                    title="Organizar"
                                >
                                    <x-lucide-folder-input class="h-4 w-4" />
                                </button>

                                <button
                                    wire:click="confirmUnsave({{ $post->id }})"
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/95 text-red-500 shadow-xl backdrop-blur transition hover:bg-red-50 active:scale-90"
                                    title="Remover"
                                >
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col p-6">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="rounded-lg bg-zinc-100 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-zinc-500">
                                    {{ $post->category->name }}
                                </span>

                                @if(auth()->user()->isAdmin())
                                    <span class="flex items-center gap-1 text-[9px] text-zinc-400 font-bold uppercase">
                                        <x-lucide-user class="h-2.5 w-2.5" />
                                        User: #{{ $post->user_id }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="mb-4 line-clamp-2 text-base font-bold leading-tight text-zinc-900 transition-colors group-hover:text-zinc-600">
                                <a href="{{ route('posts.show', $post->slug) }}" wire:navigate>{{ $post->title }}</a>
                            </h3>

                            <div class="mt-auto flex items-center justify-between pt-4 border-t border-zinc-50">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-lg bg-zinc-100 flex items-center justify-center text-[10px] font-bold text-zinc-400">
                                        {{ substr($post->author->name, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-medium text-zinc-500">{{ $post->author->name }}</span>
                                </div>
                                <span class="text-[10px] font-medium text-zinc-400 italic">
                                    @php
                                        $date = $post->saved_at ?? $post->created_at;
                                    @endphp
                                    {{ Carbon::parse($date)->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-32 text-center rounded-[3rem] border-2 border-dashed border-zinc-100">
                        <div class="bg-zinc-50 h-16 w-16 rounded-3xl flex items-center justify-center mx-auto mb-6">
                            <x-lucide-archive class="h-8 w-8 text-zinc-200" />
                        </div>
                        <p class="text-sm text-zinc-400 font-medium">Nenhum tesouro encontrado por aqui.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-12">
                {{ $this->savedPosts->links() }}
            </div>
        </div>
    </div>

    {{-- Modais --}}

    {{-- Modal: Confirmação Exclusão de Coleção --}}
    <x-ui.confirm-modal
        name="confirm-delete-collection"
        title="Excluir Coleção"
        content="Tem certeza que deseja excluir esta coleção? Seus posts salvos não serão apagados, apenas voltarão para a lista geral."
        buttonText="Sim, Excluir Coleção"
        variant="danger"
        action="deleteCollection"
    />

    {{-- Modal: Mover para Coleção --}}
    <x-ui.modal name="move-to-collection-modal" title="Organizar Conteúdo">
        <div class="space-y-6">
            <p class="text-sm text-zinc-500">Selecione uma coleção para mover este conteúdo ou remova-o de todas as coleções.</p>

            <div class="space-y-3">
                <label class="block text-sm font-bold text-zinc-700">Escolha a Coleção destino:</label>

                <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                    <button
                        wire:click="$set('targetCollectionId', null)"
                        @class([
                            'flex w-full items-center justify-between px-4 py-3 rounded-2xl border text-sm font-medium transition',
                            'border-zinc-900 bg-zinc-900 text-white shadow-lg' => is_null($targetCollectionId),
                            'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300' => !is_null($targetCollectionId)
                        ])
                    >
                        <span class="flex items-center gap-3">
                            <x-lucide-layers class="h-4 w-4" /> Sem Coleção (Geral)
                        </span>
                        @if(is_null($targetCollectionId)) <x-lucide-check class="h-4 w-4" /> @endif
                    </button>

                    @foreach($this->collections as $coll)
                        <button
                            wire:click="$set('targetCollectionId', {{ $coll->id }})"
                            @class([
                                'flex w-full items-center justify-between px-4 py-3 rounded-2xl border text-sm font-medium transition',
                                'border-zinc-900 bg-zinc-900 text-white shadow-lg' => $targetCollectionId === $coll->id,
                                'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300' => $targetCollectionId !== $coll->id
                            ])
                        >
                            <span class="flex items-center gap-3">
                                <x-lucide-folder class="h-4 w-4" /> {{ $coll->name }}
                            </span>
                            @if($targetCollectionId === $coll->id) <x-lucide-check class="h-4 w-4" /> @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', { name: 'move-to-collection-modal' })">
                    Cancelar
                </x-ui.button>
                <x-ui.button wire:click="moveToCollection" loading="moveToCollection">
                    Confirmar Mudança
                </x-ui.button>
            </div>
        </div>
    </x-ui.modal>

    {{-- Modal: Criar Nova Coleção --}}
    <x-ui.modal name="new-collection-modal" title="Criar Nova Coleção">
        <form wire:submit="createCollection" class="space-y-5">
            <x-ui.input
                label="Nome da Coleção"
                wire:model="collectionForm.name"
                placeholder="Ex: Estudos de Laravel..."
                :error="$errors->first('collectionForm.name')"
            />
            <x-ui.input
                label="Slug"
                wire:model="collectionForm.slug"
                placeholder="Ex: estudos-laravel..."
                :error="$errors->first('collectionForm.slug')"
            />
            <x-ui.textarea
                label="Descrição (Opcional)"
                wire:model="collectionForm.description"
                placeholder="Sobre o que é esta coleção?"
                rows="3"
                :error="$errors->first('collectionForm.description')"
            />
            <div class="flex justify-end gap-3 pt-4">
                <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', { name: 'new-collection-modal' })">
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit" loading="createCollection">
                    Criar Coleção
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal: Confirmação de Remoção Post --}}
    <x-ui.confirm-modal
        name="confirm-unsave-post"
        title="Remover dos Salvos"
        content="Tem certeza que deseja remover este post da sua biblioteca? Ele será excluído permanentemente da sua lista de salvos e coleções."
        buttonText="Sim, Remover"
        variant="danger"
        action="unsave"
    />

    {{-- Modal: Editar Coleção --}}
    <x-ui.modal name="edit-collection-modal" title="Editar Coleção">
        <form wire:submit="updateCollection" class="space-y-5">
            <x-ui.input
                label="Nome da Coleção"
                wire:model="collectionForm.name"
                :error="$errors->first('collectionForm.name')"
            />

            <x-ui.input
                label="Slug"
                wire:model="collectionForm.slug"
                :error="$errors->first('collectionForm.slug')"
            />

            <x-ui.textarea
                label="Descrição (Opcional)"
                wire:model="collectionForm.description"
                rows="3"
                :error="$errors->first('collectionForm.description')"
            />

            <div class="flex justify-end gap-3 pt-4">
                <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', { name: 'edit-collection-modal' })">
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit" loading="updateCollection">
                    Salvar Alterações
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
