@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class('rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8') }}>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900">
                {{ $title }}
            </h2>

            @if ($description)
                <p class="mt-1 text-sm text-zinc-500">
                    {{ $description }}
                </p>
            @endif
        </div>

        @isset($actions)
            <div>
                {{ $actions }}
            </div>
        @endisset
    </div>

    {{ $slot }}
</div>
