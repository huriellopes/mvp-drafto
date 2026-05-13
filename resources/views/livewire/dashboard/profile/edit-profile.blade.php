@use(App\Enums\ThemePlatformEnum)
@use(App\Enums\ProfileVisibilityEnum)
@use(App\Enums\ModuleEnum)

<div class="space-y-10 pb-20">
    {{ Breadcrumbs::render('dashboard.profile') }}

    <form wire:submit="save" class="space-y-10">
        {{-- Seção Visual --}}
        <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 shadow-sm">
            <div class="relative h-48 bg-zinc-100 dark:bg-zinc-800">
                @if ($form->cover && method_exists($form->cover, 'temporaryUrl'))
                    <img src="{{ $form->cover->temporaryUrl() }}" class="h-full w-full object-cover" />
                @elseif(auth()->user()->profile?->cover_path)
                    <img src="{{ Storage::url(auth()->user()->profile->cover_path) }}" class="h-full w-full object-cover">
                @endif

                <label class="absolute bottom-4 right-4 cursor-pointer rounded-xl bg-white/90 px-3 py-2 text-xs font-semibold text-zinc-900 backdrop-blur hover:bg-white transition shadow-sm border border-zinc-200/50">
                    <input type="file" wire:model="form.cover" class="sr-only">
                    <span wire:loading.remove wire:target="form.cover">{{ __('dashboard.profile.edit.visual_section.change_cover') }}</span>
                    <span wire:loading wire:target="form.cover" class="flex items-center gap-2 text-zinc-600">
                         <x-lucide-loader-2 class="h-3 w-3 animate-spin" /> {{ __('dashboard.profile.edit.visual_section.processing') }}
                    </span>
                </label>
            </div>

            <div class="relative px-8 pb-8">
                <div class="-mt-12 mb-6 relative inline-block">
                    <div class="h-24 w-24 overflow-hidden rounded-3xl border-4 border-white dark:border-zinc-900 bg-zinc-100 dark:bg-zinc-800 shadow-md">
                        @if ($form->avatar && method_exists($form->avatar, 'temporaryUrl'))
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
                        label="{{ __('dashboard.profile.edit.visual_section.name_label') }}"
                        placeholder="John Doe"
                        :error="$errors->first('form.name')"
                    />
                    <x-ui.input
                        wire:model.live="form.email"
                        label="{{ __('dashboard.profile.edit.visual_section.email_label') }}"
                        placeholder="johndoe@example.com"
                        :error="$errors->first('form.email')"
                    />
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-ui.input
                        wire:model.blur="form.username"
                        label="{{ __('dashboard.profile.edit.visual_section.username_label') }}"
                        prefix="@"
                        placeholder="exemplo"
                        :error="$errors->first('form.username')"
                    />
                    <x-ui.input
                        wire:model.blur="form.website_url"
                        label="{{ __('dashboard.profile.edit.visual_section.website_label') }}"
                        placeholder="https://exemplo.com"
                        :error="$errors->first('form.website_url')"
                    />
                </div>
            </div>
        </section>

        {{-- Seção de Informações (Bio/Localização) --}}
        <x-ui.section-card title="{{ __('dashboard.profile.edit.about_section.title') }}" description="{{ __('dashboard.profile.edit.about_section.description') }}">
            <div class="space-y-6">
                <div>
                    <x-ui.textarea
                        label="{{ __('dashboard.profile.edit.about_section.bio_label') }}"
                        wire:model.blur="form.bio"
                        :error="$errors->first('form.bio')"
                    />
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-ui.select wire:model.live="selectedUf" label="{{ __('dashboard.profile.edit.about_section.state_label') }}">
                        <option value="">{{ __('dashboard.profile.edit.about_section.select_state') }}</option>
                        @foreach($ufs as $uf)
                            <option value="{{ $uf['sigla'] }}">{{ $uf['nome'] }}</option>
                        @endforeach
                    </x-ui.select>

                    <div class="relative">
                        <x-ui.select wire:model="form.location" label="{{ __('dashboard.profile.edit.about_section.city_label') }}" wire:loading.attr="disabled" wire:target="selectedUf" :error="$errors->first('form.location')">
                            <option value="">{{ $selectedUf ? __('dashboard.profile.edit.about_section.select_city') : __('dashboard.profile.edit.about_section.select_state_first') }}</option>
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

        {{-- Personalização Avançada --}}
        <x-ui.section-card title="Identidade Visual" description="Personalize as cores, fontes e estilos dos elementos do seu perfil público.">
            <div class="space-y-10">
                {{-- Cores --}}
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-3 block text-xs font-black uppercase tracking-widest text-zinc-500">{{ __('dashboard.profile.edit.style_section.primary_color') }}</label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.primary_color" class="h-12 w-12 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1 shadow-sm">
                            <span class="text-xs font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->primary_color }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-3 block text-xs font-black uppercase tracking-widest text-zinc-500">{{ __('dashboard.profile.edit.style_section.accent_color') }}</label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.accent_color" class="h-12 w-12 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1 shadow-sm">
                            <span class="text-xs font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->accent_color }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-3 block text-xs font-black uppercase tracking-widest text-zinc-500">Cor Secundária</label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.secondary_color" class="h-12 w-12 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1 shadow-sm">
                            <span class="text-xs font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->secondary_color ?? 'Nenhuma' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-3 block text-xs font-black uppercase tracking-widest text-zinc-500">Cor do Texto</label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.text_color" class="h-12 w-12 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1 shadow-sm">
                            <span class="text-xs font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->text_color ?? 'Padrão' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-3 block text-xs font-black uppercase tracking-widest text-zinc-500">Cor de Fundo (Página)</label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.background_color" class="h-12 w-12 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1 shadow-sm">
                            <span class="text-xs font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->background_color ?? 'Padrão' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <x-ui.select wire:model="form.theme_mode" label="{{ __('dashboard.profile.edit.style_section.theme_mode') }}">
                            @foreach(ThemePlatformEnum::options() as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                    <x-ui.select wire:model="form.layout_type" label="Layout do Perfil">
                        <option value="default">Layout Padrão (Sidebar)</option>
                        <option value="centered">Centralizado (Foco no Autor)</option>
                        <option value="grid">Grade (Foco em Posts)</option>
                    </x-ui.select>

                    <x-ui.select wire:model="form.button_style" label="Estilo dos Botões">
                        <option value="rounded-md">Arredondado Leve</option>
                        <option value="rounded-xl">Arredondado Médio</option>
                        <option value="rounded-full">Pílula (Full)</option>
                        <option value="square">Quadrado</option>
                    </x-ui.select>

                    <x-ui.select wire:model="form.card_style" label="Estilo dos Cards">
                        <option value="bordered">Com Bordas</option>
                        <option value="shadow">Com Sombra</option>
                        <option value="flat">Flat (Fundo Cinza)</option>
                    </x-ui.select>

                    <x-ui.select wire:model="form.font_family" label="Tipografia">
                        <option value="sans">Sans Serif (Padrão)</option>
                        <option value="serif">Serifado (Clássico)</option>
                        <option value="mono">Monospaced (Moderno)</option>
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                    <x-ui.checkbox
                        wire:model="form.show_subscriber_count"
                        label="Exibir seguidores"
                        description="Mostra o total de seguidores publicamente."
                    />
                    <x-ui.checkbox
                        wire:model="form.show_view_count"
                        label="Exibir visualizações"
                        description="Exibe o contador de visitas do perfil."
                    />
                </div>
            </div>
        </x-ui.section-card>

        {{-- Seção de Privacidade --}}
        <x-ui.section-card title="{{ __('dashboard.profile.edit.privacy_section.title') }}" description="{{ __('dashboard.profile.edit.privacy_section.description') }}">
            <div class="space-y-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div class="flex flex-col justify-between rounded-2xl border border-zinc-100 bg-zinc-50/50 p-5 transition-colors hover:border-zinc-200">
                        <x-ui.checkbox
                            wire:model="form.show_email_publicly"
                            label="{{ __('dashboard.profile.edit.privacy_section.show_email') }}"
                            description="{{ __('dashboard.profile.edit.privacy_section.show_email_desc', ['email' => $form->email]) }}"
                            :error="$errors->first('form.show_email_publicly')"
                        />
                    </div>

                    <div class="flex flex-col justify-center rounded-2xl border border-zinc-100 bg-zinc-50/50 p-5 transition-colors hover:border-zinc-200">
                        <x-ui.select
                            wire:model="form.visibility"
                            label="{{ __('dashboard.profile.edit.privacy_section.visibility') }}"
                            description="{{ __('dashboard.profile.edit.privacy_section.visibility_desc') }}"
                            :error="$errors->first('form.visibility')"
                            class="w-full"
                        >
                            @foreach(ProfileVisibilityEnum::options() as $visibility)
                                <option value="{{ $visibility['value'] }}">{{ $visibility['label'] }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>

                <div class="flex items-center gap-2 px-1 text-[10px] font-bold uppercase tracking-widest text-zinc-400">
                    <x-lucide-shield-check class="h-3.5 w-3.5 {{ $form->visibility === 'public' ? 'text-emerald-500' : 'text-amber-500' }}" />
                    {{ __('dashboard.profile.edit.privacy_section.current_status') }}
                    <span class="{{ $form->visibility === 'public' ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $form->visibility === 'public' ? __('dashboard.profile.edit.privacy_section.discovered') : __('dashboard.profile.edit.privacy_section.restricted') }}
                    </span>
                </div>
            </div>
        </x-ui.section-card>

        <x-ui.section-card
            title="{{ __('dashboard.profile.edit.seo_section.title') }}"
            description="{{ __('dashboard.profile.edit.seo_section.description') }}"
        >
            <div class="space-y-8">
                <div @class([
                    'rounded-2xl border p-5 transition-all duration-300',
                    $form->is_searchable
                        ? 'border-emerald-100 bg-emerald-50/30 dark:border-emerald-500/10 dark:bg-emerald-500/5'
                        : 'border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50'
                ])>
                    <x-ui.checkbox
                        wire:model.live="form.is_searchable"
                        label="{{ __('dashboard.profile.edit.seo_section.indexable') }}"
                        description="{{ __('dashboard.profile.edit.seo_section.indexable_desc') }}"
                        :error="$errors->first('form.is_searchable')"
                    />

                    <div class="mt-4 flex items-start gap-3 px-1 text-[10px] font-medium leading-relaxed text-zinc-500">
                        <x-lucide-info class="h-3.5 w-3.5 mt-0.5 shrink-0" />
                        @if($form->is_searchable)
                            <p>Seu perfil ficará visível em buscadores como Google e Bing. Isso pode levar alguns dias para ser processado pelos motores de busca.</p>
                        @else
                            <p class="text-amber-600 dark:text-amber-500/80">Adicionaremos uma tag 'noindex'. Buscadores serão instruídos a não exibir seu perfil nos resultados de pesquisa.</p>
                        @endif
                    </div>
                </div>

                @if($form->is_searchable)
                    @if(auth()->user()->getModuleSetting(ModuleEnum::PROFILE, 'enable_seo', false))
                        <div class="grid grid-cols-1 gap-6 pt-4 border-t border-zinc-100 dark:border-zinc-800 animate-in fade-in duration-500">
                            <x-ui.input
                                wire:model.blur="form.seo_title"
                                label="Título SEO Customizado"
                                description="Título que aparecerá no Google (Recomendado: 50-60 caracteres)"
                                placeholder="{{ $form->name }}"
                                maxlength="60"
                                :error="$errors->first('form.seo_title')"
                            />

                            <x-ui.textarea
                                wire:model.blur="form.seo_description"
                                label="Meta Descrição SEO"
                                description="Breve resumo para os resultados de busca (Recomendado: 120-160 caracteres)"
                                placeholder="{{ $form->bio }}"
                                maxlength="160"
                                rows="3"
                                :error="$errors->first('form.seo_description')"
                            />
                        </div>
                    @else
                        <div class="rounded-[2.5rem] bg-zinc-50 dark:bg-zinc-800/50 p-8 border border-zinc-100 dark:border-zinc-700/50 text-center space-y-4 animate-in fade-in duration-500">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-white mx-auto shadow-lg shadow-indigo-500/20">
                                <x-lucide-sparkles class="h-6 w-6" />
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-widest italic">Personalização SEO</h4>
                                <p class="text-xs text-zinc-500 mt-1 max-w-xs mx-auto">
                                    Desbloqueie o controle total de como você aparece no Google com os planos <b>Plus</b> ou <b>Pro</b>.
                                </p>
                            </div>
                            <x-ui.button href="{{ route('dashboard.billing.plans') }}" variant="outline" sizes="sm" class="!rounded-xl !px-8">
                                Ver Planos
                            </x-ui.button>
                        </div>
                    @endif
                @endif
            </div>
        </x-ui.section-card>

        <div class="flex justify-end gap-3 border-t border-zinc-100 dark:border-zinc-800 pt-8">
            <x-ui.button
                type="submit"
                loading="save"
                sizes="lg"
                class="w-full">
                {{ __('dashboard.profile.edit.submit_button') }}
            </x-ui.button>
        </div>
    </form>
</div>
