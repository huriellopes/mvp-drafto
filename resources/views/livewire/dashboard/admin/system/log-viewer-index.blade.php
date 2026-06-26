<div class="space-y-6">
    {{ Breadcrumbs::render('admin.system-logs.index') }}

    <header class="flex flex-col gap-1">
        <h2 class="text-2xl font-black italic uppercase tracking-tighter text-zinc-900 dark:text-white">{{ __('dashboard.logs.title') }}</h2>
        <p class="text-xs font-bold uppercase tracking-widest text-zinc-500">{{ __('dashboard.logs.subtitle') }}</p>
    </header>

    {{-- Tabs --}}
    <div class="flex flex-wrap items-center gap-1 border-b border-zinc-200 dark:border-zinc-800">
        @foreach(['errors' => __('dashboard.logs.tab_errors'), 'jobs' => __('dashboard.logs.tab_jobs'), 'debug' => __('dashboard.logs.tab_debug')] as $key => $label)
            <button
                type="button"
                wire:click="selectTab('{{ $key }}')"
                @class([
                    'relative px-4 py-2.5 text-xs font-black uppercase tracking-widest transition',
                    'text-indigo-600 dark:text-indigo-400' => $tab === $key,
                    'text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200' => $tab !== $key,
                ])
            >
                {{ $label }}
                @if($tab === $key)
                    <span class="absolute inset-x-2 -bottom-px h-0.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Toolbar (somente abas de arquivo) --}}
    @if($tab !== 'jobs')
        <div class="flex flex-wrap items-center gap-3">
            <div class="min-w-56">
                <x-ui.select wire:model.live="file" :label="__('dashboard.logs.file_label')">
                    @foreach($files as $logFile)
                        <option value="{{ $logFile }}">{{ $logFile }}</option>
                    @endforeach
                    @if(empty($files))
                        <option value="">{{ __('dashboard.logs.no_files') }}</option>
                    @endif
                </x-ui.select>
            </div>
            <x-ui.button wire:click="$refresh" variant="secondary" size="sm" icon="refresh-cw" class="mt-6">
                {{ __('dashboard.logs.refresh') }}
            </x-ui.button>
        </div>
    @endif

    {{-- Conteúdo: Jobs --}}
    @if($tab === 'jobs')
        @if($failedJobs->isEmpty())
            <x-ui.empty-state :title="__('dashboard.logs.jobs_empty_title')" :description="__('dashboard.logs.jobs_empty_desc')" />
        @else
            <x-ui.table>
                <x-slot:header>
                    <x-ui.table.th :label="__('dashboard.logs.col_job_queue')" />
                    <x-ui.table.th :label="__('dashboard.logs.col_failed_at')" />
                    <x-ui.table.th :label="__('dashboard.logs.col_actions')" />
                </x-slot:header>

                @foreach($failedJobs as $job)
                    <tr wire:key="job-{{ $job['uuid'] }}" class="align-middle">
                        <td class="px-6 py-4">
                            <p class="font-mono text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $job['job'] }}</p>
                            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-widest text-zinc-400">{{ $job['queue'] }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-zinc-500">{{ $job['failed_at'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <x-ui.tooltip :text="__('dashboard.logs.detail')">
                                    <button wire:click="showDetail('{{ $job['uuid'] }}')" type="button" class="flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-100 text-zinc-500 transition hover:bg-zinc-200 hover:text-zinc-900 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:hover:text-white">
                                        <x-lucide-eye class="h-4 w-4" />
                                    </button>
                                </x-ui.tooltip>

                                <x-ui.tooltip :text="__('dashboard.logs.retry')">
                                    <button wire:click="confirmRetry('{{ $job['uuid'] }}')" type="button" class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400">
                                        <x-lucide-rotate-ccw class="h-4 w-4" />
                                    </button>
                                </x-ui.tooltip>

                                <x-ui.tooltip :text="__('dashboard.logs.forget')" position="left">
                                    <button wire:click="confirmForget('{{ $job['uuid'] }}')" type="button" class="flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-100 text-zinc-500 transition hover:bg-red-50 hover:text-red-500 dark:bg-zinc-800 dark:hover:bg-red-500/10">
                                        <x-lucide-trash-2 class="h-4 w-4" />
                                    </button>
                                </x-ui.tooltip>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            {{-- Modal: detalhe do erro do job --}}
            <x-ui.modal name="job-detail" :title="__('dashboard.logs.detail_title')">
                @if($detailJob)
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('dashboard.logs.col_job') }}</p>
                                <p class="font-mono text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $detailJob['job'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('dashboard.logs.col_queue') }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $detailJob['queue'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('dashboard.logs.col_failed_at') }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $detailJob['failed_at'] }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ __('dashboard.logs.col_error') }}</p>
                            <textarea
                                readonly
                                rows="12"
                                class="w-full resize-y rounded-2xl border border-zinc-100 bg-zinc-50 p-4 font-mono text-[11px] leading-relaxed text-red-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-zinc-800 dark:bg-zinc-950 dark:text-red-400"
                            >{{ $detailJob['error'] ?: __('dashboard.logs.no_error') }}</textarea>
                        </div>
                    </div>
                @endif
            </x-ui.modal>

            {{-- Modais de confirmação (operam sobre o job selecionado) --}}
            <x-ui.confirm-modal
                name="confirm-retry-job"
                :title="__('dashboard.logs.retry_title')"
                :content="__('dashboard.logs.retry_warning')"
                :buttonText="__('dashboard.logs.retry')"
                variant="primary"
                action="retryJob"
            />

            <x-ui.confirm-modal
                name="confirm-forget-job"
                :title="__('dashboard.logs.forget_title')"
                :content="__('dashboard.logs.forget_warning')"
                :buttonText="__('dashboard.logs.forget')"
                variant="danger"
                action="forgetJob"
            />
        @endif

    {{-- Conteúdo: Erros / Debug --}}
    @else
        @forelse($entries as $i => $entry)
            <div wire:key="log-{{ $i }}" x-data="{ open: false }" class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-start gap-3 p-4">
                    <x-ui.badge :label="mb_strtoupper($entry->level)" :color="$entry->color()" />
                    <div class="min-w-0 flex-1">
                        <p class="break-words font-mono text-sm text-zinc-800 dark:text-zinc-100">{{ $entry->summary }}</p>
                        <p class="mt-1 text-[11px] font-medium text-zinc-400">{{ $entry->loggedAt }}</p>
                    </div>
                    @if($entry->details !== '')
                        <button type="button" @click="open = !open" class="shrink-0 rounded-lg px-2 py-1 text-[10px] font-black uppercase tracking-widest text-zinc-400 transition hover:text-indigo-600">
                            <span x-text="open ? @js(__('dashboard.logs.hide')) : @js(__('dashboard.logs.details'))"></span>
                        </button>
                    @endif
                </div>
                @if($entry->details !== '')
                    <pre x-show="open" x-cloak class="overflow-x-auto border-t border-zinc-100 bg-zinc-50 p-4 text-[11px] leading-relaxed text-zinc-600 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400">{{ $entry->details }}</pre>
                @endif
            </div>
        @empty
            <x-ui.empty-state :title="__('dashboard.logs.empty_title')" :description="__('dashboard.logs.empty_desc')" />
        @endforelse
    @endif
</div>
