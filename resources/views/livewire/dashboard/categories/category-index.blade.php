<div class="space-y-6">
    {{-- Header & Filtros --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-80">
            <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Buscar categorias...">
                <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
            </x-ui.input>
        </div>

        <x-ui.button wire:click="edit()" class="!w-auto px-6">
            <x-lucide-plus class="h-4 w-4 mr-2" /> Nova Categoria
        </x-ui.button>
    </div>

    {{-- Grid usando Componente Blade --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" wire:loading.class="opacity-50 transition-opacity">
        @forelse($this->categories as $category)
            <x-dashboard.category-card :$category wire:key="cat-{{ $category->id }}" />
        @empty
            <div class="col-span-full py-20 bg-white dark:bg-zinc-900 rounded-[3rem] border border-dashed border-zinc-200 dark:border-zinc-800 text-center">
                <x-lucide-folder-plus class="h-12 w-12 text-zinc-200 mx-auto mb-4" />
                <p class="text-sm font-bold text-zinc-500">Nenhuma categoria personalizada encontrada.</p>
                <button wire:click="edit()" class="mt-4 text-xs font-black uppercase tracking-widest text-profile-primary hover:underline">Criar agora</button>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $this->categories->links() }}
    </div>

    {{-- Modal de Formulário --}}
    <x-ui.modal name="category-modal" title="{{ $this->form->editingCategoryId ? 'Editar Categoria' : 'Nova Categoria' }}">
        <form wire:submit.prevent="save" class="space-y-6">
            <x-ui.input label="Nome da Categoria" wire:model="form.name" :error="$errors->first('form.name')" />
            <x-ui.input label="Slug" wire:model="form.slug" placeholder="Opcional: deixe vazio para gerar automático" :error="$errors->first('form.slug')" />
            <x-ui.textarea label="Descrição (Opcional)" wire:model="form.description" :error="$errors->first('form.description')" />

            <div class="flex justify-end gap-3 pt-4">
                <x-ui.button variant="secondary" x-on:click="show = false" type="button" class="!w-auto px-8">Cancelar</x-ui.button>
                <x-ui.button type="submit" loading="save" class="!w-auto px-10">Salvar</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal de Confirmação de Exclusão (Componente UI) --}}
    <x-ui.confirm-modal
        name="confirm-delete-category"
        title="Remover Categoria"
        content="Tem certeza? Esta ação removerá a categoria permanentemente. Só é possível remover se não houver posts vinculados."
        buttonText="Sim, Remover"
        variant="danger"
        action="delete"
    />
</div>
