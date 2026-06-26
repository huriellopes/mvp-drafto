@props(['post'])
@use(App\Enums\PostVisibilityEnum)
@use(Illuminate\Support\Str)

<article class="group relative flex flex-col overflow-hidden rounded-3xl border border-zinc-200/70 bg-white transition-all duration-500 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-2xl hover:shadow-indigo-500/10 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-500/40">
    <div class="relative overflow-hidden bg-zinc-100 dark:bg-zinc-800">
        @if($post->cover_image_url)
            <img
                src="{{ $post->cover_image_url }}"
                alt="{{ $post->title }}"
                loading="lazy"
                decoding="async"
                class="block aspect-[16/10] w-full rounded-t-3xl rounded-b-none object-cover shadow-none transition-transform duration-700 ease-out group-hover:scale-[1.06]"
            >
        @else
            <div class="flex aspect-[16/10] w-full items-center justify-center bg-gradient-to-br from-indigo-500/10 to-fuchsia-500/10">
                <span class="text-7xl font-black uppercase tracking-tighter text-indigo-500/20">
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
            <span class="rounded-lg border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-400">
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

        <h3 class="mb-3 text-xl font-black leading-tight tracking-tighter text-zinc-900 transition-colors group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
            <a href="{{ route('posts.show', $post->slug) }}" wire:navigate class="after:absolute after:inset-0 focus:outline-none">
                {{ $post->title }}
            </a>
        </h3>

        <p class="mb-6 line-clamp-2 text-sm font-medium leading-relaxed text-zinc-500 dark:text-zinc-400">
            {{ Str::limit(strip_tags((string) $post->excerpt), 150) }}
        </p>

        <div class="mt-auto flex items-center justify-between gap-3 border-t border-zinc-100 pt-6 dark:border-zinc-800/60">
            <div class="flex min-w-0 items-center gap-3">
                <x-ui.avatar
                    :src="$post->author->profile?->avatar_path ? Storage::url($post->author->profile->avatar_path) : null"
                    :name="$post->author->name"
                    size="sm"
                    class="ring-2 ring-zinc-50 dark:ring-zinc-800"
                />
                <div class="flex min-w-0 flex-col">
                    <span class="flex items-center gap-1 text-xs font-bold text-zinc-900 dark:text-white">
                        <span class="truncate">{{ format_display_name($post->author->name) }}</span>
                        @if($post->author->isVerified())
                            <x-lucide-badge-check class="h-3 w-3 shrink-0 fill-blue-500/10 text-blue-500" />
                        @endif
                    </span>
                    @if($post->author->profile?->username)
                        <span class="truncate text-[10px] font-medium text-zinc-400">{{ '@' . $post->author->profile->username }}</span>
                    @endif
                </div>
            </div>

            @if($post->reading_time)
                <span class="flex shrink-0 items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-zinc-400">
                    <x-lucide-clock class="h-3 w-3" /> {{ $post->reading_time }} min
                </span>
            @endif
        </div>
    </div>
</article>
