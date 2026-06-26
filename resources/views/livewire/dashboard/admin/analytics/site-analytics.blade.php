<div class="space-y-6">
    {{ Breadcrumbs::render('admin.analytics.index') }}

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white leading-tight">{{ __('dashboard.admin.analytics.title') }}</h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.admin.analytics.subtitle') }}</p>
    </div>

    {{-- Filters & Export --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 bg-white dark:bg-zinc-900 p-6 rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div class="flex flex-col md:flex-row items-end gap-6 flex-1">
            <div class="w-full md:w-48">
                <x-ui.select :label="__('dashboard.admin.analytics.period')" wire:model.live="days">
                    @foreach(__('dashboard.admin.analytics.periods') as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="grid grid-cols-2 gap-4 flex-1 w-full">
                <x-ui.input label="Data Inicial" type="date" wire:model.live="startDate" />
                <x-ui.input label="Data Final" type="date" wire:model.live="endDate" />
            </div>
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
                <x-ui.button wire:click="export" variant="secondary" class="!rounded-2xl shadow-sm">
                    <x-lucide-file-spreadsheet class="h-4 w-4 mr-1" />
                    Exportar Excel
                </x-ui.button>
            @endif
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-ui.stat-card
            :title="__('dashboard.admin.analytics.stats.total_views')"
            :value="number_format($this->analytics->totalViews)"
            :description="__('dashboard.admin.analytics.stats.total_views_desc')"
        />
        <x-ui.stat-card
            :title="__('dashboard.admin.analytics.stats.unique_visitors')"
            :value="number_format($this->analytics->uniqueVisitors)"
            :description="__('dashboard.admin.analytics.stats.unique_visitors_desc')"
        />
        <x-ui.stat-card
            :title="__('dashboard.admin.analytics.stats.avg_duration')"
            :value="$this->analytics->avgDuration . 's'"
            :description="__('dashboard.admin.analytics.stats.avg_duration_desc')"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top Pages --}}
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                <x-lucide-file-text class="h-5 w-5 text-blue-500" />
                {{ __('dashboard.admin.analytics.top_pages') }}
            </h3>
            <div class="space-y-3">
                @forelse($this->analytics->topPages as $page)
                    <div class="flex items-center justify-between p-2 hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-xl transition-colors">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400 truncate max-w-[75%]" title="{{ $page['url'] }}">
                            {{ Str::after($page['url'], config('app.url')) ?: '/' }}
                        </span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ number_format($page['total']) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 italic">{{ __('dashboard.admin.analytics.no_data') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Top Search Queries --}}
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                <x-lucide-search class="h-5 w-5 text-green-500" />
                {{ __('dashboard.admin.analytics.top_searches') }}
            </h3>
            <div class="space-y-3">
                @forelse($this->analytics->topSearches as $search)
                    <div class="flex items-center justify-between p-2 hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-xl transition-colors">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $search['search_query'] }}</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ number_format($search['total']) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 italic">{{ __('dashboard.admin.analytics.no_searches') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
