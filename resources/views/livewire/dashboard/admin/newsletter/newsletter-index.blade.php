<div class="space-y-6">
    {{ Breadcrumbs::render('dashboard.newsletter.index') }}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white leading-tight">{{ __('dashboard.admin.newsletter.title') }}</h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.admin.newsletter.subtitle') }}</p>
    </div>

    {{-- Filtros e Ações --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between bg-white dark:bg-zinc-900 p-4 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center">
            <div class="w-full md:w-80">
                <x-ui.input
                    wire:model.live.debounce.300ms="filters.search"
                    placeholder="{{ __('dashboard.admin.newsletter.search_placeholder') }}"
                >
                    <x-slot:prefix>
                        <x-lucide-search class="h-4 w-4 text-zinc-400" />
                    </x-slot:prefix>
                </x-ui.input>
            </div>

            <div class="w-full md:w-70">
                <select
                    wire:model.live="filters.category_id"
                    class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-indigo-500 focus:ring-0 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                >
                    <option value="">{{ __('dashboard.admin.newsletter.all_categories') }}</option>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if($this->isFileReady)
                <div class="flex items-center gap-2 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 px-4 py-2 rounded-2xl animate-in fade-in slide-in-from-right-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Pronto!</span>
                    <a 
                        href="{{ route('dashboard.temporary-file.download', ['path' => $generatedPath]) }}" 
                        wire:click="clearGeneratedFile"
                        class="flex h-8 items-center gap-2 rounded-xl bg-emerald-600 px-3 text-[10px] font-bold text-white transition hover:bg-emerald-700 shadow-sm"
                    >
                        <x-lucide-download class="h-3 w-3" />
                        Baixar
                    </a>
                    <x-ui.tooltip text="Descartar arquivo">
                        <button wire:click="clearGeneratedFile" class="text-emerald-400 hover:text-emerald-600">
                            <x-lucide-x class="h-4 w-4" />
                        </button>
                    </x-ui.tooltip>
                </div>
            @elseif($generatedPath)
                <div wire:poll.1s class="flex items-center gap-3 px-4 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 animate-pulse">
                    <x-lucide-loader-2 class="h-4 w-4 animate-spin text-zinc-400" />
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500">Gerando...</span>
                </div>
            @else
                <x-ui.button
                    variant="dark"
                    x-on:click="$dispatch('open-modal', { name: 'manual-newsletter-modal' })"
                    title="{{ __('dashboard.admin.newsletter.manual_title') }}"
                    class="!w-auto px-4"
                >
                    <x-lucide-send class="h-4 w-4" />
                </x-ui.button>
                <x-ui.button wire:click="export" class="!w-auto px-6">
                    <x-lucide-download class="mr-2 h-4 w-4" />
                    {{ __('dashboard.admin.newsletter.export_excel') }}
                </x-ui.button>
            @endif
        </div>
    </div>

    {{-- Tabela --}}
    <x-ui.table>
        <x-slot:header>
            <x-ui.table.th label="{{ __('dashboard.admin.newsletter.table.email') }}" column="email" :sort="$filters->sort" :direction="$filters->direction" />
            <x-ui.table.th label="{{ __('dashboard.admin.newsletter.table.interest') }}" />
            <x-ui.table.th label="{{ __('dashboard.admin.newsletter.table.joined_at') }}" column="created_at" :sort="$filters->sort" :direction="$filters->direction" />
            <x-ui.table.th label="{{ __('dashboard.admin.newsletter.table.actions') }}" align="right" />
        </x-slot:header>

        @forelse($this->subscribers as $subscriber)
            <tr wire:key="{{ $subscriber->id }}" class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-500">
                            <x-lucide-mail class="h-4 w-4" />
                        </div>
                        <span class="font-bold text-zinc-900 dark:text-white">{{ $subscriber->email }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                    @if($subscriber->categories->isNotEmpty())
                        <div class="flex flex-wrap gap-1">
                            @foreach($subscriber->categories as $category)
                                <span class="inline-flex items-center rounded-full bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-xs text-zinc-400 italic">{{ __('dashboard.admin.newsletter.table.general') }}</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-zinc-500 text-xs">
                    {{ $subscriber->created_at->translatedFormat('d M, Y H:i') }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex justify-end gap-2">
                        <button
                            wire:click="confirmDeletion({{ $subscriber->id }})"
                            class="flex h-9 w-9 items-center justify-center rounded-xl text-zinc-400 hover:bg-red-50 hover:text-red-600 transition"
                        >
                            <x-lucide-trash-2 class="h-4 w-4" />
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-6 py-20 text-center">
                    <div class="flex flex-col items-center justify-center text-zinc-400">
                        <x-lucide-inbox class="h-12 w-12 mb-4" />
                        <p class="text-lg font-medium">{{ __('dashboard.admin.newsletter.empty_state') }}</p>
                    </div>
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            {{ $this->subscribers->links() }}
        </x-slot:footer>
    </x-ui.table>

    {{-- Modal de Confirmação --}}
    <x-ui.confirm-modal
        name="confirm-subscriber-deletion"
        title="{{ __('dashboard.admin.newsletter.delete_modal.title') }}"
        content="{{ __('dashboard.admin.newsletter.delete_modal.content') }}"
        buttonText="{{ __('dashboard.admin.newsletter.delete_modal.confirm') }}"
        variant="danger"
        action="delete"
    />

    <x-ui.modal name="manual-newsletter-modal" title="{{ __('dashboard.admin.newsletter.manual_modal.title') }}">
        <form wire:submit.prevent="sendManualNewsletter" class="space-y-6">
            <p class="text-sm text-zinc-500">
                {{ __('dashboard.admin.newsletter.manual_modal.description') }}
            </p>

            <x-ui.select 
                label="Filtrar por Categoria (Opcional)" 
                wire:model="manualCategoryId"
                :error="$errors->first('manualCategoryId')"
            >
                <option value="">Enviar para TODOS</option>
                @foreach($this->categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.textarea
                label="{{ __('dashboard.admin.newsletter.manual_modal.label') }}"
                wire:model="customMessage"
                placeholder="{{ __('dashboard.admin.newsletter.manual_modal.placeholder') }}"
                rows="5"
                :error="$errors->first('customMessage')"
            />

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', { name: 'manual-newsletter-modal' })" type="button" class="!w-auto px-8">
                    {{ __('dashboard.admin.newsletter.manual_modal.cancel') }}
                </x-ui.button>
                <x-ui.button type="submit" loading="sendManualNewsletter" class="!w-auto px-10">
                    {{ __('dashboard.admin.newsletter.manual_modal.confirm') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
