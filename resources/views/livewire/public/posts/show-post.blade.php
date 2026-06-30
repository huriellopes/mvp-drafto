<div class="mx-auto max-w-7xl px-4 py-12 md:py-20 transition-colors duration-500 bg-white dark:bg-zinc-950"
     x-data="{
        progress: 0,
        updateProgress() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            this.progress = (winScroll / height) * 100;
        }
     }"
     x-init="window.onscroll = () => updateProgress()"
>
    {{-- Reading Progress Bar --}}
    <div class="fixed top-0 left-0 w-full h-1.5 z-[100] bg-zinc-100/10 backdrop-blur-sm pointer-events-none">
        <div class="h-full bg-indigo-600 dark:bg-indigo-500 shadow-[0_0_15px_rgba(79,70,229,0.5)] transition-all duration-150"
             :style="'width: ' + progress + '%'"></div>
    </div>

    {{-- Header Section --}}
    <header class="mb-16 space-y-10 text-left">
        <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Início</a>
            <x-lucide-chevron-right class="h-3 w-3"/>
            <a href="{{ route('posts.explore', ['category' => $this->post?->category?->slug]) }}"
               wire:navigate
               class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                {{ $this->post?->category?->name }}
            </a>
        </nav>

        <h1 class="max-w-5xl text-5xl font-black leading-tight text-zinc-900 dark:text-white md:text-7xl tracking-tighter italic">
            {{ $this->post->title }}
        </h1>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 py-10 border-y border-zinc-100 dark:border-zinc-800/50">
            <div class="flex flex-wrap items-center gap-5 md:gap-10">
                {{-- Autor Info --}}
                <div class="flex items-center gap-5">
                    <a href="{{ route('profile.show', $this->post->author->profile->username) }}" wire:navigate class="group relative">
                        <div class="absolute -inset-1 bg-indigo-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition"></div>
                        <img src="{{ $this->post->author->profile->avatar_path ? Storage::url($this->post->author->profile->avatar_path) : 'https://ui-avatars.com/api/?name='.$this->post->author->display_name }}"
                             class="relative h-14 w-14 rounded-2xl object-cover ring-4 ring-white dark:ring-zinc-900 shadow-2xl transition-transform group-hover:scale-105"
                             alt="{{ $this->post->author->display_name }}"
                        >
                    </a>
                    <div class="space-y-1">
                        <p class="text-base font-black text-zinc-900 dark:text-white leading-none">
                            {{ $this->post?->author?->display_name }}
                        </p>
                        <p class="text-xs text-zinc-500 font-bold uppercase tracking-widest mt-1">
                            {{ $this->post->published_at->translatedFormat('d \d\e F, Y') }}
                        </p>
                    </div>
                </div>

                {{-- Stats Section --}}
                <div class="flex items-center gap-6 text-sm font-bold text-zinc-500 dark:text-zinc-400 border-l border-zinc-100 dark:border-zinc-800 pl-6 md:pl-10">
                    <div class="flex items-center gap-2">
                        <x-lucide-clock class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                        <span>Leitura de {{ $this->post->reading_time }} min</span>
                    </div>

                    <div class="h-1 w-1 rounded-full bg-zinc-300 dark:bg-zinc-700"></div>

                    <div class="flex items-center gap-2">
                        <x-lucide-eye class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                        <span>{{ number_format($this->post->views_count, 0, ',', '.') }} visualizações</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-ui.report-button type="App\Models\Post" :id="$this->post->id" />
                <x-ui.share-post :post="$this->post"/>
                <livewire:actions.save-button :post="$this->post" :key="'save-'.$this->post->id"/>
                <livewire:actions.like-button :post="$this->post" :key="'like-'.$this->post->id"/>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-20">
        <main class="lg:col-span-8 min-w-0">
            @if($this->post->cover_image_path)
                <div class="relative mb-20 overflow-hidden rounded-[3.5rem] shadow-2xl border border-zinc-100 dark:border-zinc-800">
                    <img src="{{ $this->post->cover_image_url }}"
                         alt="{{ $this->post->title }}"
                         loading="eager" fetchpriority="high" decoding="async"
                         class="w-full object-cover max-h-[600px] hover:scale-[1.02] transition-transform duration-1000">
                </div>
            @else
                <div class="relative mb-20 overflow-hidden rounded-[3.5rem] shadow-xl border border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 aspect-[21/9] flex items-center justify-center select-none">
                     <span class="text-9xl font-black text-indigo-500/10 tracking-tighter uppercase">
                         {{ get_initials($this->post->title) }}
                     </span>
                </div>
            @endif

            @if($this->canReadContent)
                <article class="prose prose-zinc prose-lg lg:prose-xl dark:prose-invert max-w-none min-w-0 break-words
                                prose-headings:font-black prose-headings:tracking-tighter
                                prose-p:leading-relaxed prose-p:text-zinc-600 dark:prose-p:text-zinc-400
                                prose-a:text-indigo-600 dark:prose-a:text-indigo-400 prose-a:font-bold prose-a:no-underline hover:prose-a:underline
                                prose-img:max-w-full prose-pre:overflow-x-auto prose-pre:max-w-full">
                    {!! $this->renderedContent !!}
                </article>

                {{-- Related Posts --}}
                @if($this->relatedPosts->isNotEmpty())
                    <section class="mt-32 space-y-12">
                        <div class="flex items-center gap-6">
                            <h3 class="text-3xl font-black tracking-tighter text-zinc-900 dark:text-white italic">
                                Continue <span class="text-indigo-600 dark:text-indigo-400">Lendo</span>
                            </h3>
                            <div class="h-px flex-1 bg-zinc-100 dark:bg-zinc-800"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            @foreach($this->relatedPosts as $related)
                                <div class="group flex flex-col gap-4">
                                    <a href="{{ route('posts.show', $related->slug) }}"
                                       wire:navigate
                                       class="relative aspect-video overflow-hidden rounded-[2rem] border border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center">
                                        @if($related->cover_image_url)
                                            <img src="{{ $related->cover_image_url }}"
                                                 alt="{{ $related->title }}"
                                                 loading="lazy"
                                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        @else
                                            <span class="text-4xl font-black text-indigo-500/10 tracking-tighter uppercase select-none">
                                                {{ get_initials($related->title) }}
                                            </span>
                                        @endif
                                        <div class="absolute inset-0 bg-black/5 group-hover:bg-transparent transition-colors"></div>
                                    </a>

                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-zinc-400">
                                            <span class="text-indigo-600 dark:text-indigo-400">{{ $related?->category?->name }}</span>
                                            <span>•</span>
                                            <span>{{ $related->published_at->diffForHumans() }}</span>
                                        </div>
                                        <h4 class="font-bold text-zinc-900 dark:text-white leading-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors tracking-tight">
                                            <a href="{{ route('posts.show', $related->slug) }}" wire:navigate>{{ $related->title }}</a>
                                        </h4>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @else
                {{-- PAYWALL SOCIAL --}}
                <div class="rounded-[4rem] bg-zinc-950 p-16 text-center border border-zinc-800 shadow-3xl overflow-hidden relative">
                    <div class="absolute inset-0 bg-gradient-to-b from-indigo-500/5 to-transparent"></div>
                    <div class="relative z-10 space-y-8">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[2rem] bg-white/5 border border-white/10 backdrop-blur-xl">
                            <x-lucide-lock class="h-10 w-10 text-indigo-400"/>
                        </div>
                        <div class="space-y-4">
                            <h3 class="text-4xl font-black text-white tracking-tighter">Conteúdo Reservado</h3>
                            <p class="mx-auto max-w-md text-zinc-500 font-medium leading-relaxed">
                                Este autor compartilha este conhecimento exclusivamente com sua rede de seguidores. Siga
                                agora para desbloquear.
                            </p>
                        </div>
                        <div class="pt-6">
                            <livewire:actions.follow-button :user="$this->post->author"
                                                            :key="'paywall-'.$this->post->author->id"/>
                        </div>
                    </div>
                </div>
            @endif

            @if($post->comments_enabled)
                    <livewire:public.posts.post-comments :post="$post" />
            @endif
        </main>

        {{-- Sidebar --}}
        <aside class="lg:col-span-4">
            <div class="sticky top-32 space-y-8">
                {{-- Autor Card --}}
                <x-public.author-badge :user="$this->post->author" />

                {{-- Newsletter Component --}}
                <livewire:public.newsletter-form :categoryId="$this->post->category_id"/>
            </div>
        </aside>
    </div>
</div>
