@use(App\Enums\ThemePlatformEnum)
@use(App\Enums\ProfileVisibilityEnum)

<div class="space-y-10 pb-20">
    <form wire:submit="save" class="space-y-10">
        {{-- Seção Visual --}}
        <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 shadow-sm">
            <div class="relative h-48 bg-zinc-100 dark:bg-zinc-800">
                @if ($form->cover)
                    <img src="{{ $form->cover->temporaryUrl() }}" class="h-full w-full object-cover" />
                @elseif(auth()->user()->profile?->cover_path)
                    <img src="{{ Storage::url(auth()->user()->profile->cover_path) }}" class="h-full w-full object-cover">
                @endif

                <label class="absolute bottom-4 right-4 cursor-pointer rounded-xl bg-white/90 px-3 py-2 text-xs font-semibold text-zinc-900 backdrop-blur hover:bg-white transition shadow-sm border border-zinc-200/50">
                    <input type="file" wire:model="form.cover" class="sr-only">
                    <span wire:loading.remove wire:target="form.cover">Alterar Capa</span>
                    <span wire:loading wire:target="form.cover" class="flex items-center gap-2 text-zinc-600">
                         <x-lucide-loader-2 class="h-3 w-3 animate-spin" /> Processando...
                    </span>
                </label>
            </div>

            <div class="relative px-8 pb-8">
                <div class="-mt-12 mb-6 relative inline-block">
                    <div class="h-24 w-24 overflow-hidden rounded-3xl border-4 border-white dark:border-zinc-900 bg-zinc-100 dark:bg-zinc-800 shadow-md">
                        @if ($form->avatar)
                            <img src="{{ $form->avatar->temporaryUrl() }}" class="h-full w-full object-cover">
                        @elseif(auth()->user()->profile?->avatar_path)
                            <img src="{{ Storage::url(auth()->user()->profile->avatar_path) }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center text-2xl font-bold text-zinc-300">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <label class="absolute -bottom-1 -right-1 cursor-pointer rounded-full bg-zinc-900 p-2 text-white shadow-lg hover:bg-zinc-800 transition active:scale-95">
                        <input type="file" wire:model="form.avatar" class="sr-only">
                        <x-lucide-camera wire:loading.remove wire:target="form.avatar" class="h-4 w-4" />
                        <x-lucide-loader-2 wire:loading wire:target="form.avatar" class="h-4 w-4 animate-spin" />
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 mb-2">
                    <x-ui.input
                        wire:model.blur="form.name"
                        label="Nome"
                        placeholder="John Doe"
                        :error="$errors->first('form.name')"
                    />
                    <x-ui.input
                        wire:model.blur="form.email"
                        label="E-Mail"
                        placeholder="johndoe@example.com"
                        :error="$errors->first('form.email')"
                    />
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-ui.input
                        wire:model.blur="form.username"
                        label="Nome de usuário"
                        prefix="@"
                        placeholder="exemplo"
                        :error="$errors->first('form.username')"
                    />
                    <x-ui.input
                        wire:model.blur="form.website_url"
                        label="Website"
                        placeholder="https://exemplo.com"
                        :error="$errors->first('form.website_url')"
                    />
                </div>
            </div>
        </section>

        {{-- Seção de Informações (Bio/Localização) --}}
        <x-ui.section-card title="Sobre você" description="Conte aos outros quem você é e onde você está.">
            <div class="space-y-6">
                <div>
                    <x-ui.textarea
                        label="Bio"
                        wire:model.blur="form.bio"
                        :error="$errors->first('form.bio')"
                    />
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-ui.select wire:model.live="selectedUf" label="Estado (UF)">
                        <option value="">Selecione o estado</option>
                        @foreach($ufs as $uf)
                            <option value="{{ $uf['sigla'] }}">{{ $uf['nome'] }}</option>
                        @endforeach
                    </x-ui.select>

                    <div class="relative">
                        <x-ui.select wire:model="form.location" label="Cidade" wire:loading.attr="disabled" wire:target="selectedUf" :error="$errors->first('form.location')">
                            <option value="">{{ $selectedUf ? 'Selecione a cidade' : 'Selecione um estado primeiro' }}</option>
                            @foreach($this->municipios as $municipio)
                                <option value="{{ $municipio['nome'] }}, {{ $selectedUf }}">{{ $municipio['nome'] }}</option>
                            @endforeach
                        </x-ui.select>
                        <div wire:loading wire:target="selectedUf" class="absolute right-10 top-[42px]">
                            <x-lucide-loader-2 class="h-4 w-4 animate-spin text-zinc-400" />
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.section-card>

        {{-- Personalização --}}
        <x-ui.section-card title="Personalização de Estilo" description="Isso afetará como os visitantes veem seu perfil e posts.">
            <div class="space-y-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div>
                        <label class="mb-3 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Cor Principal</label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.primary_color" class="h-12 w-20 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1">
                            <span class="text-sm font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->primary_color }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-3 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Cor de Destaque</label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.accent_color" class="h-12 w-20 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1">
                            <span class="text-sm font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->accent_color }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <x-ui.select wire:model="form.theme_mode" label="Modo de Cor (Tema)">
                            @foreach(ThemePlatformEnum::options() as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>
            </div>
        </x-ui.section-card>

        {{-- Seção de Privacidade --}}
        <x-ui.section-card title="Privacidade" description="Controle quem pode encontrar e ver as informações do seu perfil.">
            <div class="space-y-8">
                {{-- Grid para alinhar os controles de forma limpa --}}
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">

                    {{-- Coluna 1: Controle de E-mail (Toggle Style) --}}
                    <div class="flex flex-col justify-between rounded-2xl border border-zinc-100 bg-zinc-50/50 p-5 transition-colors hover:border-zinc-200">
                        <x-ui.checkbox
                            wire:model="form.show_email_publicly"
                            label="Exibir e-mail publicamente"
                            description="Seu endereço ({{ $form->email }}) será visível para qualquer visitante."
                            :error="$errors->first('form.show_email_publicly')"
                        />
                    </div>

                    {{-- Coluna 2: Seletor de Visibilidade --}}
                    <div class="flex flex-col justify-center rounded-2xl border border-zinc-100 bg-zinc-50/50 p-5 transition-colors hover:border-zinc-200">
                        <x-ui.select
                            wire:model="form.visibility"
                            label="Visibilidade do perfil"
                            description="Define quem pode acessar sua estante pública."
                            :error="$errors->first('form.visibility')"
                            class="w-full"
                        >
                            @foreach(ProfileVisibilityEnum::options() as $visibility)
                                <option value="{{ $visibility['value'] }}">{{ $visibility['label'] }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>

                {{-- Badge de Status (Feedback Visual Sênior) --}}
                <div class="flex items-center gap-2 px-1 text-[10px] font-bold uppercase tracking-widest text-zinc-400">
                    <x-lucide-shield-check class="h-3.5 w-3.5 {{ $form->visibility === 'public' ? 'text-emerald-500' : 'text-amber-500' }}" />
                    Status Atual:
                    <span class="{{ $form->visibility === 'public' ? 'text-emerald-600' : 'text-amber-600' }}">
                {{ $form->visibility === 'public' ? 'Perfil Descoberto' : 'Perfil Restrito' }}
            </span>
                </div>
            </div>
        </x-ui.section-card>

        <x-ui.section-card title="Visibilidade e SEO" description="Aumente seu alcance ou mantenha-se discreto.">
            <x-ui.checkbox
                wire:model="form.is_searchable"
                label="Permitir indexação por mecanismos de busca"
                description="Ao desativar, o Google e outros buscadores serão instruídos a não exibir seu perfil nos resultados."
                :error="$errors->first('form.is_searchable')"
            />
        </x-ui.section-card>

        <div class="flex justify-end gap-3 border-t border-zinc-100 dark:border-zinc-800 pt-8">
            <x-ui.button type="submit" loading="save" class="w-full md:w-auto">
                Atualizar Perfil
            </x-ui.button>
        </div>
    </form>
</div>
