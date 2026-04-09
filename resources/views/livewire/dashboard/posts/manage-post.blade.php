@use(App\Enums\PostStatusEnum)
@use(App\Enums\PostTypeEnum)

<div class="mx-auto w-full max-w-7xl px-4 pb-20 2xl:max-w-[1550px]">
    {{ Breadcrumbs::render('dashboard.posts.create') }}
    <form wire:submit="save" class="space-y-6">

        {{-- Header Sticky --}}
        <div class="sticky top-0 z-0 -mx-4 border-b border-zinc-200 bg-white/80 px-4 py-4 backdrop-blur-md sm:mx-0 sm:rounded-b-[2rem]">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ $post?->isPublished() ? route('dashboard.posts.index') : route('dashboard.posts.draft') }}"
                       class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-zinc-50 text-zinc-500 ring-1 ring-zinc-200 transition hover:text-zinc-900">
                        <x-lucide-arrow-left class="h-5 w-5" />
                    </a>
                    <div class="min-w-0">
                        <h1 class="truncate text-sm font-bold text-zinc-900">{{ $post?->exists ? 'Editando: ' . $post->title : 'Nova Publicação' }}</h1>
                        <p class="text-xs text-zinc-500">Salve e publique quando estiver satisfeito.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-ui.button loading="save" class="!bg-white !text-zinc-900 border border-zinc-200 !w-auto px-6 shadow-sm">
                        Salvar agora
                    </x-ui.button>
                    <x-ui.button type="button" wire:click="publish" class="!w-auto px-8 shadow-lg shadow-zinc-900/10">
                        {{ $post?->isPublished() ? 'Atualizar' : 'Publicar' }}
                    </x-ui.button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            {{-- Escrita Principal --}}
            <div class="lg:col-span-8">
                <div class="rounded-[2.5rem] border border-zinc-200 bg-white shadow-sm ring-1 ring-zinc-100">
                    <div class="border-b border-zinc-100 p-8 sm:p-12">
                        <x-ui.input
                            wire:model.live.debounce.500ms="form.title"
                            placeholder="Título da obra..."
                            class="!border-none px-0 !text-5xl !font-black tracking-tight focus:ring-0 sm:!text-6xl"
                        />
                        <div class="mt-8 flex gap-4">
                            <span class="inline-flex items-center gap-2 rounded-full bg-zinc-100 px-4 py-1.5 text-[10px] font-bold uppercase tracking-widest text-zinc-600">
                                <x-lucide-type class="h-3 w-3" /> {{ $form->type }}
                            </span>
                        </div>
                    </div>

                    <div class="p-8 sm:p-12 pt-6">
                        <x-ui.rich-editor
                            wire:model="form.content"
                            placeholder="Conte sua história..."
                            uploadUrl="{{ route('trix.attachments.store') }}"
                        />
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="lg:col-span-4 space-y-6">
                <div class="sticky top-28 space-y-6">
                    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
                        <livewire:dashboard.posts.cover-upload :post="$post" />
                    </div>

                    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm space-y-6">
                        <x-ui.input label="Slug da URL" wire:model="form.slug" placeholder="link-do-artigo" />

                        <x-ui.select label="Categoria" wire:model="form.category_id">
                            <option value="">Escolha uma...</option>
                            
                            <optgroup label="Padrão do Sistema">
                                @foreach($categories->whereNull('user_id') as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </optgroup>

                            @if($categories->whereNotNull('user_id')->count() > 0)
                                <optgroup label="Minhas Categorias">
                                    @foreach($categories->whereNotNull('user_id') as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </x-ui.select>

                        <x-ui.select label="Tipo de conteúdo" wire:model="form.type">
                            @foreach(PostTypeEnum::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.textarea label="Resumo (SEO)" wire:model="form.excerpt" rows="3" />

                        <div class="flex items-center justify-between rounded-2xl bg-zinc-50 p-4 ring-1 ring-zinc-100">
                            <span class="text-xs font-bold text-zinc-700 uppercase tracking-wider">Comentários</span>
                            <input type="checkbox" wire:model="form.comments_enabled" class="h-5 w-5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                        </div>
                    </div>

                    {{-- SEO --}}
                    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-zinc-900">Otimização (SEO)</h3>
                            <input type="checkbox" wire:model.live="form.seo_enabled" class="h-5 w-5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                        </div>

                        @if($form->seo_enabled)
                            <div class="space-y-4 pt-2 border-t border-zinc-100">
                                <x-ui.input label="Título SEO" wire:model="form.seo_title" placeholder="Título para o Google" />
                                <x-ui.textarea label="Descrição SEO" wire:model="form.seo_description" rows="3" placeholder="Pequeno resumo para atrair cliques" />
                                <p class="text-[10px] text-zinc-400 italic">Deixe em branco para usar o título e resumo originais.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>
