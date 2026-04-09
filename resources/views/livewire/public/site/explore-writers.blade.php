<div class="max-w-7xl mx-auto px-4 py-12 lg:py-20 space-y-16">
    {{-- Header --}}
    <div class="text-center space-y-6 max-w-2xl mx-auto">
        <h1 class="text-5xl md:text-7xl font-black text-zinc-900 dark:text-white tracking-tighter italic">
            Descubra novos <span class="text-profile-primary not-italic">Escritores.</span>
        </h1>
        <p class="text-zinc-500 font-medium">Conecte-se com as mentes que estão moldando o futuro da escrita.</p>

        <div class="pt-4">
            <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Buscar por nome ou @username...">
                <x-slot:prefix><x-lucide-search class="h-5 w-5 text-zinc-400" /></x-slot:prefix>
            </x-ui.input>
        </div>
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8" wire:loading.class="opacity-50 blur-[1px] transition-all duration-500">
        @forelse($this->writers as $writer)
            <x-public.writer-card :$writer wire:key="writer-{{ $writer->id }}">
                <x-slot:actions>
                    <div class="grid grid-cols-5 gap-2">
                        <div class="col-span-4">
                            <x-ui.button
                                href="{{ route('profile.show', $writer->profile->username) }}"
                                size="lg"
                                class="w-full !rounded-xl text-[11px]"
                            >
                                Ver Perfil
                            </x-ui.button>
                        </div>
                        <div class="col-span-1">
                            <livewire:actions.follow-button :user="$writer" :iconOnly="true" :key="'explore-'.$writer->id" />
                        </div>
                    </div>
                </x-slot:actions>
            </x-public.writer-card>
        @empty
            <div class="col-span-full py-24 text-center">
                <div class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-zinc-50 dark:bg-zinc-900 mb-4">
                    <x-lucide-user-x class="h-10 w-10 text-zinc-300" />
                </div>
                <p class="text-zinc-400 font-bold italic">Nenhum escritor encontrado para "{{ $search }}".</p>
            </div>
        @endforelse
    </div>

    <div class="mt-20">
        {{ $this->writers->links() }}
    </div>
</div>
