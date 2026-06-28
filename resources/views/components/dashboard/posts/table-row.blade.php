@use(App\Enums\PostStatusEnum)
@props(['post'])

<tr {{ $attributes->merge(['class' => 'group hover:bg-zinc-50/50 transition']) }}>
    <td class="px-6 py-4">
        <div class="flex flex-col">
            <span class="font-bold text-zinc-900 leading-tight">{{ $post->title }}</span>
            <span class="text-xs text-zinc-500 mt-1">{{ $post->category?->name }}</span>
        </div>
    </td>

    <td class="px-6 py-4 text-center">
        <span @class([
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider',
            'bg-green-100 text-green-700' => $post->status === PostStatusEnum::PUBLISHED,
            'bg-zinc-100 text-zinc-600' => $post->status === PostStatusEnum::ARCHIVED,
            'bg-indigo-100 text-indigo-700' => $post->status === PostStatusEnum::SCHEDULED,
        ])>
            {{ $post->status->label() }}
        </span>
    </td>

    <td class="px-6 py-4 text-center font-medium text-zinc-500">
        {{ number_format($post->views_count) }}
    </td>

    <td class="px-6 py-4 text-right">
        <div class="flex justify-end gap-1">
            <x-ui.tooltip text="Visualizar">
                <a href="{{ route('posts.show', $post->slug) }}" target="_blank"
                   class="block p-2 text-zinc-400 hover:text-zinc-900 transition rounded-xl hover:bg-zinc-100">
                    <x-lucide-eye class="h-4 w-4" />
                </a>
            </x-ui.tooltip>

            <x-ui.tooltip text="Editar">
                <a href="{{ route('dashboard.posts.edit', $post) }}"
                   class="block p-2 text-zinc-400 hover:text-zinc-900 transition rounded-xl hover:bg-zinc-100">
                    <x-lucide-pencil class="h-4 w-4" />
                </a>
            </x-ui.tooltip>

            <x-ui.tooltip text="Adicionar a coleção">
                <button wire:click="openCollections({{ $post->id }})"
                        class="p-2 text-zinc-400 hover:text-indigo-600 transition rounded-xl hover:bg-indigo-50">
                    <x-lucide-folder-plus class="h-4 w-4" />
                </button>
            </x-ui.tooltip>

            <x-ui.tooltip text="Excluir">
                <button wire:click="confirmDelete({{ $post->id }})"
                        class="p-2 text-zinc-400 hover:text-red-600 transition rounded-xl hover:bg-red-50">
                    <x-lucide-trash-2 class="h-4 w-4" />
                </button>
            </x-ui.tooltip>
        </div>
    </td>
</tr>
