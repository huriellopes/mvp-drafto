@props([
    'variant' => 'warning',
    'title' => null,
])

@php
    $styles = [
        'warning' => [
            'container' => 'border-amber-200 bg-amber-50',
            'icon_bg' => 'bg-amber-100 text-amber-600',
            'title' => 'text-amber-900',
            'text' => 'text-amber-700',
            'icon' => 'alert-triangle'
        ],
        'danger' => [
            'container' => 'border-red-200 bg-red-50',
            'icon_bg' => 'bg-red-100 text-red-600',
            'title' => 'text-red-900',
            'text' => 'text-red-700',
            'icon' => 'shield-alert'
        ],
        'success' => [
            'container' => 'border-emerald-200 bg-emerald-50',
            'icon_bg' => 'bg-emerald-100 text-emerald-600',
            'title' => 'text-emerald-900',
            'text' => 'text-emerald-700',
            'icon' => 'check-circle'
        ],
    ][$variant];
@endphp

<div {{ $attributes->merge(['class' => "rounded-3xl border p-6 shadow-sm {$styles['container']}"]) }}>
    <div class="flex items-start gap-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $styles['icon_bg'] }}">
            <x-dynamic-component :component="'lucide-' . ($attributes->get('icon') ?? $styles['icon'])" class="h-5 w-5" />
        </div>

        <div class="flex-1">
            @if($title)
                <h3 class="text-sm font-semibold {{ $styles['title'] }}">
                    {{ $title }}
                </h3>
            @endif

            <div class="mt-1 text-sm {{ $styles['text'] }}">
                {{ $slot }}
            </div>

            @isset($actions)
                <div class="mt-4">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>
</div>
