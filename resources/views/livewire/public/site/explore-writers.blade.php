<div class="max-w-7xl mx-auto px-4 py-12 lg:py-20 space-y-16">
    {{-- Header de Busca --}}
    <div class="text-center space-y-6 max-w-2xl mx-auto">
        <h1 class="text-4xl md:text-6xl font-black text-zinc-900 dark:text-white tracking-tighter">Descubra novos <span class="text-profile-primary">Escritores.</span></h1>
        <p class="text-zinc-500 font-medium">Conecte-se com autores que compartilham suas paixões e interesses.</p>

        <div class="pt-4">
            <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Buscar por nome ou @username...">
                <x-slot:prefix><x-lucide-search class="h-5 w-5 text-zinc-400" /></x-slot:prefix>
            </x-ui.input>
        </div>
    </div>

    {{-- Grid de Escritores --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8" wire:loading.class="opacity-50">
        @forelse($writers as $writer)
            <div wire:key="writer-{{ $writer->id }}" class="group relative bg-white dark:bg-zinc-900 p-8 rounded-[3rem] border border-zinc-100 dark:border-zinc-800 transition-all hover:shadow-2xl hover:shadow-zinc-200/50 hover:-translate-y-2">
                {{-- Avatar --}}
                <div class="relative mx-auto w-24 h-24 mb-6">
                    <div class="absolute inset-0 bg-profile-primary/20 rounded-[2rem] rotate-6 group-hover:rotate-12 transition-transform"></div>
                    <div class="relative h-full w-full overflow-hidden rounded-[2rem] bg-zinc-100 ring-4 ring-white dark:ring-zinc-900">
                        <img src="{{ $writer->profile->avatar_path ? Storage::url($writer->profile->avatar_path) : 'https://ui-avatars.com/api/?name='.$writer->name }}" class="h-full w-full object-cover">
                    </div>
                </div>

                {{-- Info --}}
                <div class="text-center space-y-1">
                    <h3 class="font-black text-zinc-900 dark:text-white truncate">{{ $writer->name }}</h3>
                    <p class="text-profile-primary text-xs font-bold italic">@ {{$writer->profile->username}}</p>
                </div>

                {{-- Stats --}}
                <div class="mt-6 flex items-center justify-around py-4 border-t border-zinc-50 dark:border-zinc-800">
                    <div class="text-center">
                        <p class="text-sm font-black text-zinc-900 dark:text-white">{{ $writer->published_posts_count }}</p>
                        <p class="text-[9px] font-bold text-zinc-400 uppercase tracking-widest">Posts</p>
                    </div>
                    <div class="h-6 w-px bg-zinc-100 dark:bg-zinc-800"></div>
                    <div class="text-center">
                        <p class="text-sm font-black text-zinc-900 dark:text-white">{{ $writer->followers_count ?? 0 }}</p>
                        <p class="text-[9px] font-bold text-zinc-400 uppercase tracking-widest">Seguidores</p>
                    </div>
                </div>

                {{-- Link --}}
                <a href="{{ route('profile.show', $writer->profile->username) }}" class="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl bg-zinc-900 py-3 text-xs font-bold text-white transition hover:bg-profile-primary dark:bg-white dark:text-zinc-900 dark:hover:bg-profile-primary dark:hover:text-white">
                    Ver Perfil Completo
                    <x-lucide-arrow-right class="h-3 w-3" />
                </a>
            </div>
        @empty
            <div class="col-span-full py-24 text-center">
                <x-lucide-user-x class="mx-auto h-12 w-12 text-zinc-200" />
                <p class="mt-4 text-zinc-400 font-medium italic">Nenhum escritor encontrado com este nome.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-20">
        {{ $writers->links() }}
    </div>
</div>
