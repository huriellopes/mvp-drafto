@php
    // Sênior: Lógica simplificada e robusta para o preview
    $finalUrl = null;
    
    if ($currentCoverPath) {
        $finalUrl = str_starts_with($currentCoverPath, 'http') 
            ? $currentCoverPath 
            : asset('storage/' . $currentCoverPath);
    }
@endphp

<div class="space-y-4"
     x-data="{
        cropper: null,
        showModal: false,

        init() {
            this.$watch('showModal', value => {
                if (value) {
                    this.$nextTick(() => this.setupCropper());
                } else {
                    this.destroyCropper();
                }
            });
        },

        destroyCropper() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        },

        setupCropper() {
            const image = this.$refs.cropImage;
            if (!image) return;

            this.destroyCropper();

            this.cropper = new Cropper(image, {
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                responsive: true,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        },

        async confirm() {
            if (!this.cropper) return;

            const data = this.cropper.getData(true);
            await $wire.saveCrop(data);
            this.showModal = false;
        },

        zoom(value) {
            if (!this.cropper) return;
            // O cropper.zoomTo define um nível absoluto
            this.cropper.zoomTo(value);
        },

        cancel() {
            this.showModal = false;
            $wire.set('image', null);
            $wire.set('imageUrl', null);
        }
     }"
     x-on:image-uploaded.window="showModal = true"
>
    {{-- Dropzone / Preview --}}
    <div class="group relative w-full aspect-[21/9] overflow-hidden rounded-3xl border-2 border-dashed border-zinc-200 bg-zinc-50 transition-all hover:border-zinc-300">
        @if(!$finalUrl)
            <div class="relative h-full w-full flex flex-col items-center justify-center">
                <input type="file" wire:model="image" class="absolute inset-0 z-10 cursor-pointer opacity-0" accept="image/*">
                <div class="flex flex-col items-center justify-center">
                    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-zinc-200 mb-4 group-hover:scale-110 transition-transform">
                        <x-lucide-image class="h-8 w-8 text-indigo-500" />
                    </div>
                    <p class="text-xs text-zinc-600 font-bold px-4 text-center">Capa do Artigo</p>
                    <p class="text-[10px] text-zinc-400 font-medium mt-1">PNG, JPG até 5MB</p>
                </div>
            </div>
        @else
            <div class="relative h-full w-full">
                <div class="h-full w-full overflow-hidden bg-zinc-100 flex items-center justify-center">
                    <img src="{{ $finalUrl }}" class="h-full w-full object-cover">
                </div>

                {{-- Botão de Excluir (Sempre Visível) --}}
                <button type="button" 
                        x-on:click="$dispatch('open-modal', { name: 'confirm-cover-deletion' })"
                        class="absolute top-4 right-4 z-30 flex h-10 w-10 items-center justify-center rounded-xl bg-white/90 text-red-500 shadow-lg backdrop-blur-sm transition hover:bg-red-50 hover:text-red-600 active:scale-90"
                        title="Excluir Capa">
                    <x-lucide-trash-2 class="h-5 w-5" />
                </button>

                {{-- Overlay de Alterar --}}
                <div class="absolute inset-0 bg-zinc-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-3 backdrop-blur-[2px]">
                    <button type="button" onclick="this.parentElement.parentElement.querySelector('input').click()"
                            class="inline-flex h-9 items-center justify-center rounded-xl bg-white px-5 text-xs font-bold text-zinc-900 shadow-xl transition active:scale-95 hover:bg-zinc-50">
                        <x-lucide-refresh-cw class="mr-2 h-3.5 w-3.5" />
                        Alterar Capa
                    </button>
                </div>
                <input type="file" wire:model="image" class="hidden" accept="image/*">
            </div>
        @endif

        {{-- Loading State --}}
        <div wire:loading wire:target="image" class="absolute inset-0 z-40 bg-white/95 backdrop-blur-md flex flex-col items-center justify-center">
            <div class="flex flex-col items-center justify-center gap-3 animate-in fade-in zoom-in duration-300">
                <div class="relative flex items-center justify-center">
                    <div class="h-12 w-12 rounded-full border-2 border-zinc-100 border-t-indigo-600 animate-spin"></div>
                    <x-lucide-image class="absolute h-5 w-5 text-indigo-600" />
                </div>
                <div class="flex flex-col items-center gap-1">
                    <span class="text-[11px] font-black text-zinc-800 uppercase tracking-[0.2em] animate-pulse">Processando</span>
                    <span class="text-[9px] font-bold text-zinc-400 uppercase tracking-widest">Preparando editor...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Recorte (Teleportado para o body para evitar problemas de stacking context) --}}
    <template x-teleport="body">
        <div
            x-show="showModal"
            class="fixed inset-0 overflow-y-auto"
            x-data="{ zoomValue: 1 }"
            style="z-index: 9999;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="fixed inset-0 bg-zinc-950/95 backdrop-blur-2xl" style="z-index: 9999;"></div>

            <div class="flex min-h-screen items-center justify-center p-4 relative" style="z-index: 10000;">
                <div
                    class="relative w-full max-w-4xl rounded-[2.5rem] bg-white shadow-2xl overflow-hidden"
                    @click.away="showModal = false"
                >
                    <div class="flex items-center justify-between border-b border-zinc-100 px-8 py-6">
                        <div>
                            <h3 class="text-xl font-black text-zinc-900 tracking-tight italic">Enquadrar <span class="text-indigo-600">Imagem</span></h3>
                            <p class="text-xs text-zinc-500 font-medium mt-0.5">Mova e redimensione a área de destaque.</p>
                        </div>
                        <button type="button" @click="showModal = false" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-50 hover:text-zinc-900 transition">
                            <x-lucide-x class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="bg-zinc-900 p-4 sm:p-8 space-y-6">
                        <div class="max-h-[50vh] min-h-[300px] overflow-hidden rounded-2xl bg-zinc-950 shadow-inner flex items-center justify-center">
                            @if($imageUrl)
                                <img x-ref="cropImage" src="{{ $imageUrl }}" class="max-w-full block" @load="setupCropper()">
                            @endif
                        </div>

                        {{-- Zoom Slider --}}
                        <div class="flex items-center gap-4 px-4 py-2 bg-zinc-800/50 rounded-2xl border border-white/5">
                            <x-lucide-zoom-out class="h-4 w-4 text-zinc-500" />
                            <input 
                                type="range" 
                                x-model="zoomValue" 
                                min="0.1" 
                                max="3" 
                                step="0.01"
                                @input="zoom($event.target.value)"
                                class="flex-1 h-1.5 bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-500 focus:outline-none"
                            >
                            <x-lucide-zoom-in class="h-4 w-4 text-zinc-500" />
                            <span class="text-[10px] font-bold text-zinc-400 w-10 text-right" x-text="Math.round(zoomValue * 100) + '%'"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-8 py-6 bg-zinc-50/50">
                        <button type="button" @click="showModal = false"
                                class="h-12 px-6 text-sm font-bold text-zinc-500 hover:text-zinc-900 transition">
                            Descartar
                        </button>
                        <button type="button" @click="confirm()"
                                class="inline-flex h-12 items-center justify-center rounded-2xl bg-zinc-900 px-8 text-sm font-bold text-white shadow-lg shadow-zinc-900/20 transition hover:bg-indigo-600 active:scale-95">
                            <x-lucide-check class="mr-2 h-4 w-4 text-emerald-400" />
                            Aplicar Recorte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <x-ui.confirm-modal 
        name="confirm-cover-deletion"
        title="Excluir imagem de capa?"
        content="Tem certeza que deseja remover esta imagem? Esta ação irá deletar o arquivo permanentemente do servidor."
        buttonText="Sim, excluir agora"
        variant="danger"
        action="removeCover"
    />
</div>
