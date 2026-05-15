<div class="space-y-12 pb-20">
    {{ Breadcrumbs::render('dashboard.support') }}

    <header class="space-y-4">
        <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tighter italic">
            Suporte e <span class="text-indigo-600 dark:text-indigo-400">Atendimento</span>
        </h1>
        <p class="text-zinc-500 dark:text-zinc-400 text-sm font-medium max-w-2xl">
            Precisa de ajuda? Envie um ticket para nossa equipe ou entre em contato pelos canais oficiais. Responderemos diretamente nas suas notificações e por e-mail.
        </p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        {{-- Formulário de Ticket --}}
        <div class="lg:col-span-7">
            <x-ui.section-card title="Novo Ticket" description="Descreva seu problema ou sugestão com detalhes.">
                <form wire:submit.prevent="save" class="space-y-6">
                    <x-ui.input
                        label="Assunto"
                        wire:model="form.subject"
                        placeholder="Ex: Problema com visualização de posts"
                        :error="$errors->first('form.subject')"
                    >
                        <x-slot:label_extra>
                            <x-ui.badge label="Obrigatório" color="red" class="ml-1" />
                        </x-slot:label_extra>
                    </x-ui.input>

                    <x-ui.textarea
                        label="Mensagem"
                        wire:model="form.message"
                        placeholder="Descreva aqui sua dúvida, bug encontrado ou sugestão..."
                        rows="6"
                        :error="$errors->first('form.message')"
                    >
                        <x-slot:label_extra>
                            <x-ui.badge label="Obrigatório" color="red" class="ml-1" />
                        </x-slot:label_extra>
                    </x-ui.textarea>

                    <div class="flex justify-end">
                        <x-ui.button type="submit" loading="save" class="px-12 rounded-2xl shadow-lg">
                            <x-lucide-send class="h-4 w-4 mr-2" />
                            Enviar Ticket
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.section-card>
        </div>

        {{-- Canais Diretos --}}
        <aside class="lg:col-span-5 space-y-6">
            <h3 class="text-xs font-black uppercase tracking-widest text-zinc-400 px-2 mb-4">Canais Diretos</h3>
            
            <a href="https://wa.me/5500000000000" target="_blank" class="group block p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 transition-all hover:border-emerald-500 hover:shadow-xl">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 flex items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600">
                        <x-lucide-message-circle class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="font-black text-zinc-900 dark:text-white">WhatsApp</h3>
                        <p class="text-xs text-zinc-500">Atendimento em horário comercial</p>
                    </div>
                </div>
            </a>

            <a href="mailto:suporte@drafto.com" class="group block p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 transition-all hover:border-indigo-500 hover:shadow-xl">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 flex items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600">
                        <x-lucide-mail class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="font-black text-zinc-900 dark:text-white">E-mail</h3>
                        <p class="text-xs text-zinc-500">suporte@drafto.com</p>
                    </div>
                </div>
            </a>
        </aside>
    </div>

    {{-- Meus Tickets --}}
    @if($tickets->isNotEmpty())
        <section class="space-y-6 mt-12">
            <h3 class="text-xs font-black uppercase tracking-widest text-zinc-400 px-2">Meus Tickets Recentes</h3>
            
            <div class="bg-white dark:bg-zinc-900 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-100 dark:border-zinc-800">
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-zinc-400">Data</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-zinc-400">Assunto</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-zinc-400">Status</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-zinc-400 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                            @foreach($tickets as $ticket)
                                <tr wire:key="ticket-{{ $ticket->id }}" class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="px-8 py-5 text-sm font-medium text-zinc-500">{{ $ticket->created_at->format('d/m/Y') }}</td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $ticket->subject }}</p>
                                    </td>
                                    <td class="px-8 py-5">
                                        <x-ui.badge :label="$ticket->status->label()" :color="$ticket->status->color()" />
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                         @if($ticket->admin_response)
                                            <x-ui.button sizes="xs" variant="secondary" wire:click="selectTicket({{ $ticket->id }})">
                                                Ver Resposta
                                            </x-ui.button>
                                         @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-6">
                {{ $tickets->links() }}
            </div>
        </section>
    @endif

    {{-- Modal de Resposta Único --}}
    <x-ui.modal name="view-response" title="Resposta do Suporte">
        @if($selectedTicketId)
            @php $active = $tickets->firstWhere('id', $selectedTicketId); @endphp
            @if($active)
                <div class="space-y-6">
                    <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-100 dark:border-zinc-800">
                        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2">Sua Mensagem:</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 italic">"{{ $active->message }}"</p>
                    </div>
                    <div class="p-6 rounded-2xl bg-indigo-50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/20">
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-2">Resposta da Equipe:</p>
                        <p class="text-base text-zinc-900 dark:text-white font-medium leading-relaxed">{{ $active->admin_response }}</p>
                        <p class="mt-4 text-[10px] text-zinc-400">Respondido em {{ $active->responded_at?->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="flex justify-end">
                        <x-ui.button variant="secondary" @click="show = false" class="px-8 rounded-2xl">
                            Fechar
                        </x-ui.button>
                    </div>
                </div>
            @endif
        @endif
    </x-ui.modal>
</div>
