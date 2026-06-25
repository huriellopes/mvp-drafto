@use(App\Enums\ThemePlatformEnum)
@use(App\Enums\ProfileVisibilityEnum)
@use(App\Enums\ModuleEnum)

<div class="space-y-10 pb-20">
    {{ Breadcrumbs::render('dashboard.profile') }}

    <form wire:submit="save" class="space-y-10">
        {{-- Visual Section --}}
        <section class="overflow-hidden rounded-[2.5rem] border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            {{-- Interactive Cover --}}
            <div
                class="group relative h-48 bg-zinc-100 transition-all dark:bg-zinc-800"
                x-data="{ isDropping: false }"
                @dragover.prevent="isDropping = true"
                @dragleave.prevent="isDropping = false"
                @drop.prevent="isDropping = false"
            >
                {{-- Show permanent image from DB with cache buster --}}
                @if ($this->profile?->cover_path)
                    <img src="/storage/{{ $this->profile->cover_path }}?v={{ now()->timestamp }}" class="h-full w-full object-cover">
                @endif

                <label
                    @class([
                        'absolute inset-0 flex cursor-pointer flex-col items-center justify-center transition-all duration-300',
                        'bg-black/40 opacity-0 group-hover:opacity-100 backdrop-blur-[2px]' => !$this->profile?->cover_path && !$form->cover,
                        'bg-black/50 opacity-0 group-hover:opacity-100 backdrop-blur-sm' => $this->profile?->cover_path || $form->cover,
                    ])
                    :class="{ 'bg-indigo-600/40 opacity-100 backdrop-blur-md': isDropping }"
                >
                    <input type="file" wire:model="form.cover" class="sr-only">
                    <div class="flex flex-col items-center gap-2 text-white drop-shadow-lg" wire:loading.remove wire:target="form.cover">
                        <x-lucide-image class="h-8 w-8 transition-transform group-hover:scale-110" />
                        <span class="text-[10px] font-black uppercase tracking-widest">{{ __('dashboard.profile.edit.visual_section.change_cover') }}</span>
                        <span class="text-[8px] font-bold opacity-70 italic">{{ __('dashboard.profile.edit.visual_section.change_cover_help') }}</span>
                    </div>

                    <div wire:loading wire:target="form.cover" class="flex flex-col items-center gap-2 text-white">
                        <x-lucide-loader-2 class="h-6 w-6 animate-spin" />
                        <span class="text-[10px] font-black uppercase tracking-widest">{{ __('dashboard.profile.edit.visual_section.processing') }}</span>
                    </div>
                </label>

                {{-- Delete Cover Button --}}
                @if($this->profile?->cover_path)
                    <button 
                        type="button" 
                        x-on:click="$dispatch('open-modal', { name: 'confirm-cover-deletion' })"
                        class="absolute top-4 right-4 z-20 p-2.5 rounded-xl bg-white/90 dark:bg-zinc-900/90 text-red-500 hover:text-red-600 hover:bg-white transition shadow-sm border border-zinc-200/50 dark:border-zinc-700/50 backdrop-blur-sm"
                        title="{{ __('dashboard.profile.edit.delete_cover.confirm') }}"
                    >
                        <x-lucide-trash-2 class="h-4 w-4" />
                    </button>
                @endif
            </div>

            <div class="relative px-8 pb-8">
                {{-- Interactive Avatar --}}
                <div
                    class="group relative -mt-12 mb-6 inline-block"
                    x-data="{ isDropping: false }"
                    @dragover.prevent="isDropping = true"
                    @dragleave.prevent="isDropping = false"
                    @drop.prevent="isDropping = false"
                >
                    <div @class([
                        'h-24 w-24 overflow-hidden rounded-3xl border-4 transition-all duration-300 bg-zinc-100 dark:bg-zinc-800 shadow-md',
                        'border-white dark:border-zinc-900 group-hover:scale-105 group-hover:shadow-xl'
                    ])>
                        {{-- Show permanent image from DB with cache buster --}}
                        @if($this->profile?->avatar_path)
                            <img
                                src="/storage/{{ $this->profile->avatar_path }}?v={{ now()->timestamp }}"
                                class="h-full w-full object-cover"
                                alt="{{ $this->profile->name }}"
                            />
                        @else
                            <div class="flex h-full items-center justify-center text-2xl font-black uppercase tracking-widest text-zinc-400">
                                {{ get_initials($form->name) }}
                            </div>
                        @endif
                    </div>

                    <label
                        @class([
                            'absolute inset-0 flex cursor-pointer flex-col items-center justify-center rounded-3xl transition-all duration-300',
                            'bg-black/40 opacity-0 group-hover:opacity-100 backdrop-blur-[2px]' => !$this->profile?->avatar_path && !$form->avatar,
                            'bg-black/50 opacity-0 group-hover:opacity-100 backdrop-blur-sm' => $this->profile?->avatar_path || $form->avatar,
                        ])
                        :class="{ 'bg-indigo-600/40 opacity-100 backdrop-blur-md': isDropping }"
                    >
                        <input type="file" wire:model="form.avatar" class="sr-only">
                        <div class="flex flex-col items-center gap-1 text-white" wire:loading.remove wire:target="form.avatar">
                            <x-lucide-camera class="h-5 w-5 transition-transform group-hover:scale-110" />
                            <span class="text-[8px] font-black uppercase tracking-tighter">{{ __('dashboard.profile.edit.visual_section.change_avatar') }}</span>
                        </div>
                        <div wire:loading wire:target="form.avatar">
                            <x-lucide-loader-2 class="h-5 w-5 animate-spin text-white" />
                        </div>
                    </label>

                    {{-- Delete Avatar Button --}}
                    @if($this->profile?->avatar_path)
                        <button 
                            type="button" 
                            x-on:click="$dispatch('open-modal', { name: 'confirm-avatar-deletion' })"
                            class="absolute -top-2 -left-2 z-20 flex h-7 w-7 items-center justify-center rounded-full bg-white dark:bg-zinc-900 text-red-500 shadow-md border border-zinc-100 dark:border-zinc-800 hover:text-red-600 transition group-hover:scale-110"
                            title="{{ __('dashboard.profile.edit.delete_avatar.confirm') }}"
                        >
                            <x-lucide-trash-2 class="h-3.5 w-3.5" />
                        </button>
                    @endif

                    {{-- Edit Button (Pencil) - Fully Clickable for triggers --}}
                    <label 
                        for="avatar-upload-pencil"
                        class="absolute -bottom-1 -right-1 flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-zinc-900 text-white shadow-lg dark:bg-white dark:text-zinc-900 transition-all hover:scale-110 active:scale-95 z-30"
                        title="{{ __('dashboard.profile.edit.visual_section.change_avatar') }}"
                    >
                        <x-lucide-pencil class="h-3.5 w-3.5" />
                        <input id="avatar-upload-pencil" type="file" wire:model="form.avatar" class="sr-only">
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 mb-2">
                    <x-ui.input
                        wire:model.blur="form.name"
                        label="{{ __('dashboard.profile.edit.visual_section.name_label') }}"
                        placeholder="John Doe"
                        :error="$errors->first('form.name')"
                    >
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                        </x-slot:label_extra>
                    </x-ui.input>

                    <x-ui.input
                        wire:model.live="form.email"
                        label="{{ __('dashboard.profile.edit.visual_section.email_label') }}"
                        placeholder="johndoe@example.com"
                        :error="$errors->first('form.email')"
                    >
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                        </x-slot:label_extra>
                    </x-ui.input>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-ui.input
                        wire:model.blur="form.username"
                        label="{{ __('dashboard.profile.edit.visual_section.username_label') }}"
                        prefix="@"
                        placeholder="exemplo"
                        :error="$errors->first('form.username')"
                    >
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                        </x-slot:label_extra>
                    </x-ui.input>

                    <x-ui.input
                        wire:model.blur="form.website_url"
                        label="{{ __('dashboard.profile.edit.visual_section.website_label') }}"
                        placeholder="https://exemplo.com"
                        :error="$errors->first('form.website_url')"
                    >
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                        </x-slot:label_extra>
                    </x-ui.input>
                </div>
            </div>
        </section>

        {{-- About Section --}}
        <x-ui.section-card title="{{ __('dashboard.profile.edit.about_section.title') }}" description="{{ __('dashboard.profile.edit.about_section.description') }}">
            <div class="space-y-6">
                <div>
                    <x-ui.textarea
                        label="{{ __('dashboard.profile.edit.about_section.bio_label') }}"
                        wire:model.blur="form.bio"
                        :error="$errors->first('form.bio')"
                    >
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                        </x-slot:label_extra>
                    </x-ui.textarea>
                </div>
            </div>
        </x-ui.section-card>

        {{-- Location Section --}}
        <x-ui.section-card title="{{ __('dashboard.profile.edit.about_section.location_title') ?? 'Sua Localização' }}" description="{{ __('dashboard.profile.edit.about_section.location_desc') ?? 'Informe de onde você escreve (Opcional).' }}">
            <x-slot:title_extra>
                <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-2" />
            </x-slot:title_extra>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-ui.select wire:model.live="selectedUf" label="{{ __('dashboard.profile.edit.about_section.state_label') }}">
                    <x-slot:label_extra>
                        <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                    </x-slot:label_extra>
                    <option value="">{{ __('dashboard.profile.edit.about_section.select_state') }}</option>
                    @foreach($ufs as $uf)
                        <option value="{{ $uf['sigla'] }}">{{ $uf['nome'] }}</option>
                    @endforeach
                </x-ui.select>

                <div class="relative">
                    <x-ui.select wire:model="form.location" label="{{ __('dashboard.profile.edit.about_section.city_label') }}" wire:loading.attr="disabled" wire:target="selectedUf" :error="$errors->first('form.location')">
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                        </x-slot:label_extra>
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
        </x-ui.section-card>

        {{-- Links & Redes Sociais Section --}}
        <x-ui.section-card :title="__('dashboard.profile.edit.links_section.title')" :description="__('dashboard.profile.edit.links_section.description')">
            <x-slot:title_extra>
                <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-2" />
            </x-slot:title_extra>

            <div class="space-y-4" x-data="{
                from: null,
                drop(event) {
                    if (this.from === null) return;
                    const target = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-link-index]');
                    const to = target ? Number(target.dataset.linkIndex) : null;
                    const from = this.from;
                    this.from = null;
                    if (to !== null && to !== from) {
                        $wire.reorderLinks(from, to);
                    }
                }
            }">
                @forelse($form->links as $index => $link)
                    <div
                        wire:key="profile-link-{{ $index }}"
                        data-link-index="{{ $index }}"
                        :class="from === {{ $index }} ? 'opacity-50 ring-2 ring-indigo-400' : ''"
                        class="flex flex-col gap-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-4 sm:flex-row sm:items-center"
                    >
                        {{-- Alça de arrastar para reordenar (mouse e touch via pointer events) --}}
                        <button
                            type="button"
                            x-on:pointerdown.prevent="from = {{ $index }}; $event.target.setPointerCapture($event.pointerId)"
                            x-on:pointerup="drop($event)"
                            style="touch-action: none;"
                            class="flex h-10 w-8 shrink-0 cursor-grab items-center justify-center text-zinc-300 hover:text-zinc-500 active:cursor-grabbing"
                            title="{{ __('dashboard.profile.edit.links_section.reorder') }}"
                        >
                            <x-lucide-grip-vertical class="h-5 w-5" />
                        </button>

                        <div class="sm:w-44">
                            <x-ui.select wire:model="form.links.{{ $index }}.platform" :error="$errors->first('form.links.'.$index.'.platform')">
                                @foreach($this->socialPlatforms as $platform)
                                    <option value="{{ $platform->value }}">{{ $platform->label }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <div class="flex-1">
                            <x-ui.input
                                type="text"
                                wire:model="form.links.{{ $index }}.url"
                                :placeholder="__('dashboard.profile.edit.links_section.url_placeholder')"
                                :error="$errors->first('form.links.'.$index.'.url')"
                            />
                        </div>

                        <div class="sm:w-40">
                            <x-ui.select wire:model="form.links.{{ $index }}.visibility" :error="$errors->first('form.links.'.$index.'.visibility')">
                                @foreach(\App\Enums\LinkVisibilityEnum::cases() as $vis)
                                    <option value="{{ $vis->value }}">{{ $vis->label() }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <button
                            type="button"
                            wire:click="removeLink({{ $index }})"
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-zinc-200 dark:border-zinc-800 text-zinc-400 transition hover:border-red-300 hover:text-red-500 active:scale-95"
                            title="{{ __('dashboard.profile.edit.links_section.remove') }}"
                        >
                            <x-lucide-trash-2 class="h-4 w-4" />
                        </button>
                    </div>
                @empty
                    <p class="text-sm font-medium text-zinc-400">{{ __('dashboard.profile.edit.links_section.empty') }}</p>
                @endforelse

                @if(count($form->links) < 8)
                    <button
                        type="button"
                        wire:click="addLink"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-800 py-4 text-xs font-black uppercase tracking-widest text-zinc-500 transition hover:border-indigo-400 hover:text-indigo-500 active:scale-[0.99]"
                    >
                        <x-lucide-plus class="h-4 w-4" /> {{ __('dashboard.profile.edit.links_section.add') }}
                    </button>
                @else
                    <p class="text-center text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('dashboard.profile.edit.links_section.limit_reached') }}</p>
                @endif
            </div>
        </x-ui.section-card>

        {{-- Visual Identity Section --}}
        <x-ui.section-card title="{{ __('dashboard.profile.edit.style_section.title') }}" description="{{ __('dashboard.profile.edit.style_section.description') }}">
            <div class="space-y-10">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-3 block text-xs font-black uppercase tracking-widest text-zinc-500">
                            {{ __('dashboard.profile.edit.style_section.primary_color') }}
                            <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.primary_color" class="h-12 w-12 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1 shadow-sm">
                            <span class="text-xs font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->primary_color }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-3 block text-xs font-black uppercase tracking-widest text-zinc-500">
                            {{ __('dashboard.profile.edit.style_section.accent_color') }}
                            <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.accent_color" class="h-12 w-12 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1 shadow-sm">
                            <span class="text-xs font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->accent_color }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-3 block text-xs font-black uppercase tracking-widest text-zinc-500">
                            {{ __('dashboard.profile.edit.style_section.secondary_color') }}
                            <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="color" value="{{ $form->secondary_color ?? '#000000' }}" x-on:change="$wire.set('form.secondary_color', $event.target.value)" class="h-12 w-12 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1 shadow-sm">
                            <span class="text-xs font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->secondary_color ?? __('dashboard.profile.edit.style_section.none') }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-3 block text-xs font-black uppercase tracking-widest text-zinc-500">
                            {{ __('dashboard.profile.edit.style_section.text_color') }}
                            <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="color" value="{{ $form->text_color ?? '#000000' }}" x-on:change="$wire.set('form.text_color', $event.target.value)" class="h-12 w-12 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1 shadow-sm">
                            <span class="text-xs font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->text_color ?? __('dashboard.profile.edit.style_section.default') }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-3 block text-xs font-black uppercase tracking-widest text-zinc-500">
                            {{ __('dashboard.profile.edit.style_section.background_color') }}
                            <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="color" value="{{ $form->background_color ?? '#000000' }}" x-on:change="$wire.set('form.background_color', $event.target.value)" class="h-12 w-12 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1 shadow-sm">
                            <span class="text-xs font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->background_color ?? __('dashboard.profile.edit.style_section.default') }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <x-ui.select wire:model="form.theme_mode" label="{{ __('dashboard.profile.edit.style_section.theme_mode') }}">
                            <x-slot:label_extra>
                                <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                            </x-slot:label_extra>
                            @foreach(ThemePlatformEnum::options() as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                    <x-ui.select wire:model="form.layout_type" label="{{ __('dashboard.profile.edit.style_section.layout_type') }}">
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                        </x-slot:label_extra>
                        <option value="default">Layout Padrão (Sidebar)</option>
                        <option value="centered">Centralizado (Foco no Autor)</option>
                        <option value="grid">Grade (Foco em Posts)</option>
                    </x-ui.select>

                    <x-ui.select wire:model="form.button_style" label="{{ __('dashboard.profile.edit.style_section.button_style') }}">
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                        </x-slot:label_extra>
                        <option value="rounded-md">Arredondado Leve</option>
                        <option value="rounded-xl">Arredondado Médio</option>
                        <option value="rounded-full">Pílula (Full)</option>
                        <option value="square">Quadrado</option>
                    </x-ui.select>

                    <x-ui.select wire:model="form.card_style" label="{{ __('dashboard.profile.edit.style_section.card_style') }}">
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                        </x-slot:label_extra>
                        <option value="bordered">Com Bordas</option>
                        <option value="shadow">Com Sombra</option>
                        <option value="flat">Flat (Fundo Cinza)</option>
                    </x-ui.select>

                    <x-ui.select wire:model="form.font_family" label="{{ __('dashboard.profile.edit.style_section.font_family') }}">
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                        </x-slot:label_extra>
                        <option value="sans">Sans Serif (Padrão)</option>
                        <option value="serif">Serifado (Clássico)</option>
                        <option value="mono">Monospaced (Moderno)</option>
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                    <x-ui.checkbox
                        wire:model="form.show_subscriber_count"
                        label="{{ __('dashboard.profile.edit.style_section.show_followers') }}"
                        description="{{ __('dashboard.profile.edit.style_section.show_followers_desc') }}"
                    >
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                        </x-slot:label_extra>
                    </x-ui.checkbox>
                    <x-ui.checkbox
                        wire:model="form.show_view_count"
                        label="{{ __('dashboard.profile.edit.style_section.show_views') }}"
                        description="{{ __('dashboard.profile.edit.style_section.show_views_desc') }}"
                    >
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                        </x-slot:label_extra>
                    </x-ui.checkbox>
                </div>
            </div>
        </x-ui.section-card>

        {{-- Privacy Section --}}
        <x-ui.section-card title="{{ __('dashboard.profile.edit.privacy_section.title') }}" description="{{ __('dashboard.profile.edit.privacy_section.description') }}">
            <div class="space-y-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div class="flex flex-col justify-between rounded-2xl border border-zinc-100 bg-zinc-50/50 p-5 transition-colors hover:border-zinc-200">
                        <x-ui.checkbox
                            wire:model="form.show_email_publicly"
                            label="{{ __('dashboard.profile.edit.privacy_section.show_email') }}"
                            description="{{ __('dashboard.profile.edit.privacy_section.show_email_desc', ['email' => $form->email]) }}"
                            :error="$errors->first('form.show_email_publicly')"
                        >
                            <x-slot:label_extra>
                                <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                            </x-slot:label_extra>
                        </x-ui.checkbox>
                    </div>

                    <div class="flex flex-col justify-center rounded-2xl border border-zinc-100 bg-zinc-50/50 p-5 transition-colors hover:border-zinc-200">
                        <x-ui.select
                            wire:model="form.visibility"
                            label="{{ __('dashboard.profile.edit.privacy_section.visibility') }}"
                            description="{{ __('dashboard.profile.edit.privacy_section.visibility_desc') }}"
                            :error="$errors->first('form.visibility')"
                            class="w-full"
                        >
                            <x-slot:label_extra>
                                <x-ui.badge label="{{ __('validation.required_field') ?? 'Obrigatório' }}" color="red" class="ml-1" />
                            </x-slot:label_extra>
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

        {{-- SEO Section --}}
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
                    >
                        <x-slot:label_extra>
                            <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                        </x-slot:label_extra>
                    </x-ui.checkbox>

                    <div class="mt-4 flex items-start gap-3 px-1 text-[10px] font-medium leading-relaxed text-zinc-500">
                        <x-lucide-info class="h-3.5 w-3.5 mt-0.5 shrink-0" />
                        @if($form->is_searchable)
                            <p>{{ __('dashboard.profile.edit.seo_section.google_visible') }}</p>
                        @else
                            <p class="text-amber-600 dark:text-amber-500/80">{{ __('dashboard.profile.edit.seo_section.google_hidden') }}</p>
                        @endif
                    </div>
                </div>

                @if($form->is_searchable)
                    @if(auth()->user()->getModuleSetting(ModuleEnum::PROFILE, 'enable_seo', true))
                        <div class="grid grid-cols-1 gap-6 pt-4 border-t border-zinc-100 dark:border-zinc-800 animate-in fade-in duration-500">
                            <x-ui.input
                                wire:model.blur="form.seo_title"
                                label="{{ __('dashboard.profile.edit.seo_section.custom_title') }}"
                                description="{{ __('dashboard.profile.edit.seo_section.custom_title_desc') }}"
                                placeholder="{{ $form->name }}"
                                maxlength="60"
                                :error="$errors->first('form.seo_title')"
                            >
                                <x-slot:label_extra>
                                    <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                                </x-slot:label_extra>
                            </x-ui.input>

                            <x-ui.textarea
                                wire:model.blur="form.seo_description"
                                label="{{ __('dashboard.profile.edit.seo_section.meta_description') }}"
                                description="{{ __('dashboard.profile.edit.seo_section.meta_description_desc') }}"
                                placeholder="{{ $form->bio }}"
                                maxlength="160"
                                rows="3"
                                :error="$errors->first('form.seo_description')"
                            >
                                <x-slot:label_extra>
                                    <x-ui.badge label="{{ __('validation.optional_field') ?? 'Opcional' }}" color="zinc" class="ml-1" />
                                </x-slot:label_extra>
                            </x-ui.textarea>
                        </div>
                    @endif
                @endif
            </div>
        </x-ui.section-card>

        {{-- Barra fixa de salvar: cola no fim da tela ao rolar, sem precisar descer a página toda. --}}
        <x-ui.sticky-bar>
            <x-ui.button
                type="submit"
                loading="save"
                sizes="lg"
                class="w-full justify-center">
                {{ __('dashboard.profile.edit.submit_button') }}
            </x-ui.button>
        </x-ui.sticky-bar>
    </form>

    {{-- Cover Cropper Modal --}}
    <x-ui.modal name="cover-cropper-modal" title="{{ __('dashboard.profile.edit.cropper.title') }}" :maxWidth="'4xl'">
        <div class="space-y-6"
             x-data="{
                cropper: null,
                setup() {
                    if (this.cropper) this.cropper.destroy();
                    const img = document.getElementById('cover-preview-image');
                    if (!img) return;

                    this.cropper = new Cropper(img, {
                        aspectRatio: 3 / 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        background: false,
                        zoomable: true,
                        movable: true,
                    });
                },
                confirm() {
                    if (!this.cropper) return;
                    const data = this.cropper.getData(true);
                    $wire.saveCrop(data);
                }
             }"
             x-on:open-modal.window="if ($event.detail.name === 'cover-cropper-modal') setTimeout(() => setup(), 100)"
        >
            <div class="relative overflow-hidden rounded-2xl bg-black min-h-[300px] flex items-center justify-center">
                @if ($form->cover && method_exists($form->cover, 'temporaryUrl'))
                    <img id="cover-preview-image" src="{{ $form->cover->temporaryUrl() }}" class="max-w-full block">
                @endif
            </div>

            <div class="flex items-center justify-between gap-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-700">
                <div class="flex items-center gap-2">
                    <button type="button" @click="cropper.zoom(0.1)" class="p-2 rounded-lg bg-white dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 transition shadow-sm border border-zinc-200/50">
                        <x-lucide-zoom-in class="h-4 w-4" />
                    </button>
                    <button type="button" @click="cropper.zoom(-0.1)" class="p-2 rounded-lg bg-white dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 transition shadow-sm border border-zinc-200/50">
                        <x-lucide-zoom-out class="h-4 w-4" />
                    </button>
                    <button type="button" @click="cropper.reset()" class="p-2 rounded-lg bg-white dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 transition shadow-sm border border-zinc-200/50">
                        <x-lucide-rotate-ccw class="h-4 w-4" />
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', { name: 'cover-cropper-modal' })" type="button" class="!w-auto px-6">
                        {{ __('dashboard.profile.edit.cropper.cancel') }}
                    </x-ui.button>
                    <x-ui.button @click="confirm()" type="button" class="!w-auto px-10">
                        {{ __('dashboard.profile.edit.cropper.apply') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </x-ui.modal>

    {{-- Deletion Confirmation Modals --}}
    <x-ui.confirm-modal 
        name="confirm-avatar-deletion" 
        title="{{ __('dashboard.profile.edit.delete_avatar.title') }}"
        content="{{ __('dashboard.profile.edit.delete_avatar.content') }}"
        buttonText="{{ __('dashboard.profile.edit.delete_avatar.confirm') }}"
        variant="danger"
        action="removeAvatar"
    />

    <x-ui.confirm-modal 
        name="confirm-cover-deletion" 
        title="{{ __('dashboard.profile.edit.delete_cover.title') }}"
        content="{{ __('dashboard.profile.edit.delete_cover.content') }}"
        buttonText="{{ __('dashboard.profile.edit.delete_cover.confirm') }}"
        variant="danger"
        action="removeCover"
    />
</div>
