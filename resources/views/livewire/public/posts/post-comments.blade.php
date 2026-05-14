<section class="mt-20 border-t border-zinc-100 dark:border-zinc-800 pt-16">
    <div class="flex items-center justify-between mb-10">
        <div class="space-y-1">
            <h3 class="text-3xl font-black tracking-tighter text-zinc-900 dark:text-white italic">
                Discussão <span class="text-indigo-600 dark:text-indigo-400">({{ number_format($post->comments_count) }})</span>
            </h3>
            <div class="h-1 w-12 bg-indigo-500 rounded-full"></div>
        </div>
    </div>

    {{-- Formulário Principal --}}
    @if(auth()->check() || $post->comments_enabled)
        <form wire:submit.prevent="save" class="mb-12 space-y-4">
            <x-ui.textarea
                wire:model="form.content"
                @keydown.cmd.enter.stop="$wire.save()"
                @keydown.ctrl.enter.stop="$wire.save()"
                placeholder="O que você achou deste conteúdo?..."
                rows="4"
                class="!rounded-[2rem] shadow-sm focus:shadow-xl focus:shadow-indigo-500/5 transition-all duration-500"
                :error="$errors->first('form.content')"
            />
            <div class="flex justify-end items-center gap-4">
                <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest italic">
                    Pressione <kbd class="px-1 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border">Cmd/Ctrl + Enter</kbd>
                </p>
                <x-ui.button type="submit" loading="save" class="!w-auto px-10 !rounded-2xl shadow-lg">
                    <x-lucide-send class="h-4 w-4 mr-2" />
                    Publicar Comentário
                </x-ui.button>
            </div>
        </form>
    @else
        {{-- Empty State / Login Prompt (Apenas se comentários estiverem totalmente desativados e usuário deslogado) --}}
        @guest
            <div class="mb-12 relative overflow-hidden rounded-[2.5rem] border-2 border-dashed border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/30 p-10 text-center transition-all hover:border-indigo-500/30">
                <div class="relative z-10 space-y-4">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white dark:bg-zinc-900 shadow-sm">
                        <x-lucide-message-square-off class="h-8 w-8 text-zinc-300 dark:text-zinc-700" />
                    </div>
                    <p class="text-zinc-600 dark:text-zinc-400 font-medium">
                        Os comentários foram desativados pelo autor para este conteúdo.
                    </p>
                </div>
            </div>
        @endguest
    @endif

    {{-- Lista de Comentários --}}
    <div class="space-y-10">
        @forelse($this->comments as $comment)
            <x-public.comments.item
                :comment="$comment"
                :replyingTo="$replyingTo"
                wire:key="comment-{{ $comment->id }}"
            />
        @empty
            <div class="py-20 text-center space-y-4">
                <x-lucide-ghost class="mx-auto h-12 w-12 text-zinc-200 dark:text-zinc-800" />
                <p class="text-zinc-400 font-bold italic tracking-tight">Nenhum comentário por enquanto. Seja o primeiro a quebrar o gelo!</p>
            </div>
        @endforelse

        {{-- Paginação (se houver) --}}
        @if($this->comments instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-12">
                {{ $this->comments->links() }}
            </div>
        @endif
    </div>
</section>
