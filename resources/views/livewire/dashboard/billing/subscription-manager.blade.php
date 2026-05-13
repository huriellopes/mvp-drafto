<div class="max-w-5xl space-y-10 pb-20">
    {{ Breadcrumbs::render('dashboard.billing.index') }}

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Status da Assinatura --}}
        <div class="lg:col-span-2 space-y-8">
            <x-ui.section-card title="Plano Atual" description="Detalhes da sua assinatura ativa no momento.">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="h-16 w-16 rounded-[2rem] bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20 shadow-sm">
                            <x-lucide-award class="h-8 w-8" />
                        </div>
                        <div>
                            <h3 class="text-xl font-black italic tracking-tighter uppercase text-zinc-900 dark:text-white">
                                {{ $user->getPlanName() }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                @if($user->subscribed())
                                    @php
                                        $statusEnum = \App\Enums\StripeSubscriptionStatusEnum::tryFrom($subscription->asStripeSubscription()->status);
                                    @endphp
                                    
                                    <x-ui.badge 
                                        :label="$statusEnum ? $statusEnum->label() : 'Ativa'" 
                                        :color="$statusEnum ? $statusEnum->color() : 'green'" 
                                    />
                                    
                                    @if($subscription->onGracePeriod())
                                        <x-ui.badge label="Expira em {{ $subscription->ends_at->format('d/m/Y') }}" color="orange" />
                                    @endif
                                @elseif($user->is_lifetime)
                                    <x-ui.badge label="Vitalícia" color="blue" />
                                @else
                                    <x-ui.badge label="Gratuito" color="gray" />
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if($user->subscribed())
                            <x-ui.button href="{{ route('dashboard.billing.portal') }}" variant="secondary" size="sm" class="!rounded-2xl" wire:navigate="false">
                                <x-lucide-credit-card class="h-4 w-4 mr-2" />
                                Formas de Pagamento
                            </x-ui.button>
                        @endif
                        
                        {{-- Sênior: Admin e Vitalício não precisam mudar de plano, pois já têm tudo --}}
                        @if(!$user->isAdmin() && !$user->is_lifetime)
                            <x-ui.button href="{{ route('dashboard.billing.plans') }}" size="sm" class="!rounded-2xl">
                                Mudar Plano
                            </x-ui.button>
                        @endif
                    </div>
                </div>

                @if($subscription && !$user->is_lifetime)
                    <div class="mt-8 pt-8 border-t border-zinc-100 dark:border-zinc-800 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Próxima Cobrança</p>
                            <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300">
                                {{ $subscription->asStripeSubscription()->current_period_end ? \Carbon\Carbon::createFromTimestamp($subscription->asStripeSubscription()->current_period_end)->format('d/m/Y') : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Valor Estimado</p>
                            <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300">
                                {{ money($subscription->asStripeSubscription()->plan->amount, $subscription->asStripeSubscription()->plan->currency) }}
                            </p>
                        </div>
                    </div>
                @endif
            </x-ui.section-card>

            {{-- Histórico de Faturas (Oculto para Admin/Vitalício) --}}
            @if(!$user->isAdmin() && !$user->is_lifetime)
                <x-ui.section-card title="Histórico de Faturas" description="Visualize e baixe seus comprovantes de pagamento anteriores.">
                    @if($this->invoices->isNotEmpty())
                        <x-ui.table>
                            <x-slot:header>
                                <x-ui.table.th label="Data" />
                                <x-ui.table.th label="Valor" />
                                <x-ui.table.th label="Status" />
                                <x-ui.table.th label="Ações" align="right" />
                            </x-slot:header>

                            @foreach($this->invoices as $invoice)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors">
                                    <td class="px-6 py-4 text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        {{ $invoice->date()->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                        {{ $invoice->total() }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-ui.badge :label="$invoice->status" :color="$invoice->status === 'paid' ? 'green' : 'red'" />
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button wire:click="downloadInvoice('{{ $invoice->id }}')" wire:loading.attr="disabled" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-700 transition disabled:opacity-50">
                                            Baixar PDF
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    @else
                        <div class="py-12 text-center">
                            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-50 dark:bg-zinc-800 text-zinc-400 mb-4">
                                <x-lucide-receipt-text class="h-6 w-6" />
                            </div>
                            <p class="text-sm text-zinc-500 font-medium italic">Nenhuma fatura encontrada até o momento.</p>
                        </div>
                    @endif
                </x-ui.section-card>
            @endif
        </div>

        {{-- Sidebar de Benefícios --}}
        <div class="space-y-8">
            <div class="rounded-[2.5rem] bg-zinc-900 p-8 text-white relative overflow-hidden border border-zinc-800 shadow-2xl">
                <div class="absolute -right-10 -top-10 h-40 w-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
                
                <h4 class="relative z-10 text-lg font-black italic uppercase tracking-tighter mb-6">Benefícios do Plano</h4>
                
                <ul class="relative z-10 space-y-4">
                    @php
                        $features = match(true) {
                            $user->isAdmin() || $user->is_lifetime => $proPlan?->features ?? [],
                            default => $user->plan?->features ?? ['Acesso básico', 'Publicação limitada']
                        };
                    @endphp

                    @foreach($features as $feature)
                        <li class="flex items-start gap-3 text-xs font-medium text-zinc-400">
                            <x-lucide-check-circle-2 class="h-4 w-4 text-indigo-500 shrink-0" />
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                <div class="relative z-10 mt-10 pt-8 border-t border-zinc-800">
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-2">Suporte Prioritário</p>
                    <p class="text-[11px] leading-relaxed text-zinc-400">Dúvidas sobre cobrança? Fale com nosso time através do suporte@drafto.com.br</p>
                </div>
            </div>
        </div>
    </div>
</div>
