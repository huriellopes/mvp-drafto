@php
    $profile = $this->user->profile;
    $collection = $this->postCollection;
@endphp

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950"
     style="background-color: var(--profile-bg); color: var(--profile-text);">
    <div class="max-w-6xl mx-auto px-6 py-16 sm:py-24">
        {{-- Voltar para o perfil --}}
        <a href="{{ route('profile.show', ['username' => $profile->username]) }}"
           wire:navigate
           class="inline-flex items-center gap-2 text-sm font-bold text-zinc-500 dark:text-zinc-400 hover:text-profile-primary transition-colors">
            <x-lucide-arrow-left class="h-4 w-4" />
            @ {{ $profile->username }}
        </a>

        {{-- Cabeçalho da coleção --}}
        <div class="mt-10 space-y-6 animate-in fade-in slide-in-from-bottom-8 duration-700">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-3xl bg-profile-primary/10 text-profile-primary">
                    <x-lucide-folder-tree class="h-7 w-7" />
                </span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Coleção de {{ $profile->display_name }}</p>
                    <h1 class="text-4xl sm:text-5xl font-black text-profile-text tracking-tighter italic">{{ $collection->name }}</h1>
                </div>
            </div>

            @if($collection->description)
                <p class="max-w-2xl text-lg text-zinc-500 dark:text-zinc-400 leading-relaxed">{{ $collection->description }}</p>
            @endif

            <div class="text-sm font-bold text-zinc-400 dark:text-zinc-500">
                {{ $this->posts->total() }} {{ \Illuminate\Support\Str::plural('obra', $this->posts->total()) }}
            </div>
        </div>

        {{-- Grid de posts --}}
        <div class="mt-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($this->posts as $post)
                <div class="hover:-translate-y-2 transition-transform duration-500">
                    <x-public.post-card :post="$post" />
                </div>
            @empty
                <div class="col-span-full py-24 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-zinc-100 dark:bg-zinc-900 text-zinc-400">
                        <x-lucide-folder-open class="h-8 w-8" />
                    </div>
                    <p class="mt-6 text-lg font-bold text-zinc-500 dark:text-zinc-400">Nenhuma obra publicada nesta coleção ainda.</p>
                </div>
            @endforelse
        </div>

        @if($this->posts->hasPages())
            <div class="mt-16">
                {{ $this->posts->links() }}
            </div>
        @endif
    </div>
</div>
