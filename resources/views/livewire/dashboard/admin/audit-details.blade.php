<x-ui.modal name="audit-details-modal" title="Detalhes da Auditoria">
    @if($audit)
        <div class="space-y-6">
            {{-- Info Header --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 rounded-3xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800 shadow-xs">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Usuário</p>
                    <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $audit->user?->name ?? 'Sistema' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Evento</p>
                    <x-ui.badge :color="match($audit->event) {
                        'created' => 'green',
                        'updated' => 'orange',
                        'deleted' => 'red',
                        default => 'blue'
                    }">
                        {{ $audit->event }}
                    </x-ui.badge>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Data</p>
                    <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $audit->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Endereço IP</p>
                    <p class="text-sm font-mono text-zinc-500 dark:text-zinc-400">{{ $audit->ip_address }}</p>
                </div>
            </div>

            {{-- Comparison Table --}}
            <div class="overflow-hidden rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-zinc-50/80 dark:bg-zinc-800/50 border-b border-zinc-100 dark:border-zinc-800">
                            <th class="px-6 py-4 font-black uppercase tracking-widest text-[10px] text-zinc-500 w-1/3">Campo</th>
                            <th class="px-6 py-4 font-black uppercase tracking-widest text-[10px] text-zinc-500">Antes</th>
                            <th class="px-6 py-4 font-black uppercase tracking-widest text-[10px] text-zinc-500">Depois</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @php
                            $allKeys = array_unique(array_merge(array_keys($audit->old_values), array_keys($audit->new_values)));
                        @endphp

                        @foreach($allKeys as $key)
                            <tr class="hover:bg-zinc-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                                <td class="px-6 py-4 font-bold text-zinc-900 dark:text-zinc-300 text-xs">
                                    {{ $key }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if(isset($audit->old_values[$key]))
                                        <span class="text-red-600 dark:text-red-400 line-through decoration-red-500/30">
                                            {{ is_array($audit->old_values[$key]) ? json_encode($audit->old_values[$key]) : $audit->old_values[$key] }}
                                        </span>
                                    @else
                                        <span class="text-zinc-300 dark:text-zinc-600 italic">vazio</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if(isset($audit->new_values[$key]))
                                        <span class="text-green-600 dark:text-green-400 font-bold">
                                            {{ is_array($audit->new_values[$key]) ? json_encode($audit->new_values[$key]) : $audit->new_values[$key] }}
                                        </span>
                                    @else
                                        <span class="text-zinc-300 dark:text-zinc-600 italic">vazio</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Metadata --}}
            <div class="p-5 rounded-3xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800">
                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2">User Agent</p>
                <p class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400 leading-relaxed">{{ $audit->user_agent }}</p>
            </div>

            <div class="flex justify-end pt-4">
                <x-ui.button wire:click="closeModal" variant="secondary" size="sm" class="!rounded-2xl">
                    Fechar
                </x-ui.button>
            </div>
        </div>
    @endif
</x-ui.modal>
