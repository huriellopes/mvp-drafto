@props(['category'])

<div {{ $attributes->merge(['class' => 'group relative flex flex-col justify-between rounded-[2rem] border border-zinc-200 bg-white p-6 transition-all hover:border-profile-primary/30 hover:shadow-xl dark:bg-zinc-900 dark:border-zinc-800']) }}>
    <div class="space-y-3">
        <div class="flex items-start justify-between">
            <div class="h-12 w-12 flex items-center justify-center rounded-2xl bg-zinc-50 dark:bg-zinc-800 text-zinc-400 group-hover:bg-profile-primary/10 group-hover:text-profile-primary transition-colors">
                <x-lucide-folder-open class="h-6 w-6" />
            </div>

            <div class="flex gap-1">
                <button wire:click="edit({{ $category->id }})" class="p-2 text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition">
                    <x-lucide-pencil class="h-4 w-4" />
                </button>
                <button wire:click="confirmDelete({{ $category->id }})" class="p-2 text-zinc-400 hover:text-red-600 transition">
                    <x-lucide-trash-2 class="h-4 w-4" />
                </button>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-black text-zinc-900 dark:text-white tracking-tighter">{{ $category->name }}</h3>
            <p class="mt-1 text-xs text-zinc-500 line-clamp-2 leading-relaxed">
                {{ $category->description ?? 'Sem descrição definida.' }}
            </p>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-between pt-4 border-t border-zinc-50 dark:border-zinc-800/50">
        <div class="flex items-center gap-1.5">
            <x-lucide-layers-3 class="h-3 w-3 text-zinc-400" />
            <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500">
                {{ $category->posts_count }} {{ str('Publicação')->plural($category->posts_count) }}
            </span>
        </div>
        <span class="text-[10px] font-bold text-zinc-300 dark:text-zinc-600">/{{ $category->slug }}</span>
    </div>
</div>
