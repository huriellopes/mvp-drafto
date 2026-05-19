@props(['post'])
@use(App\Enums\PostVisibilityEnum)

<article class="group relative flex flex-col overflow-hidden rounded-[2.5rem] border border-zinc-100 bg-white transition-all duration-500 hover:border-indigo-500/50 hover:shadow-[0_20px_50px_-12px_rgba(79,70,229,0.15)] dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-400/50 dark:hover:shadow-[0_20px_50px_-12px_rgba(79,70,229,0.2)]">
    <div class="aspect-video overflow-hidden bg-zinc-100 dark:bg-zinc-800 relative">
        @if($post->cover_image_url)
            <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110">
        @else
            <div class="flex h-full w-full items-center justify-center bg-indigo-500/5 select-none">
                 <span class="text-6xl font-black text-indigo-500/10 tracking-tighter uppercase">
                     {{ get_initials($post->title) }}
                 </span>
            </div>
        @endif

        @if($post->visibility === PostVisibilityEnum::FOLLOWERS_ONLY)
            <div class="absolute left-4 top-4 z-20 flex items-center gap-1.5 rounded-full bg-zinc-950/90 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-white backdrop-blur-sm">
                <x-lucide-lock class="h-3 w-3 text-indigo-400" /> Exclusivo
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-6 sm:p-8">
        <div class="mb-4 flex items-center justify-between">
            <span class="rounded-lg bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20">
                {{ $post->category->name }}
            </span>

            <span @class([
                'flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-tighter border transition-all',
                'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' => $post->isArticle(),
                'bg-blue-50 text-blue-600 border-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20' => $post->isRegularPost(),
            ])>
                @if($post->isArticle()) <x-lucide-scroll class="h-3 w-3" /> @else <x-lucide-file-text class="h-3 w-3" /> @endif
                {{ $post->type->label() }}
            </span>
        </div>

        <h3 class="mb-3 text-xl font-black leading-tight text-zinc-900 dark:text-white transition-colors group-hover:text-indigo-600 dark:group-hover:text-indigo-400 tracking-tighter">
            <a href="{{ route('posts.show', $post->slug) }}" wire:navigate class="after:absolute after:inset-0 focus:outline-none">
                {{ $post->title }}
            </a>
        </h3>

        <p class="mb-6 line-clamp-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400 font-medium">
            {{ $post->excerpt }}
        </p>

        <div class="mt-auto flex items-center justify-between border-t border-zinc-50 pt-6 dark:border-zinc-800/50">
            <div class="flex items-center gap-3">
                <x-ui.avatar 
                    :src="$post->author->profile->avatar_path ? Storage::url($post->author->profile->avatar_path) : null" 
                    :name="$post->author->name" 
                    size="sm" 
                    class="ring-2 ring-zinc-50 dark:ring-zinc-800"
                />
                <div class="flex flex-col">
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-bold text-zinc-900 dark:text-white">{{ format_display_name($post->author->name) }}</span>
                        @if($post->author->isVerified())
                            <x-lucide-badge-check class="h-3 w-3 text-blue-500 fill-blue-500/10" />
                        @endif
                    </div>
                    <span class="text-[10px] text-zinc-400 font-medium mt-0.5">@ {{$post->author->profile->username}}</span>
                </div>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl border border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 text-zinc-400 group-hover:bg-zinc-900 group-hover:text-white dark:group-hover:bg-white dark:group-hover:text-zinc-900">
                <x-lucide-chevron-right class="h-4 w-4" />
            </div>
        </div>
    </div>
</article>
