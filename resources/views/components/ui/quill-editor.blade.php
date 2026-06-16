@props([
    'placeholder' => 'Conte sua história...',
    'uploadUrl' => route('editor.attachments.store'),
    'initial' => '',
    'maxUploadKb' => config('editor.max_upload_kb'),
])

<div
    wire:ignore
    x-data="quillEditor({
        model: '{{ $attributes->wire('model')->value() }}',
        uploadUrl: '{{ $uploadUrl }}',
        placeholder: '{{ addslashes($placeholder) }}',
        maxUploadKb: {{ (int) $maxUploadKb }},
    })"
    class="relative group"
>
    {{-- Loading Overlay --}}
    <div x-show="uploading" x-cloak class="absolute inset-0 z-50 flex items-center justify-center bg-white/50 backdrop-blur-[1px] rounded-xl">
        <div class="flex items-center gap-3 px-6 py-3 bg-zinc-900 text-white rounded-2xl shadow-2xl animate-in zoom-in duration-200">
            <svg class="animate-spin h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-black uppercase tracking-widest" x-text="uploadLabel"></span>
        </div>
    </div>

    {{-- O conteúdo inicial é renderizado AQUI dentro para o Quill adotá-lo na
         construção. Evita setContents pós-init, que corrompia a seleção e
         travava a digitação na edição. --}}
    <div x-ref="editor" class="min-h-[40rem] bg-white border border-zinc-200 rounded-b-xl">{!! $initial !!}</div>

    {{-- Modal de vídeo por link (YouTube/Vimeo) ou upload do computador --}}
    <x-ui.modal name="quill-video-link" title="Inserir vídeo">
        <div class="space-y-5">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-zinc-500">
                    Link do YouTube ou Vimeo
                </label>
                <textarea
                    x-model="videoUrl"
                    @keydown.enter.prevent="confirmVideoLink()"
                    x-on:open-modal.window="if ($event.detail.name === 'quill-video-link') setTimeout(() => $el.focus(), 120)"
                    rows="3"
                    placeholder="Cole o link (ex.: https://www.youtube.com/watch?v=...) ou o código <iframe>"
                    class="w-full resize-none rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-900 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
                ></textarea>
                <p x-show="videoError" x-cloak x-text="videoError" class="mt-2 text-sm text-red-600"></p>
                <p class="mt-2 text-[11px] text-zinc-400 leading-relaxed">
                    Aceita links do YouTube/Vimeo ou o código de incorporação (<code>&lt;iframe&gt;</code>).
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row-reverse">
                <x-ui.button type="button" @click="confirmVideoLink()" class="!w-auto px-6">
                    Inserir vídeo
                </x-ui.button>
                <x-ui.button type="button" @click="uploadVideoFromModal()" class="!bg-zinc-100 !text-zinc-700 hover:!bg-zinc-200 !w-auto px-6">
                    <x-lucide-upload class="mr-2 h-4 w-4" />
                    Enviar do computador
                </x-ui.button>
                <x-ui.button type="button" @click="$dispatch('close-modal', { name: 'quill-video-link' })" class="!bg-white !text-zinc-500 hover:!text-zinc-900 !w-auto px-6">
                    Cancelar
                </x-ui.button>
            </div>
        </div>
    </x-ui.modal>

    {{-- Modal de link --}}
    <x-ui.modal name="quill-link" title="Inserir link">
        <div class="space-y-5">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-zinc-500">URL</label>
                <input
                    type="url"
                    x-model="linkUrl"
                    @keydown.enter.prevent="confirmLink()"
                    x-on:open-modal.window="if ($event.detail.name === 'quill-link') setTimeout(() => $el.focus(), 120)"
                    placeholder="https://exemplo.com"
                    class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-900 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
                />
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-zinc-500">Texto (opcional)</label>
                <input
                    type="text"
                    x-model="linkText"
                    @keydown.enter.prevent="confirmLink()"
                    placeholder="Texto a exibir (usado quando nada está selecionado)"
                    class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-900 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 focus:outline-none"
                />
            </div>
            <p x-show="linkError" x-cloak x-text="linkError" class="text-sm text-red-600"></p>
            <div class="flex flex-col gap-3 sm:flex-row-reverse">
                <x-ui.button type="button" @click="confirmLink()" class="!w-auto px-6">Inserir link</x-ui.button>
                <x-ui.button type="button" @click="$dispatch('close-modal', { name: 'quill-link' })" class="!bg-white !text-zinc-500 hover:!text-zinc-900 !w-auto px-6">Cancelar</x-ui.button>
            </div>
        </div>
    </x-ui.modal>

    {{-- Modal de erro (substitui o alert nativo) --}}
    <x-ui.modal name="quill-error" title="Não foi possível enviar">
        <div class="space-y-6">
            <div class="flex gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 ring-1 ring-red-100">
                    <x-lucide-alert-triangle class="h-5 w-5 text-red-500" />
                </div>
                <p class="text-sm leading-relaxed text-zinc-600" x-text="errorMessage"></p>
            </div>
            <div class="flex justify-end">
                <x-ui.button type="button" @click="$dispatch('close-modal', { name: 'quill-error' })" class="!w-auto px-6">
                    Entendi
                </x-ui.button>
            </div>
        </div>
    </x-ui.modal>
</div>
