@props(['label' => null, 'description' => null, 'error' => null])

@php
    $id = $attributes->get('id') ?? ($attributes->wire('model') ? $attributes->wire('model')->value() : 'checkbox-' . Str::random(8));
@endphp

<div class="relative flex items-start gap-3">
    <div class="flex h-6 items-center">
        <input
            {{ $attributes->merge([
                'id' => $id,
                'type' => 'checkbox',
                'class' => 'h-5 w-5 cursor-pointer rounded border-zinc-300 text-profile-primary focus:ring-profile-primary/20 transition duration-200'
            ]) }}
        >
    </div>
    <div class="text-sm leading-6">
        @if($label)
            <label for="{{ $id }}" class="font-bold text-zinc-900 dark:text-white cursor-pointer select-none">
                {{ $label }}
            </label>
        @endif

        @if($description)
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                {{ $description }}
            </p>
        @endif

        @if($error)
            <p class="mt-1 text-xs text-red-600 font-medium">{{ $error }}</p>
        @endif
    </div>
</div>
