@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('pagination.navigation') }}" class="flex items-center justify-between">
        {{-- Mobile --}}
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center rounded-xl border border-zinc-200 bg-white px-4 py-2 text-xs font-black uppercase tracking-widest text-zinc-300 cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" class="relative inline-flex items-center rounded-xl border border-zinc-200 bg-white px-4 py-2 text-xs font-black uppercase tracking-widest text-zinc-600 hover:text-zinc-900 transition active:scale-95 shadow-sm">
                    {!! __('pagination.previous') !!}
                </button>
            @endif

            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" class="relative ml-3 inline-flex items-center rounded-xl border border-zinc-200 bg-white px-4 py-2 text-xs font-black uppercase tracking-widest text-zinc-600 hover:text-zinc-900 transition active:scale-95 shadow-sm">
                    {!! __('pagination.next') !!}
                </button>
            @else
                <span class="relative ml-3 inline-flex items-center rounded-xl border border-zinc-200 bg-white px-4 py-2 text-xs font-black uppercase tracking-widest text-zinc-300 cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-end">
            <div>
                <span class="relative z-0 inline-flex gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true">
                            <span class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-100 bg-zinc-50 text-zinc-300 cursor-not-allowed">
                                <x-lucide-chevron-left class="h-4 w-4" />
                            </span>
                        </span>
                    @else
                        <button wire:click="previousPage" wire:loading.attr="disabled" class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 hover:text-zinc-900 hover:border-zinc-300 transition active:scale-90 shadow-sm">
                            <x-lucide-chevron-left class="h-4 w-4" />
                        </button>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex h-9 w-9 items-center justify-center text-xs font-black text-zinc-400 italic">...</span>
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-xl bg-zinc-900 px-3 text-xs font-black italic text-white shadow-lg shadow-zinc-900/10 ring-1 ring-zinc-900 transition">{{ $page }}</span>
                                    </span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" class="relative inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-xs font-bold text-zinc-500 hover:text-zinc-900 hover:border-zinc-400 transition active:scale-90 shadow-sm">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage" wire:loading.attr="disabled" class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 hover:text-zinc-900 hover:border-zinc-300 transition active:scale-90 shadow-sm">
                            <x-lucide-chevron-right class="h-4 w-4" />
                        </button>
                    @else
                        <span aria-disabled="true">
                            <span class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-100 bg-zinc-50 text-zinc-300 cursor-not-allowed">
                                <x-lucide-chevron-right class="h-4 w-4" />
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
