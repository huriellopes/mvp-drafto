<div class="space-y-6">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white leading-tight">{{ __('dashboard.admin.analytics.title') }}</h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.admin.analytics.subtitle') }}</p>
    </div>

    {{-- Filters --}}
    <div class="flex items-center gap-4 bg-white dark:bg-zinc-900 p-4 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('dashboard.admin.analytics.period') }}</span>
        <div class="w-48">
            <x-ui.select wire:model.live="days">
                @foreach(__('dashboard.admin.analytics.periods') as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>
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
