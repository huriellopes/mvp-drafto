@props([
    'available' => [], 
    'label' => null, 
    'placeholder' => 'Pesquisar...', 
    'multiple' => false,
    'createMessage' => 'Criar novo: ',
    'error' => null
])

<div x-data="{
    open: false,
    text: '',
    selected: @entangle($attributes->wire('model')),
    items: {{ json_encode($available) }},
    
    select(item) {
        if (typeof item === 'string') {
            item = item.trim();
            if (item === '') return;
        } else {
            item = item.id;
        }
        
        if ({{ $multiple ? 'true' : 'false' }}) {
            if (!this.selected) this.selected = [];
            if (!this.selected.includes(item)) {
                this.selected.push(item);
            }
        } else {
            this.selected = item;
        }
        
        this.text = '';
        this.open = false;
    },
    
    remove(index) {
        if ({{ $multiple ? 'true' : 'false' }}) {
            this.selected.splice(index, 1);
        } else {
            this.selected = null;
        }
    },
    
    get suggestions() {
        if (this.text === '') return [];
        return this.items.filter(i => 
            i.name.toLowerCase().includes(this.text.toLowerCase()) && 
            !(Array.isArray(this.selected) ? this.selected.includes(i.id) : this.selected == i.id)
        );
    },

    getName(id) {
        if (!id) return '';
        if (typeof id === 'string' && isNaN(id)) return id;
        let found = this.items.find(i => i.id == id);
        return found ? found.name : id;
    }
}" class="space-y-2">
    @if($label)
        <label class="flex items-center gap-1 text-xs font-bold text-zinc-700 uppercase tracking-wider">
            {{ $label }}
            @if(isset($label_extra))
                {{ $label_extra }}
            @endif
        </label>
    @endif

    <div class="relative">
        <div @class([
            'flex flex-wrap gap-2 p-2 min-h-[44px] rounded-2xl bg-zinc-50 border focus-within:ring-2 transition',
            'border-zinc-200 focus-within:ring-zinc-900' => !$error,
            'border-red-500 focus-within:ring-red-500' => $error
        ])>
            {{-- Múltiplo --}}
            @if($multiple)
                <template x-for="(id, index) in selected" :key="index">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-900 text-white text-[10px] font-bold uppercase tracking-widest">
                        <span x-text="getName(id)"></span>
                        <button type="button" @click="remove(index)" class="hover:text-zinc-300 transition">
                            <x-lucide-x class="h-3 w-3" />
                        </button>
                    </span>
                </template>
            @elseif(!empty($attributes->wire('model')) && $attributes->wire('model')->value())
                <template x-if="selected">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-900 text-white text-[10px] font-bold uppercase tracking-widest">
                        <span x-text="getName(selected)"></span>
                        <button type="button" @click="remove()" class="hover:text-zinc-300 transition">
                            <x-lucide-x class="h-3 w-3" />
                        </button>
                    </span>
                </template>
            @endif
            
            <input
                type="text"
                x-model="text"
                x-show="{{ $multiple ? 'true' : '!selected' }}"
                @keydown.enter.prevent="if(text.length > 0) select(text)"
                @keydown.escape="open = false"
                @focus="open = true"
                placeholder="{{ $placeholder }}"
                aria-label="{{ $label ?? $placeholder }}"
                class="flex-1 bg-transparent border-none p-1 text-xs focus:ring-0 min-w-[120px]"
            />
        </div>

        {{-- Sugestões --}}
        <div x-show="open && (suggestions.length > 0 || text.length > 0)" 
             @click.away="open = false"
             class="absolute z-50 w-full mt-2 bg-white border border-zinc-200 rounded-2xl shadow-xl overflow-hidden max-h-60 overflow-y-auto"
             x-transition>
            
            <template x-for="suggestion in suggestions" :key="suggestion.id">
                <button type="button" 
                        @click="select(suggestion)"
                        class="w-full text-left px-4 py-3 text-xs font-medium hover:bg-zinc-50 transition border-b border-zinc-100 last:border-none">
                    <span x-text="suggestion.name"></span>
                </button>
            </template>

            <template x-if="text.length > 0 && !items.some(i => i.name.toLowerCase() === text.toLowerCase())">
                <button type="button" 
                        @click="select(text)"
                        class="w-full text-left px-4 py-3 text-xs font-bold text-indigo-600 hover:bg-zinc-50 transition italic">
                    {{ $createMessage }} "<span x-text="text"></span>"
                </button>
            </template>
        </div>
    </div>

    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
