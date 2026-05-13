@props(['availableTags' => [], 'label' => 'Tags'])

<div x-data="{
    open: false,
    text: '',
    tags: @entangle($attributes->wire('model')),
    available: {{ json_encode($availableTags->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values()) }},
    
    addTag(tag) {
        if (typeof tag === 'string') {
            tag = tag.trim();
            if (tag === '' || this.tags.includes(tag)) return;
        } else {
            if (this.tags.includes(tag.id)) return;
            tag = tag.id;
        }
        
        this.tags.push(tag);
        this.text = '';
        this.open = false;
    },
    
    removeTag(index) {
        this.tags.splice(index, 1);
    },
    
    get suggestions() {
        if (this.text === '') return [];
        return this.available.filter(t => 
            t.name.toLowerCase().includes(this.text.toLowerCase()) && 
            !this.tags.includes(t.id)
        );
    },

    getTagName(id) {
        if (typeof id === 'string') return id;
        let found = this.available.find(t => t.id == id);
        return found ? found.name : id;
    }
}" class="space-y-2">
    @if($label)
        <label class="text-xs font-bold text-zinc-700 uppercase tracking-wider">{{ $label }}</label>
    @endif

    <div class="relative">
        <div class="flex flex-wrap gap-2 p-2 min-h-[44px] rounded-2xl bg-zinc-50 border border-zinc-200 focus-within:ring-2 focus-within:ring-zinc-900 transition">
            <template x-for="(tag, index) in tags" :key="index">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-900 text-white text-[10px] font-bold uppercase tracking-widest">
                    <span x-text="getTagName(tag)"></span>
                    <button type="button" @click="removeTag(index)" class="hover:text-zinc-300 transition">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </span>
            </template>
            
            <input 
                type="text" 
                x-model="text" 
                @keydown.enter.prevent="addTag(text)"
                @keydown.escape="open = false"
                @focus="open = true"
                placeholder="Adicionar tag..."
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
                        @click="addTag(suggestion)"
                        class="w-full text-left px-4 py-3 text-xs font-medium hover:bg-zinc-50 transition border-b border-zinc-100 last:border-none">
                    <span x-text="suggestion.name"></span>
                </button>
            </template>

            <template x-if="text.length > 0 && !available.some(t => t.name.toLowerCase() === text.toLowerCase())">
                <button type="button" 
                        @click="addTag(text)"
                        class="w-full text-left px-4 py-3 text-xs font-bold text-indigo-600 hover:bg-zinc-50 transition italic">
                    Criar nova tag: "<span x-text="text"></span>"
                </button>
            </template>
        </div>
    </div>
    
    <p class="text-[10px] text-zinc-400 italic font-medium">
        Pressione Enter para adicionar uma nova tag.
    </p>
</div>
