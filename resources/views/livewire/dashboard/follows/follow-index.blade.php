<div class="space-y-8 pb-20">
    {{-- Header / Tabs e Sort --}}
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-2 rounded-[2rem] bg-zinc-100 p-1.5 w-fit">
            <button wire:click="$set('tab', 'followers')" @class([
                'px-6 py-2.5 text-sm font-bold rounded-full transition-all duration-300',
                'bg-white text-zinc-900 shadow-sm' => $tab === 'followers',
                'text-zinc-500 hover:text-zinc-700' => $tab !== 'followers'
            ])>
                Seguidores ({{ auth()->user()->followers()->count() }})
            </button>

            <button wire:click="$set('tab', 'following')" @class([
                'px-6 py-2.5 text-sm font-bold rounded-full transition-all duration-300',
                'bg-white text-zinc-900 shadow-sm' => $tab === 'following',
                'text-zinc-500 hover:text-zinc-700' => $tab !== 'following'
            ])>
                Seguindo ({{ auth()->user()->following()->count() }})
            </button>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.select wire:model.live="sort" class="w-44">
                <option value="created_at">Data de adesão</option>
                <option value="name">Nome</option>
            </x-ui.select>

            <button wire:click="sortBy('{{ $sort }}')" class="flex h-11 w-11 items-center justify-center rounded-2xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50">
                <x-lucide-arrow-up-down @class(['h-4 w-4 transition', 'rotate-180' => $direction === 'desc']) />
            </button>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="max-w-md">
        <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Buscar conexões...">
            <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
        </x-ui.input>
    </div>

    {{-- Grid --}}
    <div wire:loading.class="opacity-50 blur-[1px]" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 transition-all">
        @forelse($this->follows as $person)
            <div wire:key="follow-{{ $person->id }}" class="group relative flex items-center justify-between rounded-[2rem] border border-zinc-100 bg-white p-5 transition-all hover:shadow-xl">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 overflow-hidden rounded-2xl bg-zinc-50">
                        @if($person->profile?->avatar_path)
                            <img src="{{ Storage::url($person->profile->avatar_path) }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center font-black text-zinc-300 bg-zinc-100">
                                {{ substr($person->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h4 class="truncate text-sm font-black text-zinc-900">{{ $person->name }}</h4>
                        <p class="truncate text-xs font-medium text-profile-primary">{{ '@' . $person->profile?->username }}</p>
                    </div>
                </div>

                @if($tab === 'following')
                    <button wire:click="confirmUnfollow({{ $person->id }})" class="h-10 w-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 opacity-0 group-hover:opacity-100 transition-all hover:bg-red-600 hover:text-white">
                        <x-lucide-user-minus class="h-4 w-4" />
                    </button>
                @else
                    <a href="{{ route('profile.show', $person->profile->username) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-zinc-50 text-zinc-400 opacity-0 group-hover:opacity-100 transition-all hover:bg-zinc-900 hover:text-white">
                        <x-lucide-external-link class="h-4 w-4" />
                    </a>
                @endif
            </div>
        @empty
            <div class="col-span-full py-20 text-center rounded-[3rem] border-2 border-dashed border-zinc-100">
                <x-lucide-users class="mx-auto h-12 w-12 text-zinc-200" />
                <p class="mt-4 text-zinc-500">Nenhum resultado em {{ $tab === 'followers' ? 'seguidores' : 'seguindo' }}.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-10">{{ $this->follows->links() }}</div>

    {{-- Modais --}}
    <x-ui.confirm-modal
        name="confirm-unfollow-modal"
        title="Deixar de Seguir"
        content="Tem certeza que deseja deixar de seguir este escritor? Você deixará de receber atualizações sobre as novas publicações dele."
        buttonText="Sim, Deixar de Seguir"
        variant="danger"
        action="unfollow"
    />
</div>
