@props([
    'title',
    'value',
    'description' => null,
])

<div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6">
    <p class="text-sm font-medium text-zinc-500">
        {{ $title }}
    </p>

    <p class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900 sm:text-4xl">
        {{ $value }}
    </p>

    @if ($description)
        <p class="mt-2 text-sm leading-6 text-zinc-600">
            {{ $description }}
        </p>
    @endif
</div>
