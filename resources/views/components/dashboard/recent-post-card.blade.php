@props([
    'post',
])

<article class="rounded-2xl border border-zinc-200 p-4 transition hover:border-zinc-300 hover:bg-zinc-50/70 sm:p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <x-ui.badge :label="$post->type->label()" />
                <x-ui.badge :label="$post->status->label()" />

                @if ($post->category)
                    <x-ui.badge :label="$post->category->name" />
                @endif
            </div>

            <h3 class="truncate text-base font-semibold text-zinc-900 sm:text-lg">
                {{ $post->title }}
            </h3>

            @if ($post->excerpt)
                <p class="mt-2 line-clamp-2 text-sm leading-6 text-zinc-600">
                    {{ $post->excerpt }}
                </p>
            @endif

            <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-zinc-500 sm:text-sm">
                <span>{{ $post->reading_time }} min de leitura</span>
                <span>{{ $post->views_count }} visualizações</span>
                <span>{{ $post->comments_count }} comentários</span>
            </div>
        </div>

        <div class="shrink-0 text-sm text-zinc-500">
            {{ $post->created_at->diffForHumans() }}
        </div>
    </div>
</article>
