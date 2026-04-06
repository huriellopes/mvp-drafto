@use(App\Enums\ReportStatusEnum)
@use(App\Enums\ReportReasonEnum)

<div class="space-y-6">
    {{-- Filtros --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center">
            <div class="w-full md:w-80">
                <x-ui.input
                    wire:model.live.debounce.300ms="filters.search"
                    placeholder="Buscar por repórter ou motivo..."
                >
                    <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
                </x-ui.input>
            </div>

            <div class="flex gap-4">
                <select wire:model.live="filters.status" class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-profile-primary focus:ring-0 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">
                    <option value="">Todos os Status</option>
                    @foreach(ReportStatusEnum::options() as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filters.reason" class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-profile-primary focus:ring-0 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">
                    <option value="">Todos os Motivos</option>
                    @foreach(ReportReasonEnum::options() as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Tabela --}}
    <x-ui.table>
        <x-slot:header>
            <x-ui.table.th label="Repórter" column="reporter_id" :sort="$filters->sort" :direction="$filters->direction" />
            <x-ui.table.th label="Tipo" />
            <x-ui.table.th label="Status" column="status" :sort="$filters->sort" :direction="$filters->direction" />
            <x-ui.table.th label="Ações" align="right" />
        </x-slot:header>

        @forelse($this->reports as $report)
            <tr wire:key="{{ $report->id }}" class="group hover:bg-zinc-50/50">
                <td class="px-6 py-4 font-medium">{{ $report->reporter->name }}</td>
                <td class="px-6 py-4 uppercase text-xs text-zinc-400">{{ class_basename($report->reportable_type) }}</td>
                <td class="px-6 py-4 text-sm">{{ $report->status->label() }}</td>
                <td class="px-6 py-4">
                    <div class="flex justify-end gap-2">
                        <x-ui.button variant="secondary" wire:click="openResponseModal({{ $report->id }})" class="p-2">
                            <x-lucide-message-square class="h-4 w-4" />
                        </x-ui.button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-6 py-20 text-center">
                    <div class="flex flex-col items-center justify-center text-zinc-400">
                        <x-lucide-inbox class="h-12 w-12 mb-4" />
                        <p class="text-lg font-medium">Nenhuma Reclamação encontrado.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        <x-slot:footer>{{ $this->reports->links() }}</x-slot:footer>
    </x-ui.table>

    {{-- Modal de Resposta e Moderação --}}
    <x-ui.modal name="report-response-modal" title="Analisar Denúncia">
        <form wire:submit.prevent="submitResponse" class="space-y-6">
            <div class="rounded-2xl bg-zinc-50 p-4 border border-zinc-100 text-sm">
                <p class="font-bold text-zinc-900">Relato do usuário:</p>
                <p class="mt-1 text-zinc-600 italic">"{{ $activeReport?->description ?? 'Sem descrição' }}"</p>
            </div>

            <x-ui.textarea
                label="Sua Resposta (Feedback para o denunciante)"
                wire:model="adminFeedback"
                placeholder="Explique a decisão tomada..."
                :error="$errors->first('adminFeedback')"
            />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.select label="Novo Status" wire:model="selectedStatus">
                    @foreach(ReportStatusEnum::options() as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="border-t border-zinc-100 pt-4">
                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model.live="shouldBanUser" id="ban_check" class="h-5 w-5 rounded border-zinc-300 text-red-600 focus:ring-red-500">
                    <label for="ban_check" class="text-sm font-bold text-red-600">Banir usuário denunciado (30 dias)</label>
                </div>

                @if($shouldBanUser)
                    <div class="mt-4 animate-in fade-in slide-in-from-top-2">
                        <x-ui.input
                            label="Motivo do Banimento (Enviado ao infrator)"
                            wire:model="banReason"
                            placeholder="Ex: Discurso de ódio detectado no comentário..."
                            :error="$errors->first('banReason')"
                        />
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <x-ui.button variant="secondary" x-on:click="show = false" type="button">Cancelar</x-ui.button>
                <x-ui.button type="submit" loading="submitResponse">Salvar e Notificar</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
