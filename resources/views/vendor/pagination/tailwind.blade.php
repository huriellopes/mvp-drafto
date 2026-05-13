@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('pagination.navigation') }}" class="flex flex-col gap-4">

        <div class="flex items-center justify-between">
            {{-- Desktop: Texto Informativo --}}
            <div class="hidden sm:block">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 italic">
                    {{ __('pagination.showing') }}
                    <span class="text-zinc-900 dark:text-white">{{ $paginator->firstItem() }}</span>
                    {{ __('pagination.to') }}
                    <span class="text-zinc-900 dark:text-white">{{ $paginator->lastItem() }}</span>
                    {{ __('pagination.of') }}
                    <span class="text-zinc-900 dark:text-white">{{ $paginator->total() }}</span>
                    {{ __('pagination.results') }}
                </p>
            </div>

            {{-- Botões de Navegação --}}
            <div class="flex w-full sm:w-auto items-center justify-between sm:justify-end gap-1">

                {{-- Anterior --}}
                @if ($paginator->onFirstPage())
                    <span class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-100 bg-zinc-50 text-zinc-300 cursor-not-allowed dark:bg-zinc-900 dark:border-zinc-800 dark:text-zinc-700">
                        <x-lucide-chevron-left class="h-4 w-4" />
                    </span>
                @else
                    <button wire:click="previousPage" wire:loading.attr="disabled" class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 hover:text-zinc-900 hover:border-zinc-300 transition active:scale-90 shadow-sm dark:bg-zinc-950 dark:border-zinc-800 dark:text-zinc-400 dark:hover:text-white">
                        <x-lucide-chevron-left class="h-4 w-4" />
                    </button>
                @endif

                {{-- Números das Páginas (Desktop) --}}
                <div class="hidden sm:flex gap-1">
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="relative inline-flex h-9 w-9 items-center justify-center text-xs font-black text-zinc-400 italic">...</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-xl bg-zinc-900 px-3 text-xs font-black italic text-white shadow-lg shadow-zinc-900/20 dark:bg-white dark:text-zinc-900">{{ $page }}</span>
                                    </span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" class="relative inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-xs font-bold text-zinc-500 hover:text-zinc-900 hover:border-zinc-400 transition active:scale-90 shadow-sm dark:bg-zinc-950 dark:border-zinc-800 dark:text-zinc-400">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Próximo --}}
                @if ($paginator->hasMorePages())
                    <button wire:click="nextPage" wire:loading.attr="disabled" class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 hover:text-zinc-900 hover:border-zinc-300 transition active:scale-90 shadow-sm dark:bg-zinc-950 dark:border-zinc-800 dark:text-zinc-400 dark:hover:text-white">
                        <x-lucide-chevron-right class="h-4 w-4" />
                    </button>
                @else
                    <span class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-100 bg-zinc-50 text-zinc-300 cursor-not-allowed dark:bg-zinc-900 dark:border-zinc-800 dark:text-zinc-700">
                        <x-lucide-chevron-right class="h-4 w-4" />
                    </span>
                @endif
            </div>
        </div>

        {{-- Info de Resultados (Mobile) --}}
        <div class="sm:hidden text-center">
            <p class="text-[9px] font-black uppercase tracking-widest text-zinc-400 italic">
                {{ __('pagination.showing') }} {{ $paginator->firstItem() }} {{ __('pagination.to') }} {{ $paginator->lastItem() }} {{ __('pagination.of') }} {{ $paginator->total() }}
            </p>
        </div>
    </nav>
@endif
