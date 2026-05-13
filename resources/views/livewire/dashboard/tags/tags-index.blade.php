<div class="space-y-6">
    {{ Breadcrumbs::render('dashboard.tags') }}
    
    {{-- Header & Filtros --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-80">
            <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Buscar tags...">
                <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
            </x-ui.input>
        </div>

        @if(auth()->user()->getModuleSetting(\App\Enums\ModuleEnum::TAGS, 'allow_custom_tags', false))
            <x-ui.button wire:click="edit()" class="!w-auto px-6">
                <x-lucide-plus class="h-4 w-4 mr-2" /> Nova Tag
            </x-ui.button>
        @endif
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" wire:loading.class="opacity-50 transition-opacity">
        @forelse($this->tags as $tag)
            <x-dashboard.tag-card :$tag wire:key="tag-{{ $tag->id }}" />
        @empty
            <div class="col-span-full py-20 bg-white dark:bg-zinc-900 rounded-[3rem] border border-dashed border-zinc-200 dark:border-zinc-800 text-center">
                <x-lucide-tag class="h-12 w-12 text-zinc-200 mx-auto mb-4" />
                <p class="text-sm font-bold text-zinc-500">Nenhuma tag encontrada.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $this->tags->links() }}
    </div>

    {{-- Modal de Formulário --}}
    <x-ui.modal name="tag-modal" title="{{ $this->form->editingTagId ? 'Editar Tag' : 'Nova Tag' }}">
        <form wire:submit.prevent="save" class="space-y-6">
            <x-ui.input label="Nome da Tag" wire:model="form.name" :error="$errors->first('form.name')" placeholder="Ex: Tecnologia" />

            <div class="flex justify-end gap-3 pt-4">
                <x-ui.button variant="secondary" x-on:click="show = false" type="button" class="!w-auto px-8">Cancelar</x-ui.button>
                <x-ui.button type="submit" loading="save" class="!w-auto px-10">Salvar</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal de Confirmação de Exclusão --}}
    <x-ui.confirm-modal
        name="confirm-delete-tag"
        title="Remover Tag"
        content="Tem certeza? Esta ação removerá a tag permanentemente de todas as suas publicações."
        buttonText="Sim, Remover"
        variant="danger"
        action="delete"
    />
</div>
