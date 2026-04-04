@use(App\Enums\ThemePlatformEnum)

<div class="space-y-10 pb-20">
    <form wire:submit="save" class="space-y-10">
        {{-- Seção Visual (Avatar/Cover) - Mantida conforme anterior --}}
        <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm">
            <div class="relative h-48 bg-zinc-100">
                @if ($form->cover)
                    <img src="{{ $form->cover->temporaryUrl() }}" class="h-full w-full object-cover" alt="Cover Preview" />
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
                    <div class="h-24 w-24 overflow-hidden rounded-3xl border-4 border-white bg-zinc-100 shadow-md">
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

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-ui.input wire:model.blur="form.username" label="Nome de usuário (@)" placeholder="exemplo" :error="$errors->first('form.username')" />
                    <x-ui.input wire:model.blur="form.website_url" label="Website" placeholder="https://exemplo.com" :error="$errors->first('form.website_url')" />
                </div>
            </div>
        </section>

        {{-- Seção de Informações (Bio/Localização) --}}
        <x-ui.section-card title="Sobre você" description="Conte aos outros quem você é e onde você está.">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700">Bio</label>
                    <textarea wire:model.blur="form.bio" rows="4" class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:ring-0 placeholder:text-zinc-400" placeholder="Escreva uma breve biografia..."></textarea>
                    @error('form.bio') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
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

        {{-- Seção de Estilo (Primary e Accent Color) --}}
        <x-ui.section-card title="Personalização" description="Ajuste as cores e o tema do seu perfil público.">
            <div class="space-y-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div>
                        <label class="mb-3 block text-sm font-medium text-zinc-700">Cor Principal</label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.primary_color" class="h-12 w-20 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1">
                            <span class="text-sm font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->primary_color }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-3 block text-sm font-medium text-zinc-700">Cor de Destaque (Accent)</label>
                        <div class="flex items-center gap-4">
                            <input type="color" wire:model.live="form.accent_color" class="h-12 w-20 cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50 p-1">
                            <span class="text-sm font-mono font-bold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg border border-zinc-200 uppercase">
                                {{ $form->accent_color }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-1/2">
                    <x-ui.select wire:model="form.theme_mode" label="Tema Preferencial">
                        @foreach(ThemePlatformEnum::options() as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
        </x-ui.section-card>

        {{-- Seção de Privacidade --}}
        <x-ui.section-card title="Privacidade" description="Controle quais informações ficam visíveis no seu perfil.">
            <div class="flex items-center gap-3">
                <input
                    type="checkbox"
                    id="show_email"
                    wire:model="form.show_email_publicly"
                    class="h-5 w-5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 transition"
                >
                <label for="show_email" class="text-sm font-medium text-zinc-700 cursor-pointer">
                    Exibir meu e-mail publicamente no perfil
                </label>
            </div>
            <p class="mt-2 text-xs text-zinc-500 ml-8">
                Se marcado, seu endereço de e-mail ({{ auth()->user()->email }}) será exibido para qualquer visitante.
            </p>
        </x-ui.section-card>

        <div class="flex justify-end gap-3 border-t border-zinc-100 pt-8">
            <x-ui.button loading="save" class="w-auto px-12">
                Atualizar Perfil
            </x-ui.button>
        </div>
    </form>
</div>
