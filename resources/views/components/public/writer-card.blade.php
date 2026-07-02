@props(['writer'])

<div {{ $attributes->merge(['class' => 'group relative flex flex-col justify-between rounded-[3rem] border border-zinc-100 bg-white p-8 transition-all hover:shadow-2xl hover:-translate-y-2 dark:bg-zinc-900 dark:border-zinc-800']) }}>
    <div>
        {{-- Avatar --}}
        <div class="relative mx-auto w-24 h-24 mb-6">
            <div class="absolute inset-0 bg-profile-primary/20 rounded-[2rem] rotate-6 group-hover:rotate-12 transition-transform duration-500"></div>
            <div class="relative h-full w-full overflow-hidden rounded-[2rem] bg-zinc-100 ring-4 ring-white dark:ring-zinc-900 shadow-sm">
                <img
                    src="{{ $writer->profile?->avatar_path ? Storage::url($writer->profile?->avatar_path) : 'https://ui-avatars.com/api/?name='.$writer->display_name }}"
                    width="96" height="96"
                    loading="lazy" decoding="async"
                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                    alt="{{ $writer->display_name }}">
            </div>
        </div>

        {{-- Info --}}
        <div class="text-center space-y-1">
            <h3 class="font-black text-zinc-900 dark:text-white truncate text-lg tracking-tight flex items-center justify-center gap-1.5">
                {{ $writer->display_name }}
                @if($writer->isVerified())
                    <x-lucide-badge-check class="h-4 w-4 text-blue-500 fill-blue-500/10" />
                @endif
            </h3>
            @if($writer->profile?->username)
                <p class="text-profile-primary text-xs font-bold italic tracking-wide">
                    {{ '@'.$writer->profile->username }}
                </p>
            @endif
        </div>

        {{-- Bio --}}
        <div class="mt-4 text-center">
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 line-clamp-2 leading-relaxed min-h-[2.5rem]">
                {{ $writer->profile?->bio ?? 'Escrevendo sua história no Drafto...' }}
            </p>
        </div>

        {{-- Stats --}}
        <div class="mt-6 flex items-center justify-around py-4 border-t border-zinc-50 dark:border-zinc-800">
            <div class="text-center">
                <p class="text-sm font-black text-zinc-900 dark:text-white">{{ number_format($writer->published_posts_count) }}</p>
                <p class="text-[9px] font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-widest">Obras</p>
            </div>
            <div class="h-6 w-px bg-zinc-100 dark:bg-zinc-800"></div>
            <div class="text-center">
                <p class="text-sm font-black text-zinc-900 dark:text-white">{{ number_format($writer->followers_count) }}</p>
                <p class="text-[9px] font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-widest">Leitores</p>
            </div>
        </div>
    </div>

    {{-- Área de Ações --}}
    @if(isset($actions))
        <div class="mt-6">
            {{ $actions }}
        </div>
    @elseif($writer->profile?->username)
        <a href="{{ route('profile.show', $writer->profile->username) }}" wire:navigate class="absolute inset-0 z-10" aria-label="Ver perfil"></a>
    @endif
</div>
