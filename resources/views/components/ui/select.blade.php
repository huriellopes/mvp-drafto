@props([
    'label' => null,
    'error' => null
])

@php
    $id = $attributes->get('id') ?? $attributes->wire('model')->value() ?? Str::random(8);

    $baseClasses = 'block w-full !appearance-none bg-none rounded-2xl border border-zinc-300 bg-white pl-4 pr-10 py-3 text-sm text-zinc-900 outline-none transition
                    focus:border-zinc-900 focus:ring-0
                    dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-profile-primary
                    disabled:opacity-50 disabled:cursor-not-allowed';

    if($error) {
        $baseClasses .= ' border-red-500 dark:border-red-500';
    }
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $id }}" class="mb-2 flex items-center gap-1 text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ $label }}
            @if(isset($label_extra))
                {{ $label_extra }}
            @endif
        </label>
    @endif

    <div class="relative">
        <select
            {{ $attributes->merge([
                'id' => $id,
                'class' => $baseClasses
            ]) }}
            @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        >
            {{ $slot }}
        </select>

        {{-- Nosso ícone personalizado --}}
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-zinc-400 dark:text-zinc-600">
            <x-lucide-chevron-down class="h-4 w-4" />
        </div>
    </div>

        @if($error)
            <p id="{{ $id }}-error" class="mt-2 text-[10px] font-black uppercase tracking-widest text-red-600 animate-in fade-in slide-in-from-top-1">
                {{ $error }}
            </p>
        @endif
</div>
