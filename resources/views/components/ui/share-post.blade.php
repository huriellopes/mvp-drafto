@props(['post'])

<div x-data="{
    async share() {
        const url = '{{ $post->getShareUrl() }}';

        if (navigator.share) {
            try {
                await navigator.share({
                    title: '{{ $post->title }}',
                    text: 'Confira este conteúdo incrível no Drafto:',
                    url: url
                });
            } catch (err) {
                console.log('Compartilhamento cancelado');
            }
        } else {
            try {
                await navigator.clipboard.writeText(url);
                // Usando o Toaster que já temos no projeto para um feedback elegante
                $dispatch('toaster:info', { message: 'Link copiado para a área de transferência!' });
            } catch (err) {
                console.error('Falha ao copiar', err);
            }
        }
    }
}">
    <x-ui.tooltip text="Compartilhar artigo">
        <button
            @click="share"
            type="button"
            aria-label="Compartilhar artigo"
            class="group flex h-11 w-11 items-center justify-center rounded-2xl border border-zinc-200 bg-white shadow-sm transition-all hover:border-profile-primary hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-profile-primary dark:hover:bg-zinc-800"
        >
            <x-lucide-share-2 class="h-5 w-5 text-zinc-600 transition-colors group-hover:text-profile-primary dark:text-zinc-400" aria-hidden="true" />
        </button>
    </x-ui.tooltip>
</div>
