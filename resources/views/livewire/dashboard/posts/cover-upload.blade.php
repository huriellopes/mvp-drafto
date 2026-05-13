@php
    $displayUrl = $imageUrl ?? $this->post?->cover_image_url;
@endphp

<div class="space-y-4"
     x-data="{
        cropper: null,
        isCropped: @entangle('isCropped'),

        init() {
            // Se já abrir com imagem (edição), tenta iniciar
            if (this.$refs.image && !this.isCropped) {
                this.setupCropper();
            }
        },

        setupCropper() {
            if (this.cropper) this.cropper.destroy();

            this.cropper = new Cropper(this.$refs.image, {
                aspectRatio: 16 / 9,
                viewMode: 1,
                autoCropArea: 1,
                dragMode: 'move',
            });
        },

        confirm() {
            if (!this.cropper) return;
            $wire.saveCrop(this.cropper.getData(true));
        }
     }"
>
    <div class="relative overflow-hidden rounded-3xl border-2 border-dashed border-zinc-200 bg-zinc-50 transition-all">
        @if(!$displayUrl)
            <input type="file" wire:model="image" class="absolute inset-0 z-10 cursor-pointer opacity-0">
            <div class="flex flex-col items-center justify-center py-12">
                <x-lucide-image class="h-10 w-10 text-zinc-300 mb-2" />
                <p class="text-xs text-zinc-500 font-medium text-center px-4">Clique ou arraste uma imagem de capa</p>
            </div>
        @else
            <div class="max-h-[400px] overflow-hidden bg-black flex items-center justify-center" wire:ignore>
                <img x-ref="image" src="{{ $displayUrl }}"
                     class="max-w-full block"
                     @load="if(!isCropped) setupCropper()">
            </div>

            @if($isCropped || ($this->post?->cover_image_path && !$image))
                <div class="absolute inset-0 bg-zinc-900/40 backdrop-blur-[2px] flex items-center justify-center">
                    <div class="bg-white px-4 py-2 rounded-2xl shadow-xl flex items-center gap-2">
                        <x-lucide-check-circle class="h-4 w-4 text-green-500" />
                        <span class="text-xs font-bold text-zinc-900">Capa Definida</span>
                    </div>
                </div>
            @endif
        @endif
    </div>

    @if($imageUrl && !$isCropped)
        <div class="flex items-center justify-between gap-2 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
            <p class="text-[10px] font-black uppercase text-zinc-400">Ajuste o recorte</p>
            <button type="button" @click="confirm()"
                    class="inline-flex h-9 items-center justify-center rounded-xl bg-zinc-900 px-5 text-xs font-bold text-white shadow-lg transition active:scale-95">
                Confirmar Recorte
            </button>
        </div>
    @elseif($displayUrl)
        <button type="button" wire:click="$set('imageUrl', null); $set('isCropped', false); $set('image', null)"
                class="w-full text-center text-xs font-bold text-zinc-400 hover:text-red-500 transition duration-200">
            Remover e trocar imagem
        </button>
    @endif
</div>
