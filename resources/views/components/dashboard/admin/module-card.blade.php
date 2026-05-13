@props(['module'])

<div @class([
    'relative p-8 rounded-[3rem] border transition-all duration-500 group',
    'bg-white border-zinc-100 shadow-sm hover:border-profile-primary/30' => $module->is_enabled,
    'bg-zinc-50 border-zinc-200 dark:bg-zinc-900/40 dark:border-zinc-800' => !$module->is_enabled,
])>
    <div class="flex items-start justify-between mb-6">
        {{-- Ícone do Módulo --}}
        <div @class([
            'h-14 w-14 rounded-2xl flex items-center justify-center transition-all duration-500',
            'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' => $module->is_enabled,
            'bg-zinc-200 text-zinc-400 dark:bg-zinc-800' => !$module->is_enabled
        ])>
            <x-dynamic-component :component="'lucide-'.$module->icon" class="h-7 w-7" />
        </div>

        {{-- Toggle Switch Ultra Visível --}}
        <div class="flex items-center gap-3">
             <span class="text-[10px] font-black uppercase tracking-tighter text-zinc-400">
                {{ $module->is_enabled ? 'ON' : 'OFF' }}
            </span>

            <button
                wire:click="toggleModule({{ $module->id }})"
                wire:loading.attr="disabled"
                @class([
                    'relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 transition-all duration-200 focus:outline-none ring-offset-2 dark:ring-offset-zinc-900 focus:ring-2 focus:ring-profile-primary/50',
                    // Ativo: Fundo forte (Primary ou Zinc-900)
                    'bg-zinc-900 border-zinc-900 dark:bg-profile-primary dark:border-profile-primary shadow-lg shadow-profile-primary/20' => $module->is_enabled,
                    // Inativo: Borda forte e fundo visível para não "apagar"
                    'bg-zinc-200 border-zinc-300 dark:bg-zinc-800 dark:border-zinc-700' => !$module->is_enabled
                ])
            >
                {{-- A "bolinha" do toggle com sombra para profundidade --}}
                <span @class([
                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-[0_2px_4px_rgba(0,0,0,0.2)] transition duration-300 ease-in-out',
                    'translate-x-5' => $module->is_enabled,
                    'translate-x-0' => !$module->is_enabled
                ])></span>
            </button>
        </div>
    </div>

    {{-- Textos --}}
    <div class="space-y-2">
        <h3 @class([
            'text-xl font-black tracking-tight transition-colors',
            'text-zinc-900 dark:text-white' => $module->is_enabled,
            'text-zinc-400 dark:text-zinc-500' => !$module->is_enabled
        ])>
            {{ $module->name }}
        </h3>
        <p @class([
            'text-sm leading-relaxed line-clamp-2 transition-colors',
            'text-zinc-500 dark:text-zinc-400' => $module->is_enabled,
            'text-zinc-400/60 dark:text-zinc-600' => !$module->is_enabled
        ])>
            {{ $module->description }}
        </p>
    </div>

    {{-- Footer do Card --}}
    <div @class([
        'mt-8 pt-6 border-t flex items-center justify-between',
        'border-zinc-100 dark:border-zinc-800/50' => $module->is_enabled,
        'border-zinc-200/50 dark:border-zinc-800' => !$module->is_enabled
    ])>
        <div class="flex items-center gap-2">
            <div @class([
                'h-2 w-2 rounded-full',
                'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)] animate-pulse' => $module->is_enabled,
                'bg-zinc-300 dark:bg-zinc-700' => !$module->is_enabled
            ])></div>
            <span @class([
                'text-[10px] font-black uppercase tracking-[0.1em]',
                'text-emerald-600 dark:text-emerald-400' => $module->is_enabled,
                'text-zinc-400' => !$module->is_enabled
            ])>
                {{ $module->is_enabled ? 'Módulo Ativo' : 'Módulo Inativo' }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-[9px] font-bold text-zinc-300 dark:text-zinc-600 uppercase">Slug</span>
            <code class="text-[10px] font-mono font-bold bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-lg text-zinc-500 border border-zinc-200 dark:border-zinc-700">
                {{ $module->slug->label() }}
            </code>
        </div>
    </div>
</div>
