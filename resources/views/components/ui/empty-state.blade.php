@props([
    'title',
    'description',
])

<div class="rounded-2xl border border-dashed border-zinc-300 p-8 text-center">
    <h3 class="text-base font-medium text-zinc-900">
        {{ $title }}
    </h3>

    <p class="mt-2 text-sm text-zinc-500">
        {{ $description }}
    </p>
</div>
