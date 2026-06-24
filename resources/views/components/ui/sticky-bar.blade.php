@props([
    // Alinhamento dos botões dentro da barra: 'center' (padrão), 'end', 'start' ou 'between'.
    'align' => 'center',
])

@php
    $justify = match ($align) {
        'start' => 'justify-start',
        'end' => 'justify-end',
        'between' => 'justify-between',
        default => 'justify-center',
    };
@endphp

{{--
    Barra de ações fixada ao fim da viewport (sticky), para salvar/publicar sem
    precisar rolar a página toda. Coloque os botões (type="submit"/wire:click) no slot.

    Ex.:
    <x-ui.sticky-bar align="end">
        <x-ui.button type="submit" loading="save">Salvar</x-ui.button>
    </x-ui.sticky-bar>
--}}
<div {{ $attributes->merge(['class' => 'sticky bottom-4 z-30']) }}>
    <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-zinc-200 bg-white/90 px-5 py-4 shadow-xl backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/90 {{ $justify }}">
        {{ $slot }}
    </div>
</div>
