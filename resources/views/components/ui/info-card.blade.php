@props([
    'title',
    'value',
])

<div class="rounded-2xl bg-zinc-50 p-4">
    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">
        {{ $title }}
    </p>

    <p class="mt-1 text-sm font-medium text-zinc-900">
        {{ $value }}
    </p>
</div>
