@use(App\Enums\RoleEnum)
@php
    $isReader = auth()->user()?->hasRole(RoleEnum::READER);
@endphp

<x-ui.section-card
    title="Descubra autores"
    description="Sugestões baseadas no seu perfil."
>
    <div @class([
        'grid gap-4',
        'grid-cols-1 md:grid-cols-2' => $isReader,
        'grid-cols-1' => !$isReader
    ])>
        @forelse ($this->suggestions as $writer)
            @if(is_object($writer))
                <div wire:key="suggest-{{ $writer->id }}"
                    @class([
                        'group relative rounded-[2rem] border transition-all hover:shadow-md p-5',
                        'bg-white dark:bg-zinc-900 border-zinc-100 dark:border-zinc-800' => !$isReader,
                        'bg-zinc-50/50 dark:bg-zinc-900/50 border-zinc-50 dark:border-zinc-800 flex items-center gap-4' => $isReader
                    ])>

                    {{-- Layout para SIDEBAR (Stack Vertical) --}}
                    @if(!$isReader)
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-xl ring-2 ring-zinc-50 dark:ring-zinc-800">
                                        <img
                                            src="{{ $writer?->profile?->avatar_path ? Storage::url($writer->profile->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($writer?->name ?? 'User') }}"
                                            class="h-full w-full object-cover"
                                            alt="{{ $writer?->name ?? 'User' }}"
                                        />
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="truncate text-xs font-black text-zinc-900 dark:text-white">{{ $writer?->name ?? 'Usuário' }}</h4>
                                        <p class="text-[9px] font-bold text-zinc-400 uppercase tracking-widest">{{ $writer?->published_posts_count ?? 0 }} Obras</p>
                                    </div>
                                </div>
                                @if($writer?->profile?->username)
                                    <a href="{{ route('profile.show', $writer->profile->username) }}" class="text-zinc-300 hover:text-profile-primary transition">
                                        <x-lucide-chevron-right class="h-4 w-4" />
                                    </a>
                                @endif
                            </div>

                            <div class="w-full">
                                <livewire:actions.follow-button :user="$writer" :key="'suggest-sidebar-'.$writer->id" compact />
                            </div>
                        </div>

                    {{-- Layout para MAIN (Horizontal - Reader) --}}
                    @else
                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl ring-2 ring-white dark:ring-zinc-800">
                            <img src="{{ $writer?->profile?->avatar_path ? Storage::url($writer->profile->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($writer?->name ?? 'User') }}" class="h-full w-full object-cover">
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-sm font-black text-zinc-900 dark:text-white">{{ $writer?->name ?? 'Usuário' }}</h4>
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">{{ $writer?->published_posts_count ?? 0 }} Obras</p>
                            <div class="mt-2">
                                <livewire:actions.follow-button :user="$writer" :key="'suggest-main-'.$writer->id" compact />
                            </div>
                        </div>
                        @if($writer?->profile?->username)
                            <a href="{{ route('profile.show', $writer->profile->username) }}" class="p-1 text-zinc-300 hover:text-profile-primary transition">
                                <x-lucide-chevron-right class="h-4 w-4" />
                            </a>
                        @endif
                    @endif
                </div>
            @endif
        @empty
            <div class="py-6 text-center italic text-xs text-zinc-400">Nenhuma sugestão.</div>
        @endforelse
    </div>

    @if($this->suggestions->isNotEmpty())
        <div class="mt-6 pt-4 border-t border-zinc-50 dark:border-zinc-800">
            <a href="{{ route('writers.explore') }}" class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 hover:text-profile-primary transition flex items-center justify-center gap-2">
                Ver todos <x-lucide-arrow-right class="h-3 w-3" />
            </a>
        </div>
    @endif
</x-ui.section-card>
