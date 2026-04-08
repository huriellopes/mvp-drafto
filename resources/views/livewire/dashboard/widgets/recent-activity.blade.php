@use(App\Enums\RoleEnum)

<x-ui.section-card
    title="{{ auth()->user()->hasRole(RoleEnum::READER) ? 'Sua Atividade Social' : 'Atividade Recente' }}"
    description="Artigos que você interagiu recentemente."
>
    <div class="overflow-hidden rounded-2xl border border-zinc-100 bg-white dark:bg-zinc-900/50">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900/80 border-b border-zinc-100 dark:border-zinc-800 text-[10px] font-black uppercase tracking-widest text-zinc-400">
            <tr>
                <th class="px-6 py-4">Artigo</th>
                <th class="px-6 py-4 hidden sm:table-cell">Autor</th>
                <th class="px-6 py-4 text-right">Ação</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
            @forelse ($this->items as $post)
                <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 shrink-0 overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800">
                                <img src="{{ $post->cover_image_url }}" class="h-full w-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-bold text-zinc-900 dark:text-white">{{ $post->title }}</p>
                                <p class="text-[10px] text-zinc-400 uppercase font-bold">{{ $post->category->name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden sm:table-cell">
                        <div class="flex items-center gap-2">
                            <img src="{{ Storage::url($post->author->profile->avatar_path) }}" class="h-6 w-6 rounded-lg object-cover">
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ $post->author->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('posts.show', $post->slug) }}" class="p-2 rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 hover:scale-110 transition-transform">
                                <x-lucide-external-link class="h-4 w-4" />
                            </a>
                        </div>

                        {{-- Badges de interação (visíveis sempre) --}}
                        <div class="flex justify-end gap-1 mt-1 group-hover:hidden">
                            @if($post->likedByUsers->contains(auth()->id()))
                                <x-lucide-heart class="h-3 w-3 text-red-500 fill-current" />
                            @endif
                            @if($post->savedByUsers->contains(auth()->id()))
                                <x-lucide-bookmark class="h-3 w-3 text-profile-primary fill-current" />
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center italic text-zinc-400">
                        Você ainda não interagiu com nenhum artigo.
                        <a href="{{ route('posts.explore') }}" class="text-profile-primary font-bold hover:underline">Explorar agora</a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-ui.section-card>
