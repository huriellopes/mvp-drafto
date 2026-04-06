<section class="mt-20 border-t border-zinc-100 dark:border-zinc-800 pt-16">
    <div class="flex items-center justify-between mb-10">
        <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">
            Discussão ({{ $post->comments_count }})
        </h3>
    </div>

    {{-- Formulário Principal --}}
    @auth
        <form wire:submit="save" class="mb-12 space-y-4">
            <textarea
                wire:model="form.content"
                placeholder="O que você achou deste conteúdo?"
                class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 text-sm outline-none focus:border-profile-primary transition min-h-[100px]"
            ></textarea>
            <div class="flex justify-end">
                <x-ui.button loading="save" class="w-auto px-8">Publicar Comentário</x-ui.button>
            </div>
        </form>
    @else
        <div class="mb-12 p-6 rounded-2xl bg-zinc-100 dark:bg-zinc-900 text-center border border-dashed border-zinc-300">
            <p class="text-zinc-600 dark:text-zinc-400">Faça <a href="{{ route('login') }}" class="text-profile-primary font-bold">login</a> para participar da conversa.</p>
        </div>
    @endauth

    {{-- Lista de Comentários --}}
    <div class="space-y-8">
        @foreach($this->comments as $comment)
            <x-public.comments.item :comment="$comment" :replyingTo="$replyingTo" />
        @endforeach
    </div>
</section>
