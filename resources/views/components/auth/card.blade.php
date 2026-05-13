@props(['title', 'description', 'showLogo' => true])

<div class="w-full max-w-md">
    <div class="mb-8 text-center">
        @if($showLogo)
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-zinc-200 bg-white shadow-sm overflow-hidden p-2">
                <img src="{{ asset('images/favicon/android-chrome-192x192.png') }}" class="h-10 w-auto" alt="Drafto Logo">
            </div>
        @endif

        <h1 class="text-3xl font-semibold tracking-tight text-zinc-900">
            {{ $title }}
        </h1>

        <p class="mt-2 text-sm leading-6 text-zinc-600">
            {{ $description }}
        </p>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
        {{ $slot }}
    </div>

    @isset($footer)
        {{ $footer }}
    @else
        <p class="mt-6 text-center text-sm text-zinc-500">
            Escreva com clareza. Publique com identidade.
        </p>
    @endisset
</div>
