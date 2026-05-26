@use(App\Enums\PostStatusEnum)
<div>
    {{ Breadcrumbs::render('dashboard.posts.index') }}

    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white leading-tight">{{ __('dashboard.posts.index.title') }}</h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.posts.index.subtitle') }}</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
            {{-- Busca --}}
            <div class="min-w-[280px]">
                <x-ui.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('dashboard.posts.index.search_placeholder') }}"
                    class="!py-2.5"
                >
                    <x-slot:iconLeft>
                        <x-lucide-search class="h-4 w-4 text-zinc-400" />
                    </x-slot:iconLeft>
                </x-ui.input>
            </div>

            <div class="min-w-[180px]">
                <x-ui.select wire:model.live="status" class="!py-2.5">
                    <option value="">{{ __('dashboard.posts.index.all_status') }}</option>
                    <option value="{{ PostStatusEnum::PUBLISHED->value }}">{{ __('dashboard.posts.index.published') }}</option>
                    <option value="{{ PostStatusEnum::SCHEDULED->value }}">{{ PostStatusEnum::SCHEDULED->label() }}</option>
                    <option value="{{ PostStatusEnum::ARCHIVED->value }}">{{ __('dashboard.posts.index.archived') }}</option>
                </x-ui.select>
            </div>
        </div>
    </div>

    <x-ui.table>
        <x-slot:header>
            <x-ui.table.th
                label="{{ __('dashboard.posts.index.table.content') }}"
                column="title"
                :sort="$sort"
                :direction="$direction"
            />

            <x-ui.table.th label="{{ __('dashboard.posts.index.table.status') }}" align="center" />

            <x-ui.table.th
                label="{{ __('dashboard.posts.index.table.views') }}"
                column="views_count"
                align="center"
                :sort="$sort"
                :direction="$direction"
            />

            <x-ui.table.th label="{{ __('dashboard.posts.index.table.actions') }}" align="right" />
        </x-slot:header>

        @forelse($this->posts as $post)
            <x-dashboard.posts.table-row :post="$post" wire:key="post-item-{{ $post->id }}" />
        @empty
            <tr>
                <td colspan="4" class="px-6 py-12">
                    <x-ui.empty-state
                        title="{{ __('dashboard.posts.index.empty_state') }}"
                        description="{{ $search ? __('dashboard.posts.index.empty_search') : __('dashboard.posts.index.empty_description') }}"
                    />
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            {{ $this->posts->links() }}
        </x-slot:footer>
    </x-ui.table>

    <x-ui.confirm-modal
        name="confirm-post-deletion"
        title="{{ __('dashboard.posts.index.delete_modal.title') }}"
        content="{{ __('dashboard.posts.index.delete_modal.content') }}"
        buttonText="{{ __('dashboard.posts.index.delete_modal.confirm') }}"
        variant="danger"
        action="deletePost"
    />
</div>
