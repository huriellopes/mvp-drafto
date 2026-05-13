@props([
    'value' => '',
    'placeholder' => 'Escreva algo incrível...',
    'uploadUrl' => null,
])

@php
    $uniqueId = 'trix-' . Illuminate\Support\Str::random(10);
@endphp

<div
    wire:ignore
    x-data="{
        value: @entangle($attributes->wire('model')),
        isFocused: false,

        init() {
            const editorElement = this.$refs.editor;

            // Carrega valor inicial. 
            // Usamos setTimeout para garantir que o editor Trix esteja totalmente pronto
            setTimeout(() => {
                if (this.value && editorElement.editor) {
                    editorElement.editor.loadHTML(this.value);
                }
            }, 10);

            // Monitora mudanças externas. 
            // Só atualizamos se o valor for diferente E o usuário não estiver editando (foco)
            this.$watch('value', v => {
                if (editorElement.editor && v !== editorElement.value && !this.isFocused) {
                    editorElement.editor.loadHTML(v || '');
                }
            });

            // Sincroniza do Trix para o Livewire
            editorElement.addEventListener('trix-change', () => {
                this.value = editorElement.value;
            });

            editorElement.addEventListener('trix-focus', () => { this.isFocused = true; });
            editorElement.addEventListener('trix-blur', () => { this.isFocused = false; });

            // Upload de anexos
            editorElement.addEventListener('trix-attachment-add', async (event) => {
                const attachment = event.attachment;
                
                // Se o anexo já tiver URL (veio do loadHTML), não fazemos nada
                if (attachment.getAttributes().url) {
                    return;
                }

                if (!attachment?.file || !@js($uploadUrl)) return;

                const formData = new FormData();
                formData.append('file', attachment.file);

                try {
                    const response = await fetch(@js($uploadUrl), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    if (!response.ok) throw new Error('Upload failed');
                    const data = await response.json();

                    attachment.setAttributes({
                        url: data.url,
                        href: data.url,
                        contentType: attachment.file.type
                    });
                } catch (error) {
                    attachment.remove();
                    console.error('Trix Upload Error:', error);
                }
            });
        }
    }"
    class="trix-shell w-full"
>
    <input id="{{ $uniqueId }}" type="hidden" name="content">
    <trix-editor
        x-ref="editor"
        input="{{ $uniqueId }}"
        placeholder="{{ $placeholder }}"
        class="trix-content block w-full"
        {{ $attributes->except(['wire:model', 'upload-url', 'uploadUrl']) }}
    ></trix-editor>
</div>
