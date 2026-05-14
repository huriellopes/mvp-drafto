<div class="space-y-6">
    {{-- Card do Link Público --}}
    <div class="rounded-3xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-6 shadow-sm ring-1 ring-zinc-100 dark:ring-white/5 transition-all hover:shadow-md">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                    <x-lucide-link class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                    {{ __('dashboard.profile_status.public_profile') }}
                    <x-ui.badge 
                        :label="$this->profileStatus->label()" 
                        :color="$this->profileStatus === App\Enums\ProfileVisibilityEnum::PUBLIC ? 'green' : 'orange'" 
                    />
                </h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('dashboard.profile_status.share_subtitle') }}</p>
            </div>

            <div x-data="{ 
                copyText: @js($this->profileUrl),
                copied: false,
                copy() {
                    if (this.copyText === '#') return;
                    navigator.clipboard.writeText(this.copyText);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
            }" class="flex items-center gap-2">
                <div class="flex h-10 items-center rounded-xl bg-zinc-50 dark:bg-zinc-950 px-3 border border-zinc-100 dark:border-zinc-800 text-xs font-medium text-zinc-600 dark:text-zinc-400 max-w-[220px] truncate">
                    {{ $this->profileUrl }}
                </div>
                
                <button 
                    @click="copy()"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl transition-all"
                    :class="copied ? 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400' : 'bg-zinc-900 text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200'"
                >
                    <template x-if="!copied">
                        <x-lucide-copy class="h-4 w-4" />
                    </template>
                    <template x-if="copied">
                        <x-lucide-check class="h-4 w-4" />
                    </template>
                </button>

                <a href="{{ $this->profileUrl }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                    <x-lucide-external-link class="h-4 w-4" />
                </a>
            </div>
        </div>
    </div>

    {{-- Alerta de Perfil Incompleto --}}
    @if(!$this->isComplete)
        <div class="rounded-3xl border border-orange-100 dark:border-orange-500/20 bg-orange-50/50 dark:bg-orange-500/5 p-6 shadow-sm ring-1 ring-orange-200/50 dark:ring-orange-500/10 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="flex flex-col gap-6 md:flex-row md:items-center">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400">
                    <x-lucide-award class="h-6 w-6" />
                </div>
                
                <div class="flex-1 space-y-4">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-black uppercase tracking-tighter text-orange-900 dark:text-orange-200">{{ __('dashboard.profile_status.incomplete_title') }}</h4>
                            <span class="text-xs font-black text-orange-600 dark:text-orange-400">{{ $this->completionPercentage }}%</span>
                        </div>
                        
                        {{-- Progress Bar --}}
                        <div class="h-1.5 w-full bg-orange-200/50 dark:bg-orange-500/10 rounded-full overflow-hidden">
                            <div class="h-full bg-orange-500 transition-all duration-1000 ease-out" style="width: {{ $this->completionPercentage }}%"></div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            @if(count(auth()->user()->profile->getMissingFields()) > 0)
                                <p class="text-[10px] font-bold uppercase tracking-widest text-orange-700/80 dark:text-orange-400/80">
                                    Campos Obrigatórios: <span class="text-orange-900 dark:text-orange-200">{{ implode(', ', auth()->user()->profile->getMissingFields()) }}</span>
                                </p>
                            @else
                                <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-700/80 dark:text-emerald-400/80">
                                    <span class="flex items-center gap-1"><x-lucide-check-circle-2 class="h-3 w-3" /> Essencial completo!</span>
                                    <span class="text-zinc-500 font-medium lowercase tracking-normal">Recomendamos preencher: {{ implode(', ', array_diff($this->missingFields, auth()->user()->profile->getMissingFields())) }}</span>
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('dashboard.profile') }}" class="inline-flex h-9 items-center justify-center rounded-xl bg-orange-600 px-5 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-orange-600/20 hover:bg-orange-700 transition active:scale-95">
                                {{ count(auth()->user()->profile->getMissingFields()) > 0 ? 'Resolver agora' : 'Melhorar Perfil' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
