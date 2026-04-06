<div class="space-y-6">
    {{-- Filtros e Ações --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center">
            <div class="w-full md:w-80">
                <x-ui.input
                    wire:model.live.debounce.300ms="filters.search"
                    placeholder="Buscar por e-mail..."
                >
                    <x-slot:prefix>
                        <x-lucide-search class="h-4 w-4 text-zinc-400" />
                    </x-slot:prefix>
                </x-ui.input>
            </div>

            <div class="w-full md:w-70">
                <select
                    wire:model.live="filters.category_id"
                    class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-profile-primary focus:ring-0 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                >
                    <option value="">Todas as categorias</option>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.button
                variant="primary"
                x-on:click="$dispatch('open-modal', { name: 'manual-newsletter-modal' })"
                title="Disparo Manual"
            >
                <x-lucide-send class="h-4 w-4" />
            </x-ui.button>
            <x-ui.button wire:click="export" wire:loading.attr="disabled" class="px-6">
                <x-lucide-download wire:loading.remove wire:target="export" class="mr-2 h-4 w-4" />
                <x-lucide-loader-2 wire:loading wire:target="export" class="mr-2 h-4 w-4 animate-spin" />
                Excel
            </x-ui.button>
        </div>
    </div>

    {{-- Tabela --}}
    <x-ui.table>
        <x-slot:header>
            <x-ui.table.th label="E-mail" column="email" :sort="$filters->sort" :direction="$filters->direction" />
            <x-ui.table.th label="Categoria de Interesse" />
            <x-ui.table.th label="Inscrito em" column="created_at" :sort="$filters->sort" :direction="$filters->direction" />
            <x-ui.table.th label="Ações" align="right" />
        </x-slot:header>

        @forelse($this->subscribers as $subscriber)
            <tr wire:key="{{ $subscriber->id }}" class="group hover:bg-zinc-50/50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500">
                            <x-lucide-mail class="h-4 w-4" />
                        </div>
                        <span class="font-medium text-zinc-900">{{ $subscriber->email }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-zinc-600">
                    @if($subscriber->category)
                        <span class="inline-flex items-center rounded-full bg-profile-primary/10 px-2.5 py-0.5 text-xs font-medium text-profile-primary">
                            {{ $subscriber->category->name }}
                        </span>
                    @else
                        <span class="text-xs text-zinc-400 italic">Geral</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-zinc-500">
                    {{ $subscriber->created_at->translatedFormat('d M, Y H:i') }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex justify-end gap-2">
                        <button
                            wire:click="confirmDeletion({{ $subscriber->id }})"
                            class="flex h-9 w-9 items-center justify-center rounded-xl text-zinc-400 hover:bg-red-50 hover:text-red-600 transition"
                        >
                            <x-lucide-trash-2 class="h-4 w-4" />
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-6 py-20 text-center">
                    <div class="flex flex-col items-center justify-center text-zinc-400">
                        <x-lucide-inbox class="h-12 w-12 mb-4" />
                        <p class="text-lg font-medium">Nenhum inscrito encontrado.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            {{ $this->subscribers->links() }}
        </x-slot:footer>
    </x-ui.table>

    {{-- Modal de Confirmação --}}
    <x-ui.confirm-modal
        name="confirm-subscriber-deletion"
        title="Remover Inscrito"
        content="Tem certeza que deseja remover este e-mail da newsletter? Esta ação não pode ser desfeita."
        buttonText="Sim, Remover"
        variant="danger"
        action="delete"
    />

    <x-ui.modal name="manual-newsletter-modal" title="Disparo de Mensagem Direta">
        <div class="space-y-6">
            <p class="text-sm text-zinc-500">
                Esta mensagem será enviada para <strong>todos</strong> os inscritos da newsletter via fila de processamento.
            </p>

            <x-ui.textarea
                label="Conteúdo da Mensagem"
                wire:model="customMessage"
                placeholder="Olá leitores, temos novidades exclusivas..."
                rows="5"
            />

            <div class="flex justify-end gap-3 pt-4">
                <x-ui.button variant="secondary" x-on:click="show = false">
                    Cancelar
                </x-ui.button>
                <x-ui.button wire:click="sendManualNewsletter" loading="sendManualNewsletter">
                    Confirmar Disparo
                </x-ui.button>
            </div>
        </div>
    </x-ui.modal>
</div>
