@props(['tag'])

@php
    $isGlobal = is_null($tag->user_id);
@endphp

<div {{ $attributes->merge(['class' => 'group relative flex flex-col justify-between rounded-[2rem] border border-zinc-200 bg-white p-6 transition-all hover:border-indigo-500/30 hover:shadow-xl dark:bg-zinc-900 dark:border-zinc-800']) }}>
    <div class="space-y-3">
        <div class="flex items-start justify-between">
            <div @class([
                'h-12 w-12 flex items-center justify-center rounded-2xl transition-colors',
                'bg-zinc-50 dark:bg-zinc-800 text-zinc-400 group-hover:bg-indigo-500/10 group-hover:text-indigo-600' => !$isGlobal,
                'bg-zinc-900 text-white' => $isGlobal,
            ])>
                <x-lucide-tag class="h-6 w-6" />
            </div>

            @if(!$isGlobal)
                <div class="flex gap-1">
                    <button wire:click="edit({{ $tag->id }})" class="p-2 text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition">
                        <x-lucide-pencil class="h-4 w-4" />
                    </button>
                    <button wire:click="confirmDelete({{ $tag->id }})" class="p-2 text-zinc-400 hover:text-red-600 transition">
                        <x-lucide-trash-2 class="h-4 w-4" />
                    </button>
                </div>
            @else
                <div class="px-3 py-1 rounded-full bg-zinc-100 dark:bg-zinc-800 text-[8px] font-black uppercase tracking-widest text-zinc-500">
                    Global
                </div>
            @endif
        </div>

        <div>
            <h3 class="text-lg font-black text-zinc-900 dark:text-white tracking-tighter italic">#{{ $tag->name }}</h3>
            <p class="mt-1 text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
                /{{ $tag->slug }}
            </p>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-between pt-4 border-t border-zinc-50 dark:border-zinc-800/50">
        <div class="flex items-center gap-1.5">
            <x-lucide-library class="h-3 w-3 text-zinc-400" />
            <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500">
                {{ $tag->posts_count }} {{ str('Publicação')->plural($tag->posts_count) }}
            </span>
        </div>
        
        @if($tag->user_id)
             <span class="text-[9px] font-bold text-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded-md">Personalizada</span>
        @endif
    </div>
</div>
