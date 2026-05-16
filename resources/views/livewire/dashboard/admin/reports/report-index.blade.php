@use(App\Enums\ReportStatusEnum)
@use(App\Enums\ReportReasonEnum)

<div class="space-y-6">
    {{ Breadcrumbs::render('dashboard.reports.index') }}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white leading-tight">{{ __('dashboard.admin.reports.title') }}</h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.admin.reports.subtitle') }}</p>
    </div>

    {{-- Tabs de Navegação Rápida --}}
    <div class="flex items-center gap-1 border-b border-zinc-200 dark:border-zinc-800 p-1">
        <button wire:click="setTab('all')"
            @class(['px-6 py-3 text-sm font-bold rounded-2xl transition-all', 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-lg' => $tab === 'all', 'text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-900' => $tab !== 'all'])>
            {{ __('dashboard.admin.reports.tabs.all') }}
        </button>
        <button wire:click="setTab('moderation')"
            @class(['px-6 py-3 text-sm font-bold rounded-2xl transition-all', 'bg-red-600 text-white shadow-lg shadow-red-500/20' => $tab === 'moderation', 'text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-900' => $tab !== 'moderation'])>
            {{ __('dashboard.admin.reports.tabs.moderation') }}
        </button>
        <button wire:click="setTab('feedback')"
            @class(['px-6 py-3 text-sm font-bold rounded-2xl transition-all', 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' => $tab === 'feedback', 'text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-900' => $tab !== 'feedback'])>
            {{ __('dashboard.admin.reports.tabs.feedback') }}
        </button>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between bg-white dark:bg-zinc-900 p-4 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center">
            <div class="w-full md:w-80">
                <x-ui.input
                    wire:model.live.debounce.300ms="filters.search"
                    placeholder="{{ __('dashboard.admin.reports.search_placeholder') }}"
                >
                    <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
                </x-ui.input>
            </div>

            <div class="flex gap-4">
                <select wire:model.live="filters.status" class="block w-full rounded-2xl border border-zinc-300 bg-zinc-50 dark:bg-zinc-950 px-4 py-3 text-sm text-zinc-900 dark:text-white outline-none transition focus:border-indigo-500 focus:ring-0 dark:border-zinc-800">
                    <option value="">{{ __('dashboard.admin.reports.status_all') }}</option>
                    @foreach(ReportStatusEnum::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filters.reason" class="block w-full rounded-2xl border border-zinc-300 bg-zinc-50 dark:bg-zinc-950 px-4 py-3 text-sm text-zinc-900 dark:text-white outline-none transition focus:border-indigo-500 focus:ring-0 dark:border-zinc-800">
                    <option value="">{{ __('dashboard.admin.reports.reason_all') }}</option>
                    @foreach(ReportReasonEnum::cases() as $reason)
                        <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Tabela --}}
    <x-ui.table>
        <x-slot:header>
            <x-ui.table.th label="{{ __('dashboard.admin.reports.table.origin') }}" />
            <x-ui.table.th label="{{ __('dashboard.admin.reports.table.reason') }}" />
            <x-ui.table.th label="{{ __('dashboard.admin.reports.table.target') }}" />
            <x-ui.table.th label="{{ __('dashboard.admin.reports.table.status') }}" column="status" :sort="$filters->sort" :direction="$filters->direction" />
            <x-ui.table.th label="{{ __('dashboard.admin.reports.table.actions') }}" align="right" />
        </x-slot:header>

        @forelse($this->reports as $report)
            <tr wire:key="{{ $report->id }}" class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex flex-col">
                        <span class="font-bold text-zinc-900 dark:text-white">{{ $report->reporter?->name ?? __('Anonymous') }}</span>
                        <span class="text-[10px] text-zinc-400 uppercase font-black tracking-widest">{{ $report->created_at->diffForHumans() }}</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span @class([
                        'px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border',
                        'bg-red-50 text-red-600 border-red-100' => in_array($report->reason, [ReportReasonEnum::ABUSE, ReportReasonEnum::HARASSMENT]),
                        'bg-green-50 text-green-600 border-green-100' => $report->reason === ReportReasonEnum::PRAISE,
                        'bg-blue-50 text-blue-600 border-blue-100' => $report->reason === ReportReasonEnum::SUGGESTION,
                        'bg-purple-50 text-purple-600 border-purple-100' => $report->reason === ReportReasonEnum::BUG,
                        'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700' => !in_array($report->reason, [ReportReasonEnum::ABUSE, ReportReasonEnum::HARASSMENT, ReportReasonEnum::PRAISE, ReportReasonEnum::SUGGESTION, ReportReasonEnum::BUG]),
                    ])>
                        {{ $report->reason->label() }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                         <span class="text-[10px] text-zinc-500 font-black uppercase tracking-widest">{{ class_basename($report->reportable_type) }}</span>
                         <span class="text-zinc-300 dark:text-zinc-700">#{{ $report->reportable_id }}</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span @class([
                        'flex items-center gap-1.5 text-xs font-bold',
                        'text-orange-500' => $report->status === ReportStatusEnum::PENDING,
                        'text-green-600' => $report->status === ReportStatusEnum::ACKNOWLEDGED,
                        'text-indigo-600' => $report->status === ReportStatusEnum::IMPLEMENTED,
                        'text-blue-600' => $report->status === ReportStatusEnum::REVIEWED,
                        'text-zinc-400' => $report->status === ReportStatusEnum::DISMISSED,
                        'text-red-600' => $report->status === ReportStatusEnum::ACTION_TAKEN,
                    ])>
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ $report->status->label() }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex justify-end gap-2">
                        <x-ui.button variant="secondary" wire:click="openResponseModal({{ $report->id }})" class="!p-2.5 !rounded-xl">
                            <x-lucide-message-square-more class="h-4 w-4" />
                        </x-ui.button>
                        <x-ui.button variant="danger"
                                     wire:click="confirmDelete({{ $report->id }})"
                                     class="!p-2.5 !rounded-xl bg-red-50 !text-red-600 hover:!bg-red-600 hover:!text-white border-none transition-all">
                            <x-lucide-trash-2 class="h-4 w-4" />
                        </x-ui.button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-24 text-center">
                    <div class="flex flex-col items-center justify-center text-zinc-400">
                        <div class="h-20 w-20 rounded-full bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center mb-6">
                            <x-lucide-inbox class="h-10 w-10 text-zinc-200 dark:text-zinc-800" />
                        </div>
                        <p class="text-lg font-black text-zinc-900 dark:text-white tracking-tighter italic">{{ __('dashboard.admin.reports.empty_state') }}</p>
                        <p class="text-sm">{{ __('dashboard.admin.reports.empty_subtitle') }}</p>
                    </div>
                </td>
            </tr>
        @endforelse

        <x-slot:footer>{{ $this->reports->links() }}</x-slot:footer>
    </x-ui.table>

    <x-ui.confirm-modal
        name="confirm-report-deletion"
        title="Excluir Denúncia?"
        content="Esta ação removerá permanentemente o registro desta denúncia. O histórico de moderação não poderá ser recuperado."
        action="deleteReport"
        buttonText="Sim, Excluir Registro"
        variant="danger"
    />

    {{-- Modal de Resposta --}}
    <x-ui.modal name="report-response-modal" title="{{ __('dashboard.admin.reports.modal.title') }}">
        <form wire:submit.prevent="submitResponse" class="space-y-6">
            <div class="rounded-[2rem] bg-zinc-50 dark:bg-zinc-950 p-6 border border-zinc-100 dark:border-zinc-800 text-sm">
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-2 py-0.5 rounded-md bg-zinc-200 dark:bg-zinc-800 text-[10px] font-black uppercase tracking-widest text-zinc-600 dark:text-zinc-400">{{ $activeReport?->reason?->label() }}</span>
                    <span class="text-zinc-400">•</span>
                    <span class="text-zinc-500 font-medium">{{ __('dashboard.admin.reports.modal.reported_by', ['name' => $activeReport?->reporter?->name ?? __('Anonymous')]) }}</span>
                </div>
                <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed italic">"{{ $activeReport?->description ?? __('dashboard.admin.reports.modal.no_description') }}"</p>
            </div>

            <x-ui.textarea
                label="{{ __('dashboard.admin.reports.modal.label') }}"
                wire:model="adminFeedback"
                rows="4"
                placeholder="{{ __('dashboard.admin.reports.modal.placeholder') }}"
                :error="$errors->first('adminFeedback')"
            />

            <x-ui.select label="{{ __('dashboard.admin.reports.modal.status_label') }}" wire:model="selectedStatus">
                @foreach(ReportStatusEnum::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </x-ui.select>

            {{-- Painel de Moderação --}}
            @if($activeReport && !in_array($activeReport->reason, [ReportReasonEnum::PRAISE, ReportReasonEnum::SUGGESTION]))
                <div class="rounded-[2rem] border border-red-100 dark:border-red-900/20 bg-red-50/30 dark:bg-red-900/5 p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model.live="shouldBanUser" id="ban_check" class="h-5 w-5 rounded-lg border-red-300 text-red-600 focus:ring-red-500">
                        <label for="ban_check" class="text-sm font-bold text-red-700 dark:text-red-400">{{ __('dashboard.admin.reports.modal.ban_label') }}</label>
                    </div>

                    @if($shouldBanUser)
                        <div class="animate-in fade-in slide-in-from-top-2">
                            <x-ui.input
                                label="{{ __('dashboard.admin.reports.modal.ban_reason') }}"
                                wire:model="banReason"
                                placeholder="{{ __('dashboard.admin.reports.modal.ban_placeholder') }}"
                                :error="$errors->first('banReason')"
                            />
                        </div>
                    @endif
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                <x-ui.button variant="secondary" x-on:click="show = false" type="button" class="!w-auto px-8 !rounded-xl">
                    {{ __('dashboard.admin.reports.modal.cancel') }}
                </x-ui.button>
                <x-ui.button type="submit" loading="submitResponse" class="!w-auto px-10 !rounded-xl shadow-lg shadow-zinc-900/10">
                    {{ __('dashboard.admin.reports.modal.submit') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
