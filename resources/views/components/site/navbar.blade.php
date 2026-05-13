<nav
    x-data="{ mobileMenuOpen: false }"
    class="fixed top-0 z-[100] w-full border-b border-zinc-100 bg-white/80 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/80 transition-colors duration-300"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between">

            {{-- Lado Esquerdo: Logo e Nav Desktop --}}
            <div class="flex items-center gap-8">
                <a href="/" class="flex items-center gap-2 text-2xl font-black tracking-tighter text-zinc-900 dark:text-white group shrink-0">
                    <div class="h-8 w-8 rounded-xl bg-zinc-900 dark:bg-white group-hover:rotate-12 transition-transform"></div>
                    Drafto.
                </a>

                {{-- Nav Desktop --}}
                <div class="hidden lg:flex items-center gap-6">
                    <a href="{{ route('home') }}" @class([
                        'text-sm font-bold transition',
                        'text-zinc-900 dark:text-white' => request()->routeIs('home'),
                        'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => !request()->routeIs('home')
                    ])>Início</a>

                    <a href="{{ route('posts.explore') }}" @class([
                        'text-sm font-bold transition',
                        'text-zinc-900 dark:text-white' => request()->routeIs('posts.explore'),
                        'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => !request()->routeIs('posts.explore')
                    ])>Artigos</a>

                    <a href="{{ route('writers.explore') }}" @class([
                        'text-sm font-bold transition',
                        'text-zinc-900 dark:text-white' => request()->routeIs('writers.explore'),
                        'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => !request()->routeIs('writers.explore')
                    ])>Escritores</a>
                </div>
            </div>

            {{-- Lado Direito: Busca, Theme, Auth e Hamburguer --}}
            <div class="flex items-center gap-2 md:gap-4">

                {{-- Busca Global: Visível em todos os tamanhos --}}
                {{-- No componente, ajustamos para que no mobile seja apenas um ícone --}}
                <livewire:public.site.global-search />

                {{-- Theme Toggle --}}
                <button @click="darkMode = !darkMode"
                        class="relative flex h-10 w-10 items-center justify-center rounded-2xl border border-zinc-100 bg-zinc-50 text-zinc-500 transition hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800">
                    <x-lucide-sun x-show="!darkMode" class="h-5 w-5" />
                    <x-lucide-moon x-show="darkMode" class="h-5 w-5" x-cloak />
                </button>

                <div class="h-6 w-px bg-zinc-200 dark:bg-zinc-800 hidden lg:block mx-2"></div>

                {{-- Auth Desktop --}}
                <div class="hidden lg:flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard.index') }}" class="text-sm font-bold text-zinc-900 dark:text-white hover:text-profile-primary transition">Dashboard</a>
                        <x-ui.button href="{{ route('dashboard.posts.create') }}" size="sm" class="shadow-xl shadow-zinc-900/10">Escrever</x-ui.button>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition">Entrar</a>
                        <x-ui.button href="{{ route('register') }}" size="sm" variant="primary">Criar conta</x-ui.button>
                    @endauth
                </div>

                {{-- Botão Hamburguer Mobile --}}
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="flex h-10 w-10 items-center justify-center rounded-2xl border border-zinc-100 bg-zinc-50 text-zinc-900 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white lg:hidden transition-all active:scale-90"
                >
                    <x-lucide-menu x-show="!mobileMenuOpen" class="h-5 w-5" />
                    <x-lucide-x x-show="mobileMenuOpen" class="h-5 w-5" x-cloak />
                </button>
            </div>
        </div>
    </div>

    {{-- Menu Mobile Panel --}}
    <div
        x-show="mobileMenuOpen"
        x-collapse
        class="lg:hidden bg-white dark:bg-zinc-950 border-b border-zinc-100 dark:border-zinc-800 shadow-xl"
        x-cloak
    >
        <div class="space-y-1 px-4 pb-8 pt-2">
            <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-base font-bold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors">
                <x-lucide-house class="h-5 w-5 text-zinc-400" /> Início
            </a>
            <a href="{{ route('posts.explore') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-base font-bold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors">
                <x-lucide-newspaper class="h-5 w-5 text-zinc-400" /> Artigos
            </a>
            <a href="{{ route('writers.explore') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-base font-bold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors">
                <x-lucide-users class="h-5 w-5 text-zinc-400" /> Escritores
            </a>

            <div class="my-4 h-px bg-zinc-100 dark:bg-zinc-800 mx-4"></div>

            @auth
                <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-base font-bold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors">
                    <x-lucide-layout-dashboard class="h-5 w-5 text-zinc-400" /> Dashboard
                </a>
                <div class="px-4 pt-4">
                    <x-ui.button href="{{ route('dashboard.posts.create') }}" class="w-full py-4 rounded-2xl">Escrever agora</x-ui.button>
                </div>
            @else
                <a href="{{ route('login') }}" class="block rounded-xl px-4 py-3 text-base font-bold text-zinc-500 dark:text-zinc-400">Entrar na conta</a>
                <div class="px-4 pt-4">
                    <x-ui.button href="{{ route('register') }}" class="w-full py-4 rounded-2xl">Começar a escrever</x-ui.button>
                </div>
            @endauth
        </div>
    </div>
</nav>
