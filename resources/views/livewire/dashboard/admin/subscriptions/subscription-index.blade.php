<div class="space-y-8">
    {{ Breadcrumbs::render('dashboard.subscriptions.index') }}

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <x-ui.stat-card
            title="Assinantes Ativos"
            :value="$this->stats['active']"
            class="rounded-[2rem]!"
        />
        <x-ui.stat-card
            title="Em Período Trial"
            :value="$this->stats['trialing']"
            class="rounded-[2rem]! text-indigo-600!"
        />
        <x-ui.stat-card
            title="Cancelamentos"
            :value="$this->stats['cancelled']"
            class="rounded-[2rem]! text-amber-500!"
        />
        @php
            $percent = $this->stats['total_users'] > 0 ? ($this->stats['active'] / $this->stats['total_users']) * 100 : 0;
        @endphp
        <x-ui.stat-card
            title="Penetração (Base)"
            :value="number_format($percent, 1) . '%'"
            class="rounded-[2rem]!"
        />
    </div>

    {{-- Filtros --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-4 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div class="flex flex-1 items-center gap-3">
            <div class="w-full md:w-80">
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Buscar por cliente ou e-mail...">
                    <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
                </x-ui.input>
            </div>

            <x-ui.select wire:model.live="status" class="w-48">
                <option value="">Status (Todos)</option>
                <option value="active">Ativas</option>
                <option value="trialing">Trial</option>
                <option value="past_due">Atrasadas</option>
                <option value="canceled">Canceladas</option>
            </x-ui.select>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="min-h-[500px]" wire:loading.class="opacity-50 pointer-events-none transition-all duration-300">
        <x-ui.table>
            <x-slot:header>
                <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-zinc-400 italic">Cliente</th>
                <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-zinc-400 italic">Plano / Tipo</th>
                <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-zinc-400 italic">Status Stripe</th>
                <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-zinc-400 italic">Início / Renovação</th>
                <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-zinc-400 italic">Ações</th>
            </x-slot:header>

            @forelse($this->subscriptions as $sub)
                @include('livewire.dashboard.admin.subscriptions.partials.subscription-row', ['sub' => $sub])
            @empty
                <tr>
                    <td colspan="5" class="py-20 text-center">
                        <x-ui.empty-state
                            icon="credit"
                            title="Nenhuma assinatura processada."
                            description="Não assinaturas para o filtro selecionado!"
                        />
                    </td>
                </tr>
            @endforelse

            <x-slot:footer>
                {{ $this->subscriptions->links() }}
            </x-slot:footer>
        </x-ui.table>
    </div>

    {{-- Modal de Confirmação de Cancelamento --}}
    <x-ui.confirm-modal
        name="confirm-cancel-subscription"
        title="Cancelar Assinatura"
        content="Deseja realmente cancelar esta assinatura imediatamente no Stripe? Esta ação não poderá ser desfeita e o usuário perderá o acesso premium instantaneamente."
        buttonText="Sim, Cancelar Agora"
        variant="danger"
        action="cancelSubscription"
    />

    {{-- Modal de Detalhes da Assinatura --}}
    <x-ui.modal name="subscription-details" title="Detalhes da Assinatura">
        @if($this->selectedSubscription)
            <div class="space-y-6">
                <div class="flex items-center gap-4 p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800">
                    <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xl">
                        {{ substr($this->selectedSubscription->user->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-zinc-900 dark:text-white">{{ $this->selectedSubscription->user->name }}</h4>
                        <p class="text-sm text-zinc-500">{{ $this->selectedSubscription->user->email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Status Atual</p>
                        <x-ui.badge :label="$this->selectedSubscription->stripe_status" color="indigo" />
                    </div>
                    <div class="p-4 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Tipo de Plano</p>
                        <p class="font-bold text-zinc-900 dark:text-white uppercase">{{ $this->selectedSubscription->type }}</p>
                    </div>
                    <div class="p-4 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">ID Stripe</p>
                        <p class="text-xs font-mono text-zinc-600">{{ $this->selectedSubscription->stripe_id }}</p>
                    </div>
                    <div class="p-4 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Data de Início</p>
                        <p class="text-sm text-zinc-900 dark:text-white">{{ $this->selectedSubscription->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                @if($this->selectedSubscription->ends_at)
                    <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20">
                        <p class="text-[10px] font-black uppercase tracking-widest text-red-400 mb-1">Data de Cancelamento/Expiração</p>
                        <p class="text-sm font-bold text-red-600">{{ $this->selectedSubscription->ends_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif

                <div class="flex justify-end gap-3 mt-8">
                    <button
                        x-on:click="$dispatch('close-modal', { name: 'subscription-details' })"
                        class="px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 rounded-xl transition"
                    >
                        Fechar
                    </button>
                    <a
                        href="https://dashboard.stripe.com/subscriptions/{{ $this->selectedSubscription->stripe_id }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition"
                    >
                        <x-lucide-external-link class="h-4 w-4" />
                        Ver no Stripe
                    </a>
                </div>
            </div>
        @else
            <div class="py-12 flex justify-center">
                <x-lucide-loader-2 class="h-8 w-8 animate-spin text-zinc-300" />
            </div>
        @endif
    </x-ui.modal>
</div>
