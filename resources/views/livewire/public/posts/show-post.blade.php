<div class="mx-auto max-w-7xl px-4 py-12 md:py-20 transition-colors duration-500">
    {{-- Breadcrumb & Categoria --}}
    <nav class="mb-8 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-zinc-400">
        <a href="{{ route('home') }}" class="hover:text-profile-primary transition">Início</a>
        <x-lucide-chevron-right class="h-3 w-3" />
        <a href="{{ route('posts.explore', ['cat' => $this->post->category->slug]) }}" class="hover:text-profile-primary transition">
            {{ $this->post->category->name }}
        </a>
    </nav>

    <header class="mb-12 space-y-8 text-left">
        <h1 class="max-w-5xl text-4xl font-black leading-[1.1] text-zinc-900 dark:text-white md:text-7xl tracking-tighter">
            {{ $this->post->title }}
        </h1>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 py-8 border-y border-zinc-100 dark:border-zinc-800">
            {{-- Autor Info --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('profile.show', $this->post->author->profile->username) }}" class="relative group">
                    <div class="absolute -inset-1 bg-profile-primary/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition"></div>
                    <img src="{{ Storage::url($this->post->author->profile->avatar_path) }}" class="relative h-12 w-12 rounded-xl object-cover ring-2 ring-white dark:ring-zinc-900 shadow-lg">
                </a>
                <div>
                    <p class="text-sm font-black text-zinc-900 dark:text-white">
                        <a href="{{ route('profile.show', $this->post->author->profile->username) }}" class="hover:text-profile-primary transition">
                            {{ $this->post->author->name }}
                        </a>
                    </p>
                    <p class="text-xs text-zinc-500 font-medium italic">Publicado {{ $this->post->published_at->diffForHumans() }}</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                <x-ui.button variant="secondary" size="sm" @click="$dispatch('open-modal', { name: 'share-post' })">
                    <x-lucide-share-2 class="h-4 w-4" />
                </x-ui.button>
                <livewire:actions.save-button :post="$this->post" :key="'save-'.$this->post->id" />
                <livewire:actions.like-button :post="$this->post" :key="'like-'.$this->post->id" />
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
        <main class="lg:col-span-8">

            {{-- Imagem de Capa --}}
            @if($this->post->cover_image_path)
                <div class="relative mb-16 overflow-hidden rounded-[3rem] shadow-2xl">
                    <img src="{{ $this->post->cover_image_url }}" class="w-full object-cover max-h-[500px]">
                </div>
            @endif

            {{-- Conteúdo com Check de Visibilidade --}}
            @if($this->canReadContent)
                <article class="prose prose-zinc prose-lg lg:prose-xl dark:prose-invert max-w-none
                                prose-headings:font-black prose-headings:tracking-tighter
                                prose-p:leading-relaxed prose-a:text-profile-primary prose-a:no-underline hover:prose-a:underline">
                    {!! $this->post->content !!}
                </article>

                {{-- Tags do Post --}}
                <div class="mt-16 flex flex-wrap gap-2 py-8 border-t border-zinc-100 dark:border-zinc-800">
                    @foreach($this->post->tags as $tag)
                        <a href="{{ route('posts.explore', ['tag' => $tag->slug]) }}" class="px-4 py-2 rounded-full bg-zinc-50 dark:bg-zinc-900 text-xs font-bold text-zinc-500 hover:bg-profile-primary hover:text-white transition">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @else
                {{-- PAYWALL SOCIAL --}}
                <div class="relative overflow-hidden rounded-[3rem] bg-zinc-900 p-12 text-center text-white shadow-3xl">
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-profile-primary via-transparent to-transparent"></div>

                    <div class="relative z-10 space-y-6">
                        <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-xl">
                            <x-lucide-lock class="h-8 w-8 text-profile-primary" />
                        </div>
                        <h3 class="text-3xl font-black tracking-tight">Conteúdo Exclusivo</h3>
                        <p class="mx-auto max-w-md text-zinc-400 font-medium">
                            Este autor reservou este artigo apenas para sua comunidade de seguidores. Siga agora para desbloquear a leitura completa.
                        </p>
                        <div class="pt-4">
                            <livewire:actions.follow-button :user="$this->post->author" :key="'paywall-follow-'.$this->post->author->id" />
                        </div>
                    </div>
                </div>

                {{-- Preview embaçado do conteúdo (efeito visual) --}}
                <div class="mt-8 opacity-10 blur-md pointer-events-none select-none">
                    <p class="mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam...</p>
                    <p>Quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat...</p>
                </div>
            @endif

            {{-- Seção de Comentários --}}
            @if ($this->post->comments_enabled && $this->canReadContent)
                <div class="mt-20">
                    <livewire:public.posts.post-comments :post="$this->post" :key="'comments-'.$this->post->id" />
                </div>
            @endif
        </main>

        {{-- Sidebar do Autor --}}
        <aside class="lg:col-span-4">
            <div class="sticky top-32 space-y-8">
                <div class="rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-8 shadow-sm">
                    <div class="text-center">
                        <img src="{{ Storage::url($this->post->author->profile->avatar_path) }}" class="mx-auto mb-6 h-24 w-24 rounded-[2rem] object-cover shadow-xl ring-4 ring-zinc-50 dark:ring-zinc-800">
                        <h4 class="text-xl font-black text-zinc-900 dark:text-white">{{ $this->post->author->name }}</h4>
                        <p class="text-sm font-bold text-profile-primary italic mb-6">@ {{$this->post->author->profile->username}}</p>

                        <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-8">
                            {{ $this->post->author->profile->bio ?? 'Escritor apaixonado por compartilhar ideias.' }}
                        </p>

                        <livewire:actions.follow-button :user="$this->post->author" :key="'sidebar-follow-'.$this->post->author->id" />
                    </div>
                </div>

                {{-- Newsletter Card --}}
                <div class="overflow-hidden rounded-[2.5rem] bg-profile-primary p-8 text-white shadow-xl shadow-profile-primary/20">
                    <h5 class="text-lg font-black leading-tight">Não perca nenhuma novidade!</h5>
                    <p class="mt-2 text-xs text-white/80 font-medium">Assine a newsletter e receba novos artigos direto no seu e-mail.</p>
                    <div class="mt-6">
                        {{-- Seu componente de newsletter aqui --}}
                        <livewire:public.newsletter-form :categoryId="$this->post->category_id" />
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
