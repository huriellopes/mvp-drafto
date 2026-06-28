@use(App\Enums\RoleEnum)
@use(App\Enums\PostStatusEnum)

<x-ui.section-card
    title="{{ auth()->user()->hasRole(RoleEnum::READER) ? __('dashboard.widgets.recent_activity.social_title') : __('dashboard.widgets.recent_activity.title') }}"
    description="{{ auth()->user()->hasRole(RoleEnum::READER) ? __('dashboard.widgets.recent_activity.social_subtitle') : __('dashboard.widgets.recent_activity.subtitle') }}"
>
    <div class="space-y-4">
        @forelse ($this->items as $post)
            <div wire:key="recent-post-{{ $post?->id ?? $loop->index }}" class="group relative flex items-center gap-4 rounded-3xl border border-zinc-100 bg-white p-4 transition-all hover:border-indigo-500/30 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">

                {{-- Thumbnail do Post --}}
                <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-zinc-100 dark:bg-zinc-800">
                    @if($post?->cover_image_url)
                        <img
                            src="{{ $post->cover_image_url }}"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                            alt="{{ $post->title }}"
                        />
                    @else
                        <div class="flex h-full w-full items-center justify-center">
                            <x-lucide-image class="h-6 w-6 text-zinc-300 dark:text-zinc-700" />
                        </div>
                    @endif

                    {{-- Badge de Status (para Escritores/Admin) --}}
                    @if(!auth()->user()->hasRole(RoleEnum::READER) && $post?->status)
                        <div class="absolute inset-x-0 bottom-0 bg-black/60 py-0.5 text-center text-[8px] font-black uppercase tracking-widest text-white backdrop-blur-xs">
                            {{ $post->status->label() }}
                        </div>
                    @endif
                </div>

                {{-- Informações do Conteúdo --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        @if($post?->category)
                            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ $post->category->name }}</span>
                            <span class="text-[10px] text-zinc-300 dark:text-zinc-700">•</span>
                        @endif
                        <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase">{{ $post?->created_at?->diffForHumans() ?? '' }}</span>
                    </div>

                    <h4 class="truncate text-sm font-bold text-zinc-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                        {{ $post?->title ?? 'Sem título' }}
                    </h4>

                    {{-- Info do Autor (para Leitores/Admin) --}}
                    @if(auth()->user()->hasRole(RoleEnum::READER) || auth()->user()->isAdmin())
                        <div class="mt-2 flex items-center gap-2">
                            <div class="h-5 w-5 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                <img src="{{ $post?->author?->profile?->avatar_path ? Storage::url($post->author->profile->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($post?->author?->name ?? 'User') }}" class="h-full w-full object-cover">
                            </div>
                            <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">{{ $post?->author?->name ?? 'Usuário' }}</span>
                        </div>
                    @else
                        {{-- Stats para Escritores --}}
                        <div class="mt-2 flex items-center gap-4 text-[11px] font-bold text-zinc-400 dark:text-zinc-500">
                            <span class="flex items-center gap-1"><x-lucide-eye class="h-3 w-3" /> {{ number_format($post?->views_count ?? 0) }}</span>
                            <span class="flex items-center gap-1"><x-lucide-heart class="h-3 w-3" /> {{ number_format($post?->likes_count ?? 0) }}</span>
                        </div>
                    @endif
                </div>

                {{-- Ações e Interações --}}
                <div class="flex flex-col items-end gap-2 shrink-0">
                    <div class="flex items-center gap-1.5">
                        @if($post && $post->is_liked)
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 dark:bg-red-900/20" title="Você curtiu">
                                <x-lucide-heart class="h-3.5 w-3.5 fill-current" />
                            </div>
                        @endif
                        @if($post && $post->is_saved)
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20" title="Você salvou">
                                <x-lucide-bookmark class="h-3.5 w-3.5 fill-current" />
                            </div>
                        @endif

                        @if($post?->slug)
                            <a href="{{ route('posts.show', $post->slug) }}" target="_blank"
                               class="flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-900 text-white shadow-lg transition hover:scale-110 active:scale-95 dark:bg-white dark:text-zinc-900">
                                <x-lucide-chevron-right class="h-4 w-4" />
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-50 dark:bg-zinc-900">
                    <x-lucide-history class="h-8 w-8 text-zinc-200 dark:text-zinc-800" />
                </div>
                <p class="text-sm font-bold text-zinc-500 dark:text-zinc-400">{{ __('dashboard.widgets.recent_activity.empty') }}</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ __('dashboard.widgets.recent_activity.empty_subtitle') }}</p>
                @if(auth()->user()->hasRole(RoleEnum::READER))
                    <a href="{{ route('posts.explore') }}" target="_blank" class="mt-4 text-xs font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('dashboard.widgets.recent_activity.explore') }}</a>
                @endif
            </div>
        @endforelse
    </div>
</x-ui.section-card>
