@use(App\Enums\CommentStatusEnum)
<div class="space-y-6 pb-20">
    {{ Breadcrumbs::render('dashboard.comments') }}
    {{-- Filtros e Sorting --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-1 items-center gap-3">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar comentários..."
                class="max-w-md"
            >
                <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
            </x-ui.input>

            <x-ui.select wire:model.live="status" class="w-44">
                <option value="">Todos os Status</option>
                @foreach(CommentStatusEnum::options() as $opt)
                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                @endforeach
            </x-ui.select>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="sortBy('created_at')" class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-xs font-bold text-zinc-600 transition hover:bg-zinc-50">
                <x-lucide-clock class="h-3.5 w-3.5" />
                Data
                @if($sort === 'created_at')
                    <x-lucide-chevron-{{ $direction === 'asc' ? 'up' : 'down' }} class="h-3 w-3" />
                @endif
            </button>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        <div class="flex items-center gap-2 rounded-2xl bg-zinc-900 px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-white shadow-lg">
            <x-lucide-shield-check class="h-3.5 w-3.5 text-amber-400" />
            Visão Global de Moderação
        </div>
    @endif

    <div class="overflow-hidden rounded-[2.5rem] border border-zinc-200 bg-white shadow-sm">
        <ul class="divide-y divide-zinc-100">
            @forelse($this->comments as $comment)
                <li wire:key="comment-{{ $comment->id }}" class="group p-8 transition hover:bg-zinc-50/50">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-5">
                            {{-- Status Indicator --}}
                            <x-ui.tooltip :text="$comment->status->description()" position="right">
                                <div @class([
                                    'mt-1 h-3 w-3 shrink-0 rounded-full ring-4 ring-offset-2',
                                    'bg-emerald-500 ring-emerald-50' => $comment->status === CommentStatusEnum::VISIBLE,
                                    'bg-red-500 ring-red-50' => $comment->status === CommentStatusEnum::HIDDEN,
                                    'bg-amber-500 ring-amber-50' => $comment->status === CommentStatusEnum::PENDING,
                                ])></div>
                            </x-ui.tooltip>

                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="text-sm font-black text-zinc-900">{{ $comment->author?->name ?? __('Anonymous') }}</span>
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-tighter">{{ $comment->created_at->translatedFormat('d M, Y') }}</span>
                                    @if($comment->parent_id)
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 px-2 py-0.5 text-[9px] font-black text-zinc-500 uppercase">
                                            <x-lucide-reply class="h-2.5 w-2.5" /> Resposta
                                        </span>
                                    @endif
                                </div>

                                <p class="text-sm leading-relaxed text-zinc-600 max-w-2xl">
                                    {!! $comment->content !!}
                                </p>

                                <div class="flex items-center gap-2 rounded-xl bg-zinc-50 px-3 py-1.5 w-fit">
                                    <x-lucide-link-2 class="h-3 w-3 text-zinc-400" />
                                    <a
                                        href="{{ route('posts.show', $comment->post->slug) }}"
                                        target="_blank"
                                        class="text-[11px] font-bold text-zinc-500 hover:text-profile-primary transition"
                                    >
                                        {!! Str::limit($comment->post->title, 40) !!}
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Ações --}}
                        <div class="flex items-center gap-2 transition-all duration-300">
                            <x-ui.tooltip text="Editar ou Moderar">
                                <button
                                    wire:click="edit({{ $comment->id }})"
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white border border-zinc-100 text-zinc-400 shadow-sm hover:text-zinc-900 hover:border-zinc-300 transition-all"
                                >
                                    <x-lucide-pencil class="h-4 w-4" />
                                </button>
                            </x-ui.tooltip>

                            <x-ui.tooltip text="Excluir Comentário">
                                <button
                                    wire:click="confirmDelete({{ $comment->id }})"
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white border border-zinc-100 text-zinc-400 shadow-sm hover:text-red-600 hover:border-red-100 transition-all"
                                >
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </x-ui.tooltip>
                        </div>
                    </div>
                </li>
            @empty
                <div class="py-24 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-zinc-50 text-zinc-300">
                        <x-lucide-message-square-off class="h-8 w-8" />
                    </div>
                    <p class="mt-4 text-sm font-medium text-zinc-400">Nenhum comentário por aqui ainda.</p>
                </div>
            @endforelse
        </ul>
    </div>

    {{ $this->comments->links() }}

    {{-- Modal de Edição --}}
    <x-ui.modal name="edit-comment-modal" title="Moderar Comentário">
        <form wire:submit="save" class="space-y-6">
            @if($this->form->comment && $this->form->comment->user_id !== auth()->id())
                <div class="flex items-center gap-2">
                    <x-ui.tooltip :text="$this->form->comment->status->description()" position="right">
                        <span @class([
                            'px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border',
                            'bg-emerald-50 text-emerald-600 border-emerald-100' => $this->form->comment->status === CommentStatusEnum::VISIBLE,
                            'bg-red-50 text-red-600 border-red-100' => $this->form->comment->status === CommentStatusEnum::HIDDEN,
                            'bg-amber-50 text-amber-600 border-amber-100' => $this->form->comment->status === CommentStatusEnum::PENDING,
                        ])>
                            Status Atual: {{ $this->form->comment->status->label() }}
                        </span>
                    </x-ui.tooltip>
                </div>
            @endif

            <div class="space-y-2">
                <label class="text-sm font-bold text-zinc-700">Conteúdo do Comentário</label>
                @if($this->form->comment && $this->form->comment->user_id !== auth()->id())
                    <div class="prose prose-sm max-w-none p-4 rounded-2xl bg-zinc-50 border border-zinc-100 text-zinc-600">
                        {!! $this->form->content !!}
                    </div>
                @else
                    <x-ui.rich-editor
                        wire:model="form.content"
                        placeholder="Edite o comentário..."
                    />
                @endif
                @error('form.content') <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.select label="Status de Visibilidade" wire:model="form.status" :error="$errors->first('form.status')">
                    @foreach(CommentStatusEnum::options() as $opt)
                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <x-ui.button type="button" variant="secondary" x-on:click="show = false">
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit" loading="save">
                    Salvar Alterações
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Confirm Modal --}}
    <x-ui.confirm-modal
        name="confirm-comment-deletion"
        title="Excluir Definitivamente"
        content="Você está prestes a remover este comentário e todas as suas respostas. Esta ação não pode ser revertida."
        buttonText="Confirmar Exclusão"
        variant="danger"
        action="delete"
    />
</div>
