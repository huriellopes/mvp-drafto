@props([
    'label' => null,
    'error' => null,
    'rows' => 4
])

@php
    $id = $attributes->get('id') ?? $attributes->wire('model')->value() ?? Str::random(8);

    $baseClasses = 'block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition
                    focus:border-zinc-900 focus:ring-0 placeholder:text-zinc-400
                    dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-profile-primary
                    disabled:cursor-not-allowed disabled:opacity-50';

    if($error) {
        $baseClasses .= ' border-red-500 dark:border-red-500';
    }
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $id }}" class="mb-2 flex items-center gap-1 text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400 italic">
            {{ $label }}
            @if(isset($label_extra))
                {{ $label_extra }}
            @endif
        </label>
    @endif

    <div class="relative">
        <textarea
            {{ $attributes->merge([
                'id' => $id,
                'rows' => $rows,
                'class' => $baseClasses
            ]) }}
        ></textarea>
    </div>

        @if($error)
            <p class="mt-2 text-[10px] font-black uppercase tracking-widest text-red-600 animate-in fade-in slide-in-from-top-1">
                {{ $error }}
            </p>
        @endif
</div>
