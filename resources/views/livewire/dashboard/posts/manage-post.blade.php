@use(App\Enums\PostStatusEnum)@use(App\Enums\PostTypeEnum)@use(App\Enums\ModuleEnum)@use(App\Models\Module)<div class="mx-auto w-full max-w-7xl px-4 pb-20 2xl:max-w-[1550px]">
    @if($post?->exists)
        {{ Breadcrumbs::render('dashboard.posts.edit', $post) }}
    @else
        {{ Breadcrumbs::render('dashboard.posts.create') }}
    @endif
    <form wire:submit="save" class="space-y-6">

        {{-- Header Sticky --}}
        <div class="sticky top-0 z-40 -mx-4 border-b border-zinc-200 bg-white/80 px-4 py-4 backdrop-blur-md sm:mx-0 sm:rounded-b-[2rem]">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ $post?->isPublished() ? route('dashboard.posts.index') : route('dashboard.posts.draft') }}"
                       class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-zinc-50 text-zinc-500 ring-1 ring-zinc-200 transition hover:text-zinc-900">
                        <x-lucide-chevron-left class="h-5 w-5" />
                    </a>
                    <div class="min-w-0">
                        <h1 class="truncate text-sm font-bold text-zinc-900">{{ $post?->exists ? 'Editando: ' . $post->title : 'Nova Publicação' }}</h1>
                        <div class="flex items-center gap-2 mt-0.5">
                            <p class="text-xs text-zinc-500">Salve e publique quando estiver satisfeito.</p>
                            @if($post?->exists)
                                <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-zinc-400 border-l border-zinc-200 pl-2">
                                    <div wire:loading wire:target="save" class="flex items-center gap-1.5 text-indigo-600">
                                        <x-lucide-loader-2 class="h-3 w-3 animate-spin" />
                                        Salvando...
                                    </div>
                                    <div wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                                        <x-lucide-check-circle-2 class="h-3 w-3 text-emerald-500" />
                                        @if($lastSavedAt)
                                            Salvo às {{ $lastSavedAt }}
                                        @else
                                            Sincronizado
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-ui.button type="submit" loading="save" class="!bg-white !text-zinc-900 border border-zinc-200 !w-auto px-6 shadow-sm">
                        Salvar Rascunho
                    </x-ui.button>

                    @if(Module::isEnabled(ModuleEnum::POST_SCHEDULER))
                        <x-ui.button type="button" @click="$dispatch('open-modal', { name: 'schedule-modal' })" class="!bg-indigo-50 !text-indigo-600 border border-indigo-100 !w-auto px-6 hover:!bg-indigo-100">
                            <x-lucide-calendar-clock class="h-4 w-4 mr-2" />
                            Agendar
                        </x-ui.button>
                    @endif

                    <x-ui.button type="button" wire:click="publish" loading="publish" class="!w-auto px-8 shadow-lg shadow-zinc-900/10">
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
                            :error="$errors->first('form.title')"
                        />
                        <div class="mt-8 flex gap-4">
                            <span class="inline-flex items-center gap-2 rounded-full bg-zinc-100 px-4 py-1.5 text-[10px] font-bold uppercase tracking-widest text-zinc-600">
                                <x-lucide-type class="h-3 w-3" /> {{ $form->type }}
                            </span>
                        </div>
                    </div>

                    <div class="p-8 sm:p-12 pt-6">
                        <x-ui.quill-editor
                            wire:model="form.content"
                            placeholder="Conte sua história..."
                            uploadUrl="{{ route('trix.attachments.store') }}"
                        />
                        @error('form.content') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
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
                        <x-ui.input label="Slug da URL" wire:model.blur="form.slug" placeholder="link-do-artigo" :error="$errors->first('form.slug')" />

                        <x-ui.suggestion-input
                            label="Categoria"
                            wire:model.live="form.category_id"
                            :available="$categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()->toArray()"
                            placeholder="Pesquisar ou criar categoria..."
                            createMessage="Sugestão de nova categoria:"
                            :error="$errors->first('form.category_id')"
                        />

                        <x-ui.suggestion-input
                            label="Tags"
                            wire:model.live="form.tags"
                            :available="$availableTags->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values()->toArray()"
                            placeholder="Adicionar tag..."
                            multiple="true"
                            createMessage="Sugestão de nova tag:"
                            :error="$errors->first('form.tags')"
                        />

                        <x-ui.select label="Tipo de conteúdo" wire:model.live="form.type">
                            @foreach(PostTypeEnum::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.textarea label="Resumo (SEO)" wire:model.blur="form.excerpt" rows="3" :error="$errors->first('form.excerpt')" />

                        <div class="flex items-center justify-between rounded-2xl bg-zinc-50 p-4 ring-1 ring-zinc-100">
                            <span class="text-xs font-bold text-zinc-700 uppercase tracking-wider">Comentários</span>
                            <input type="checkbox" wire:model.live="form.comments_enabled" class="h-5 w-5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                        </div>
                    </div>

                    {{-- SEO --}}
                    @if(auth()->user()->getModuleSetting(ModuleEnum::MY_POSTS, 'enable_seo', true))
                        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-zinc-900 leading-none italic">Otimização (SEO)</h3>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live="form.seo_enabled" class="h-5 w-5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 transition">
                                </div>
                            </div>

                            @if($form->seo_enabled)
                                <div class="space-y-4 pt-4 border-t border-zinc-100 animate-in fade-in duration-300">
                                    <x-ui.input
                                        label="Título SEO Customizado"
                                        wire:model.blur="form.seo_title"
                                        placeholder="Título para o Google"
                                        description="Recomendado: 50-60 caracteres"
                                        :error="$errors->first('form.seo_title')"
                                    />
                                    <x-ui.textarea
                                        label="Descrição SEO (Meta Description)"
                                        wire:model.blur="form.seo_description"
                                        rows="3"
                                        placeholder="Resumo para atrair cliques nos resultados de busca"
                                        description="Recomendado: 120-160 caracteres"
                                        :error="$errors->first('form.seo_description')"
                                    />
                                    <p class="text-[10px] text-zinc-400 italic font-medium leading-relaxed">
                                        <x-lucide-info class="h-3 w-3 inline mr-0.5" />
                                        Deixe em branco para usar automaticamente o título e resumo originais do artigo.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </form>

    {{-- Modal de Agendamento --}}
    <x-ui.modal name="schedule-modal" wire:model="showScheduleModal" title="Agendar Publicação" max-width="lg">
        <div class="p-6 space-y-6">
            <div class="rounded-2xl bg-indigo-50 p-4 ring-1 ring-indigo-100 flex gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-indigo-200">
                    <x-lucide-calendar-check class="h-5 w-5 text-indigo-600" />
                </div>
                <div>
                    <h4 class="text-sm font-bold text-indigo-900">Planeje seu lançamento</h4>
                    <p class="text-xs text-indigo-700 leading-relaxed mt-0.5">Escolha o melhor momento para sua audiência ler seu novo conteúdo.</p>
                </div>
            </div>

            <x-ui.input
                type="datetime-local"
                label="Data e Hora da Publicação"
                wire:model="form.published_at"
                :error="$errors->first('form.published_at')"
            />

            <div class="flex flex-col gap-3">
                <x-ui.button wire:click="schedule" loading="schedule" class="w-full">
                    Confirmar Agendamento
                </x-ui.button>
                <x-ui.button @click="show = false" class="!bg-white !text-zinc-500 hover:!text-zinc-900 w-full">
                    Cancelar
                </x-ui.button>
            </div>
        </div>
    </x-ui.modal>
</div>
