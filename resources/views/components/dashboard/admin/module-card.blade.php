@props(['module'])

<div @class([
    'group relative flex flex-col justify-between rounded-[2.5rem] border p-8 transition-all duration-500',
    'bg-white border-zinc-100 shadow-sm hover:shadow-xl hover:border-indigo-500/20' => $module->is_enabled,
    'bg-zinc-50 border-zinc-200 opacity-75' => !$module->is_enabled,
])>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div @class([
                'h-14 w-14 flex items-center justify-center rounded-2xl transition-colors duration-500',
                'bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white' => $module->is_enabled,
                'bg-zinc-200 text-zinc-400' => !$module->is_enabled,
            ])>
                <x-dynamic-component :component="'lucide-' . ($module->icon ?? 'component')" class="h-7 w-7" />
            </div>

            <div @class([
                'px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border',
                'bg-emerald-50 text-emerald-600 border-emerald-100' => $module->is_enabled,
                'bg-zinc-100 text-zinc-400 border-zinc-200' => !$module->is_enabled,
            ])>
                {{ $module->is_enabled ? 'Ativo' : 'Inativo' }}
            </div>
        </div>

        <div class="space-y-2">
            <h3 class="text-xl font-black text-zinc-900 tracking-tighter uppercase italic">{{ $module->name }}</h3>
            <p class="text-sm text-zinc-500 font-medium leading-relaxed line-clamp-2">
                {{ $module->description }}
            </p>
        </div>
    </div>

    <div class="mt-8 pt-6 border-t border-zinc-100 flex items-center justify-between">
        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
            ID: {{ $module->slug }}
        </span>

        {{-- Toggle Switch --}}
        <button 
            type="button"
            wire:click="toggleModule('{{ $module->slug->value }}')"
            @class([
                'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2',
                'bg-indigo-600' => $module->is_enabled,
                'bg-zinc-200 dark:bg-zinc-700' => !$module->is_enabled,
            ])
            role="switch" 
            aria-checked="{{ $module->is_enabled ? 'true' : 'false' }}"
        >
            <span 
                aria-hidden="true" 
                @class([
                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                    'translate-x-5' => $module->is_enabled,
                    'translate-x-0' => !$module->is_enabled,
                ])
            ></span>
        </button>
    </div>
</div>
