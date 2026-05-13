<div>
    @if ($sent)
        <div class="flex items-center gap-2 text-sm font-medium text-emerald-600 transition" x-data x-init="setTimeout(() => $el.remove(), 5000)">
            <x-lucide-check-circle class="h-4 w-4" />
            <span>Link enviado com sucesso!</span>
        </div>
    @else
        <button
            wire:click="resend"
            wire:loading.attr="disabled"
            class="group inline-flex items-center gap-2 text-sm font-semibold text-amber-900 transition hover:text-amber-700"
        >
            <span class="underline decoration-amber-900/30 underline-offset-4 group-hover:decoration-amber-700" wire:loading.remove wire:target="resend">
                Reenviar e-mail de confirmação
            </span>

            <span wire:loading wire:target="resend" class="flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Enviando...
            </span>
        </button>

        @error('resend')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>
