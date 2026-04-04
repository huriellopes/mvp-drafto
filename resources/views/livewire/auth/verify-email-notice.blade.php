<div class="w-full max-w-md">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 border border-amber-100">
            <x-lucide-shield-alert class="h-7 w-7" />
        </div>
        <h1 class="text-3xl font-semibold text-zinc-900">Prazo expirado</h1>
        <p class="mt-2 text-sm text-zinc-600">
            Seus 7 dias de carência terminaram. Para continuar publicando na <strong>Drafto</strong>, você precisa confirmar seu endereço de e-mail.
        </p>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm text-center">
        <p class="mb-6 text-sm text-zinc-600">Não recebeu o link ou ele expirou?</p>

        <livewire:auth.resend-verification />

        <div class="mt-8 border-t border-zinc-100 pt-6">
            <livewire:auth.logout-button />
        </div>
    </div>
</div>
