@props([
    'label',
    'column' => null,
    'sort' => null,
    'direction' => null,
    'align' => 'left'
])

<th scope="col" {{ $attributes->merge(['class' => 'px-6 py-4 transition']) }}>
    @if($column)
        <button
            wire:click="sortBy('{{ $column }}')"
            @class([
                'flex items-center gap-2 group hover:text-zinc-900 transition-colors w-full',
                'justify-center' => $align === 'center',
                'justify-end' => $align === 'right',
                'text-zinc-900 font-bold' => $sort === $column,
                'text-zinc-500 font-semibold' => $sort !== $column,
            ])
        >
            <span>{{ $label }}</span>

            <div class="relative flex items-center">
                @if($sort === $column)
                    <x-lucide-chevron-up @class([
                        'h-3 w-3 transition-transform duration-200',
                        'rotate-180' => $direction === 'desc'
                    ]) />
                @else
                    <x-lucide-chevrons-up-down class="h-3 w-3 opacity-0 group-hover:opacity-50 transition-opacity" />
                @endif
            </div>
        </button>
    @else
        <span @class([
            'block text-xs font-semibold uppercase tracking-wider text-zinc-500',
            'text-center' => $align === 'center',
            'text-right' => $align === 'right',
        ])>
            {{ $label }}
        </span>
    @endif
</th>
