@props(['comment', 'replyingTo'])

<div class="group animate-in fade-in duration-500">
    <div class="flex gap-4">
        <a href="{{ route('profile.show', $comment->user->profile->username) }}" class="shrink-0">
            <img src="{{ $comment->user->profile->avatar_path ? Storage::url($comment->user->profile->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($comment->user->name) }}"
                 class="h-10 w-10 rounded-xl object-cover shadow-sm ring-2 ring-white dark:ring-zinc-900 transition-transform hover:scale-105">
        </a>

        <div class="flex-1 min-w-0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 p-4 rounded-2xl rounded-tl-none shadow-sm transition-all group-hover:border-zinc-200 dark:group-hover:border-zinc-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-black text-sm text-zinc-900 dark:text-white truncate tracking-tight">{{ $comment->user->display_name }}</span>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest shrink-0">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400 break-words font-medium">
                    {!! preg_replace('/@([\w\.]+)/u', '<a href="/@$1" class="text-indigo-600 dark:text-indigo-400 font-black hover:underline">@$1</a>', e($comment->content)) !!}
                </div>
            </div>

            <div class="mt-2 flex items-center gap-6 px-2">
                <button type="button" wire:click="setReply({{ $comment->id }})"
                        class="text-[10px] font-black uppercase tracking-widest text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    Responder
                </button>
                <livewire:actions.like-comment :comment="$comment" :key="'like-comment-'.$comment->id" />
                <button type="button" @click="$dispatch('openReportModal', { type: 'App\\Models\\Comment', id: {{ $comment->id }} })"
                        class="text-[10px] font-black uppercase tracking-widest text-zinc-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100">
                    Denunciar
                </button>
            </div>

            {{-- FORMULÁRIO DE RESPOSTA ISOLADO --}}
            @if($replyingTo === $comment->id)
                <div class="mt-4 pl-4 border-l-2 border-indigo-500 animate-in slide-in-from-top-2 duration-300">
                    <form wire:submit.prevent="saveReply" class="space-y-3">
                        <x-ui.textarea
                            wire:model="replyContent" {{-- ISOLAMENTO AQUI --}}
                        @keydown.cmd.enter.stop="$wire.saveReply()"
                            @keydown.ctrl.enter.stop="$wire.saveReply()"
                            placeholder="Escreva sua resposta..."
                            rows="2"
                            class="!rounded-xl text-xs"
                        />
                        @error('replyContent') <span class="text-[10px] text-red-500 font-bold">{{ $message }}</span> @enderror

                        <div class="flex justify-end items-center gap-3">
                            <button type="button" wire:click.stop="cancelReply"
                                    class="text-[10px] font-black uppercase tracking-widest text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition">
                                Cancelar
                            </button>
                            <x-ui.button type="submit" loading="saveReply" class="!h-9 !text-[10px] px-6 !rounded-xl shadow-lg shadow-indigo-500/10">
                                Publicar Resposta
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            @endif

            @if($comment->replies->isNotEmpty())
                <div class="mt-6 ml-2 pl-6 border-l border-zinc-100 dark:border-zinc-800 space-y-8 animate-in fade-in slide-in-from-left-2 duration-500">
                    @foreach($comment->replies as $reply)
                        <x-public.comments.item :comment="$reply" :replyingTo="$replyingTo" wire:key="reply-{{ $reply->id }}" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
