@props(['comment', 'replyingTo'])

<div class="group">
    <div class="flex gap-4">
        <img src="{{ Storage::url($comment->user->profile->avatar_path) }}" class="h-10 w-10 rounded-xl shrink-0 object-cover shadow-sm">

        <div class="flex-1 min-w-0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 p-4 rounded-2xl rounded-tl-none shadow-sm transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-sm text-zinc-900 dark:text-white truncate">{{ $comment->user->name }}</span>
                    <span class="text-[10px] text-zinc-400 uppercase tracking-widest shrink-0">{{ $comment->created_at->diffForHumans() }}</span>
                </div>

                <div class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed break-words">
                    @php
                        $content = e($comment->content);
                        $contentWithMentions = preg_replace(
                            '/@([\w\.]+)/u',
                            '<a href="/@$1" class="text-profile-primary font-bold hover:underline">@$1</a>',
                            $content
                        );
                    @endphp
                    {!! $contentWithMentions !!}
                </div>
            </div>

            <div class="mt-2 flex items-center gap-6 px-2">
                <button wire:click="setReply({{ $comment->id }})" class="text-xs font-bold text-zinc-400 hover:text-profile-primary transition">
                    Responder
                </button>

                <livewire:actions.like-comment :comment="$comment" :key="'like-comment-'.$comment->id" />
            </div>

            @if($replyingTo === $comment->id)
                <div class="mt-4 pl-4 border-l-2 border-profile-primary animate-in slide-in-from-top-2 duration-200">
                    <form wire:submit="save" class="space-y-3">
                        <textarea
                            wire:model="form.content"
                            placeholder="Escreva sua resposta..."
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-3 text-xs outline-none focus:border-profile-primary transition"
                        ></textarea>
                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="cancelReply" class="text-xs font-bold text-zinc-400 px-3">Cancelar</button>
                            <x-ui.button loading="save" class="h-8 text-[10px] px-6 rounded-lg">Responder</x-ui.button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Respostas Recursivas --}}
            @if($comment->replies->isNotEmpty())
                <div class="mt-6 ml-2 pl-6 border-l border-zinc-100 dark:border-zinc-800 space-y-8">
                    @foreach($comment->replies as $reply)
                        <x-public.comments.item :comment="$reply" :replyingTo="$replyingTo" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
