<div class="space-y-6">
    {{ Breadcrumbs::render('admin.logs.index') }}

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black italic text-zinc-900 dark:text-white uppercase tracking-tighter">Logs de Auditoria</h2>
            <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest mt-1">Rastreamento completo de alterações no sistema</p>
        </div>

        <div class="flex items-center gap-3">
            @if($this->isFileReady)
                <div class="flex items-center gap-2 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 px-4 py-2 rounded-2xl animate-in fade-in slide-in-from-right-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Pronto!</span>
                    <a 
                        href="{{ route('dashboard.temporary-file.download', ['path' => $generatedPath]) }}" 
                        wire:click="clearGeneratedFile"
                        class="flex h-8 items-center gap-2 rounded-xl bg-emerald-600 px-3 text-[10px] font-bold text-white transition hover:bg-emerald-700 shadow-sm"
                    >
                        <x-lucide-download class="h-3 w-3" />
                        Baixar Excel
                    </a>
                    <button wire:click="clearGeneratedFile" class="text-emerald-400 hover:text-emerald-600">
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>
            @elseif($generatedPath)
                <div wire:poll.1s class="flex items-center gap-3 px-4 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 animate-pulse">
                    <x-lucide-loader-2 class="h-4 w-4 animate-spin text-zinc-400" />
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500">Gerando...</span>
                </div>
            @else
                <x-ui.button wire:click="export" class="!rounded-2xl" size="sm" icon="download">
                    Exportar Excel
                </x-ui.button>
            @endif
        </div>
    </div>

    {{-- Filtros --}}
    <x-ui.section-card title="Filtros de auditoria">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-ui.select wire:model.live="userId" label="Usuário">
                <option value="">Todos</option>
                @foreach($users as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="event" label="Evento">
                <option value="">Todos</option>
                @foreach($events as $e)
                    <option value="{{ $e }}">{{ ucfirst($e) }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="auditableType" label="Modelo">
                <option value="">Todos</option>
                @foreach($types as $type)
                    <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.input wire:model.live="startDate" type="date" label="De" />
            <x-ui.input wire:model.live="endDate" type="date" label="Até" />
        </div>
    </x-ui.section-card>

    {{-- Tabela --}}
    <x-ui.table>
        <x-slot:header>
            <x-ui.table.th label="Usuário" />
            <x-ui.table.th label="Evento" />
            <x-ui.table.th label="Modelo" />
            <x-ui.table.th label="IP" />
            <x-ui.table.th label="Data" />
            <x-ui.table.th label="Ações" align="right" />
        </x-slot:header>

        @forelse($audits as $audit)
            <tr class="hover:bg-zinc-50/50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar
                            :name="$audit->user?->name ?? 'Sistema'"
                            size="xs"
                        />
                        <span class="font-bold text-zinc-900 dark:text-white text-xs">{{ $audit->user?->name ?? 'Sistema' }}</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <x-ui.badge :color="match($audit->event) {
                        'created' => 'green',
                        'updated' => 'orange',
                        'deleted' => 'red',
                        default => 'blue'
                    }">
                        {{ $audit->event }}
                    </x-ui.badge>
                </td>
                <td class="px-6 py-4 font-medium text-zinc-600 dark:text-zinc-400 text-xs">
                    {{ str_replace('App\\Models\\', '', $audit->auditable_type) }} #{{ $audit->auditable_id }}
                </td>
                <td class="px-6 py-4 font-mono text-[10px] text-zinc-400">
                    {{ $audit->ip_address }}
                </td>
                <td class="px-6 py-4 text-zinc-500 text-xs">
                    {{ $audit->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-6 py-4 text-right">
                    <button x-on:click="$dispatch('open-audit-details', { auditId: {{ $audit->id }} })" class="text-indigo-600 hover:text-indigo-700 font-black text-[10px] uppercase tracking-widest transition active:scale-95">
                        Detalhes
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="py-12 text-center text-zinc-500 italic text-sm">
                    Nenhum log de auditoria encontrado.
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            {{ $audits->links() }}
        </x-slot:footer>
    </x-ui.table>

    <livewire:dashboard.admin.audit-details />
</div>
