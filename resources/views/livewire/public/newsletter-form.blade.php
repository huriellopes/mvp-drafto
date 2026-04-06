<div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm text-left">
    <h4 class="text-lg font-bold mb-2">Gostou do conteúdo?</h4>
    <p class="text-gray-800 dark:text-white/80 text-sm mb-6">Receba mais artigos interessantes direto no seu e-mail.</p>

    <form wire:submit.prevent="subscribe" class="space-y-4">
        <input type="email" wire:model="email" placeholder="seu@email.com"
               class="w-full px-4 py-3 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-4 text-sm outline-none focus:border-profile-primary transition dark:text-white">

        <x-ui.button loading="subscribe"
                     class="!bg-white !text-black hover:!bg-zinc-100 shadow-lg">
            Assinar Newsletter
        </x-ui.button>
    </form>
</div>
