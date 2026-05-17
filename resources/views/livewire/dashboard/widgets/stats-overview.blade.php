<section @class([
    'grid grid-cols-1 gap-4 sm:grid-cols-2',
    'xl:grid-cols-5 lg:grid-cols-3' => count($this->stats) === 5,
    'xl:grid-cols-4' => count($this->stats) !== 5,
])>
    @foreach($this->stats as $stat)
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-zinc-300">
            <p class="text-sm font-medium text-zinc-500">
                {{ $stat['title'] }}
            </p>

            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-semibold tracking-tight text-zinc-900">
                    {{ number_format($stat['value'], 0, ',', '.') }}
                </span>
            </div>

            <p class="mt-1 text-xs text-zinc-400">
                {{ $stat['desc'] }}
            </p>
        </div>
    @endforeach
</section>
