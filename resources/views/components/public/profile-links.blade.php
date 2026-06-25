@props(['profile'])

@php
    // Apenas links marcados como públicos aparecem na página pública.
    $links = $profile->links->filter->isPublic();
@endphp

@if($links->isNotEmpty())
    <div class="mt-5 flex flex-wrap items-center gap-3">
        @foreach($links as $link)
            @php $label = $link->label(); @endphp
            {{-- Chip branco fixo + ícone na cor da marca (não segue as cores da página). --}}
            <div class="group relative">
                <a
                    href="{{ $link->url }}"
                    target="_blank"
                    rel="noopener noreferrer nofollow"
                    aria-label="{{ $label }}"
                    title="{{ $label }}"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-zinc-900/5 transition hover:scale-110 active:scale-95"
                    style="color: {{ $link->brandColor() }}"
                >
                    <x-dynamic-component :component="'lucide-' . $link->icon()" class="h-5 w-5" />
                </a>

                {{-- Tooltip ao passar o mouse --}}
                <span
                    role="tooltip"
                    class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-zinc-900 px-2.5 py-1 text-[10px] font-bold text-white opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100"
                >
                    {{ $label }}
                </span>
            </div>
        @endforeach
    </div>
@endif
