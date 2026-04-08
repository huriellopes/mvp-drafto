@unless ($breadcrumbs->isEmpty())
    <nav aria-label="Breadcrumb" class="flex">
        <ol role="list" class="flex items-center gap-2 overflow-x-auto whitespace-nowrap py-1 scrollbar-hide">

            {{-- Ícone de Início (Home) --}}
            <li class="flex items-center">
                <a href="{{ route('dashboard.index') }}" class="group flex items-center justify-center rounded-lg p-1 text-zinc-400 transition-all hover:bg-zinc-100 hover:text-profile-primary dark:hover:bg-zinc-800">
                    <x-lucide-layout-dashboard class="h-4 w-4" />
                    <span class="sr-only">Início</span>
                </a>
            </li>

            @foreach ($breadcrumbs as $breadcrumb)
                <li class="flex items-center gap-2">
                    {{-- Separador Sênior: Chevron discreto --}}
                    <x-lucide-chevron-right class="h-3.5 w-3.5 shrink-0 text-zinc-300 dark:text-zinc-700" />

                    @if (!is_null($breadcrumb->url) && !$loop->last)
                        {{-- Link do Caminho --}}
                        <a href="{{ $breadcrumb->url }}"
                           class="rounded-lg px-2 py-1 text-xs font-bold uppercase tracking-widest text-zinc-500 transition-all hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-white">
                            {{ $breadcrumb->title }}
                        </a>
                    @else
                        {{-- Estado Ativo (Onde o usuário está agora) --}}
                        <span class="flex items-center gap-1.5 rounded-lg bg-zinc-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-zinc-900 dark:bg-zinc-800 dark:text-white shadow-sm">
                            {{-- Ponto indicador de foco --}}
                            <span class="h-1 w-1 rounded-full bg-profile-primary shadow-[0_0_8px_rgba(var(--profile-primary-rgb),0.5)]"></span>
                            {{ $breadcrumb->title }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endunless
