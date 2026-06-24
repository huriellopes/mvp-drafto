<x-ui.modal name="post-collections-quick" title="Adicionar a uma coleção">
    <div class="space-y-4 p-6">
        @if($this->quickCollections->isEmpty())
            <div class="rounded-2xl bg-zinc-50 p-5 text-center text-sm text-zinc-500 ring-1 ring-zinc-100">
                Você ainda não tem coleções.
                <a href="{{ route('dashboard.posts.collections.index') }}" wire:navigate class="font-semibold text-indigo-600 underline hover:text-indigo-700">
                    Criar minha primeira coleção
                </a>.
            </div>
        @else
            <p class="text-xs text-zinc-500">Marque as coleções em que esta obra deve aparecer.</p>

            <div class="max-h-72 space-y-1 overflow-y-auto">
                @foreach($this->quickCollections as $collection)
                    <label wire:key="quick-col-{{ $collection->id }}" class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl bg-zinc-50 p-3 ring-1 ring-zinc-100 transition hover:ring-zinc-200">
                        <span class="min-w-0 truncate text-sm font-semibold text-zinc-800">{{ $collection->name }}</span>
                        <input
                            type="checkbox"
                            @checked($collection->getAttribute('in_collection'))
                            wire:click="toggleCollectionForPost({{ $collection->id }})"
                            class="h-5 w-5 shrink-0 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500"
                        >
                    </label>
                @endforeach
            </div>

            <div class="flex justify-end pt-2">
                <a href="{{ route('dashboard.posts.collections.index') }}" wire:navigate class="text-xs font-semibold text-zinc-500 underline hover:text-zinc-800">
                    Gerenciar coleções
                </a>
            </div>
        @endif
    </div>
</x-ui.modal>
