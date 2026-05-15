@use(App\Enums\SupportStatusEnum)
<div class="space-y-8 pb-20">
    {{ Breadcrumbs::render('dashboard.admin.support') }}

    <header class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tighter uppercase italic">Gestão de Suporte</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 font-medium">Gerencie e responda os tickets abertos pelos usuários.</p>
        </div>
    </header>

    {{-- Filtros --}}
    <section class="flex flex-col lg:flex-row gap-4 items-center bg-white dark:bg-zinc-900 p-4 rounded-3xl border border-zinc-100 dark:border-zinc-800 shadow-sm">
        <div class="w-full lg:flex-1">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                placeholder="Pesquisar por assunto ou usuário..."
                class="!bg-zinc-50 dark:!bg-zinc-950 rounded-full border-none shadow-none focus-within:ring-0"
            />
        </div>
        <div class="flex items-center gap-4 w-full lg:w-auto">
            <x-ui.select wire:model.live="status">
                <option value="">Todos os Status</option>
                @foreach(SupportStatusEnum::cases() as $support)
                    <option value="{{ $support->value }}">{{ $support->label() }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </section>

    {{-- Lista de Tickets --}}
    <div class="bg-white dark:bg-zinc-900 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-100 dark:border-zinc-800">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-zinc-400">Usuário</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-zinc-400">Assunto</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-zinc-400">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-zinc-400">Data</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-zinc-400 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @forelse($supports as $ticket)
                        <tr wire:key="admin-ticket-{{ $ticket->id }}" class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-[10px] text-zinc-500 uppercase">
                                        {{ get_initials($ticket->user->name) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $ticket->user->name }}</p>
                                        <p class="text-[10px] text-zinc-400">{{ $ticket->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $ticket->subject }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <x-ui.badge :label="$ticket->status->label()" :color="$ticket->status->color()" />
                            </td>
                            <td class="px-8 py-5 text-sm text-zinc-400">
                                {{ $ticket->created_at->format('d/m/y H:i') }}
                            </td>
                            <td class="px-8 py-5 text-right">
                                <x-ui.button sizes="xs" variant="primary" wire:click="selectSupport({{ $ticket->id }})">
                                    Responder
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center justify-center space-y-4">
                                    <x-lucide-help-circle class="h-12 w-12 text-zinc-200" />
                                    <p class="text-zinc-500 font-medium">Nenhum ticket encontrado.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $supports->links() }}
    </div>

    {{-- Modal de Resposta --}}
    <x-ui.modal name="respond-support" title="Responder Ticket">
        <div x-data="{ sending: false }" @submit.prevent="sending = true; $wire.saveResponse()">
            <form class="space-y-6">
                @if($selectedSupportId)
                    @php $active = $supports->firstWhere('id', $selectedSupportId); @endphp
                    @if($active)
                        <div class="p-5 rounded-2xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-100 dark:border-zinc-800 space-y-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Mensagem do Usuário:</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed italic">"{{ $active->message }}"</p>
                        </div>

                        <x-ui.select label="Novo Status" wire:model="newStatus">
                            @foreach(SupportStatusEnum::cases() as $support)
                                <option value="{{ $support->value }}">{{ $support->label() }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.textarea
                            label="Sua Resposta"
                            wire:model="adminResponse"
                            placeholder="Escreva a solução ou orientação para o usuário..."
                            rows="6"
                        />

                        <div class="flex justify-end pt-4 gap-3">
                            <x-ui.button type="button" variant="secondary" @click="$dispatch('close-modal', { name: 'respond-support' })" class="px-8 rounded-2xl">
                                Cancelar
                            </x-ui.button>
                            <x-ui.button type="submit" loading="saveResponse" class="px-12 rounded-2xl shadow-lg">
                                Enviar Resposta
                            </x-ui.button>
                        </div>
                    @endif
                @else
                    <div class="py-12 flex flex-col items-center justify-center text-center space-y-4">
                        <x-lucide-loader-2 class="h-8 w-8 animate-spin text-zinc-300" />
                        <p class="text-zinc-500 text-sm">Carregando dados do ticket...</p>
                    </div>
                @endif
            </form>
        </div>
    </x-ui.modal>
</div>
