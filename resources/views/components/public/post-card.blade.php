@props(['post'])

<article class="group flex flex-col overflow-hidden rounded-3xl border border-zinc-200 bg-white transition hover:border-zinc-300 hover:shadow-xl">
    @if($post->cover_path)
        <a href="#" class="aspect-video overflow-hidden">
            <img src="{{ Storage::url($post->cover_path) }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        </a>
    @endif

    <div class="flex flex-1 flex-col p-6">
        <div class="mb-3 flex items-center gap-2">
            <span class="rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600">
                {{ $post->category->name }}
            </span>
            <span class="text-xs text-zinc-400">
                {{ $post->published_at->diffForHumans() }}
            </span>
        </div>

        <h3 class="mb-3 text-xl font-bold leading-tight text-zinc-900 group-hover:text-[var(--profile-primary)]">
            <a href="#">{{ $post->title }}</a>
        </h3>

        <p class="mb-6 line-clamp-3 flex-1 text-sm leading-relaxed text-zinc-600">
            {{ $post->excerpt }}
        </p>

        <div class="flex items-center justify-between border-t border-zinc-50 pt-4">
            <span class="flex items-center gap-1.5 text-xs font-medium text-zinc-500">
                <x-lucide-eye class="h-4 w-4" />
                {{ $post->views_count }} visualizações
            </span>

            <x-lucide-arrow-right class="h-5 w-5 text-zinc-300 transition group-hover:translate-x-1 group-hover:text-[var(--profile-primary)]" />
        </div>
    </div>
</article>
