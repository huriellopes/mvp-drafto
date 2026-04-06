@props(['post'])

<article
    @class([
      'group relative flex flex-col overflow-hidden rounded-[2.5rem] border transition-all duration-500',
      'border-zinc-100 bg-white hover:border-profile-primary/50',
      'dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-profile-primary/50'
  ])
    style="--card-shadow: rgba(var(--profile-primary-rgb), 0.15);"
    onmouseover="this.style.boxShadow='0 25px 50px -12px var(--card-shadow)'"
    onmouseout="this.style.boxShadow='none'"
>
    {{-- Container da Imagem --}}
    <div class="aspect-video overflow-hidden bg-zinc-100 dark:bg-zinc-800 relative">
        @if($post->cover_image_url)
            <img
                src="{{ $post->cover_image_url }}"
                alt="{{ $post->title }}"
                class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
            >
        @else
            <div class="flex h-full w-full items-center justify-center bg-profile-primary/5">
                <x-lucide-image class="h-8 w-8 text-profile-primary/20" />
            </div>
        @endif

        {{-- Badge de Seguidores --}}
        @if($post->visibility === App\Enums\PostVisibilityEnum::FOLLOWERS_ONLY)
            <div class="absolute left-4 top-4 z-20 flex items-center gap-1.5 rounded-full bg-zinc-900/90 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-white backdrop-blur">
                <x-lucide-lock class="h-3 w-3 text-profile-primary" />
                Exclusivo
            </div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-b from-black/5 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
    </div>

    {{-- Conteúdo do Card --}}
    <div class="flex flex-1 flex-col p-6 sm:p-8">
        <div class="mb-4 flex items-center justify-between">
            <span style="background-color: rgba(var(--profile-primary-rgb), 0.1); color: var(--profile-primary)"
                  class="rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider">
                {{ $post->category->name }}
            </span>
            <span class="text-[10px] font-bold text-zinc-400 uppercase">
                {{ $post->published_at->diffForHumans() }}
            </span>
        </div>

        <h3 class="mb-3 text-xl font-bold leading-tight text-zinc-900 dark:text-white transition-colors group-hover:text-profile-primary">
            <a href="{{ route('posts.show', $post->slug) }}" class="after:absolute after:inset-0 focus:outline-none">
                {{ $post->title }}
            </a>
        </h3>

        <p class="mb-6 line-clamp-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
            {{ $post->excerpt }}
        </p>

        <div class="mt-auto flex items-center justify-between border-t border-zinc-50 pt-6 dark:border-zinc-800/50">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-xl overflow-hidden bg-zinc-100">
                    <img src="{{ $post->author->profile->avatar_path ? Storage::url($post->author->profile->avatar_path) : 'https://ui-avatars.com/api/?name='.$post->author->name }}" class="object-cover">
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-zinc-900 dark:text-white">{{ $post->author->name }}</span>
                    <span class="text-[10px] text-zinc-400">@ {{$post->author->profile->username}}</span>
                </div>
            </div>

            <div @class([
                'flex h-10 w-10 items-center justify-center rounded-2xl transition-all duration-300 border border-zinc-100',
                'bg-zinc-50 text-zinc-400 group-hover:bg-zinc-900 group-hover:text-white group-hover:border-zinc-900 dark:bg-zinc-800 dark:border-zinc-700'
            ])>
                <x-lucide-arrow-right class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
            </div>
        </div>
    </div>
</article>
