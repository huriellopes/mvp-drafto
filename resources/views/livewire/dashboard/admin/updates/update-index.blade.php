@use(App\Enums\UpdateAudienceEnum)
<div class="mx-auto w-full max-w-5xl px-4 pb-20">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        {{-- Compositor --}}
        <div class="lg:col-span-7">
            <div class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-black tracking-tight text-zinc-900">
                    {{ $editingId ? 'Editar comunicado' : 'Novo comunicado' }}
                </h2>
                <p class="mt-1 text-sm text-zinc-500">Escreva a novidade/melhoria. Salve como rascunho e envie quando estiver pronto.</p>

                <form wire:submit="save" class="mt-6 space-y-5">

                    <x-ui.select label="Enviar para" wire:model="audience" :error="$errors->first('audience')">
                        @foreach(UpdateAudienceEnum::cases() as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }} — {{ $option->description() }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.input
                        label="Título"
                        wire:model="title"
                        placeholder="Ex.: Novo editor, agendamento de posts e mais"
                        :error="$errors->first('title')"
                    />

                    <div>
                        <label class="mb-2 block text-sm font-bold text-zinc-700">Conteúdo</label>
                        {{-- wire:key força o editor a ser recriado ao entrar/sair de edição
                             (o Quill adota o conteúdo apenas na construção). --}}
                        <div wire:key="update-editor-{{ $editingId ?? 'new' }}">
                            <x-ui.quill-editor wire:model="content" :initial="$content" placeholder="Descreva as novidades..." />
                        </div>
                        @error('content') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Barra fixa de salvar: cola no fim da tela ao rolar, sem precisar descer a página toda. --}}
                    <x-ui.sticky-bar align="end">
                        @if($editingId)
                            <x-ui.button type="button" wire:click="cancelEdit" class="!bg-white !text-zinc-500 hover:!text-zinc-900 !w-auto px-6">Cancelar</x-ui.button>
                        @endif
                        <x-ui.button type="submit" loading="save" class="!w-auto px-6">
                            {{ $editingId ? 'Atualizar comunicado' : 'Salvar comunicado' }}
                        </x-ui.button>
                    </x-ui.sticky-bar>
                </form>
            </div>
        </div>

        {{-- Histórico --}}
        <div class="lg:col-span-5">
            <div class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-black tracking-tight text-zinc-900">Comunicados</h2>

                <div class="mt-5 space-y-3">
                    @forelse($this->updates as $update)
                        <div class="rounded-2xl border border-zinc-100 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-zinc-900">{{ $update->title }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="text-[11px] font-bold uppercase tracking-widest {{ $update->isSent() ? 'text-emerald-600' : 'text-amber-600' }}">
                                            @if($update->isSent())
                                                Enviado &middot; {{ $update->recipients_count }} dest. &middot; {{ $update->sent_at->format('d/m/Y H:i') }}
                                            @else
                                                Rascunho
                                            @endif
                                        </span>
                                        <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-zinc-500">
                                            {{ $update->audience->label() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    @unless($update->isSent())
                                        <x-ui.tooltip text="Editar">
                                            <button wire:click="edit({{ $update->id }})" class="p-2 text-zinc-400 transition hover:text-zinc-900">
                                                <x-lucide-pencil class="h-4 w-4" />
                                            </button>
                                        </x-ui.tooltip>
                                    @endunless
                                    <x-ui.tooltip text="{{ $update->isSent() ? 'Reenviar' : 'Enviar agora' }}">
                                        <button wire:click="confirmSend({{ $update->id }})" class="p-2 text-zinc-400 transition hover:text-indigo-600">
                                            <x-lucide-send class="h-4 w-4" />
                                        </button>
                                    </x-ui.tooltip>
                                    <x-ui.tooltip text="Excluir">
                                        <button
                                            wire:click="confirmDelete({{ $update->id }})"
                                            class="p-2 text-zinc-400 transition hover:text-red-600"
                                        >
                                            <x-lucide-trash-2 class="h-4 w-4" />
                                        </button>
                                    </x-ui.tooltip>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-zinc-400">Nenhum comunicado ainda.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-ui.confirm-modal
        name="confirm-send-update"
        title="Enviar comunicado"
        content="Enviar este comunicado por e-mail para todos os usuários que aceitam avisos de novidades?"
        buttonText="Enviar agora"
        action="send"
    />

    <x-ui.confirm-modal
        name="confirm-delete-update"
        title="Remover comunicado"
        content="Tem certeza que deseja remover este comunicado? Esta ação não pode ser desfeita."
        buttonText="Remover"
        variant="danger"
        action="delete"
    />
</div>
