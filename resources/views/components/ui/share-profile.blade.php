@props(['username'])

<div x-data="{
    share() {
        const url = '{{ route('profile.show', $username) }}';
        if (navigator.share) {
            navigator.share({
                title: 'Confira o perfil de {{ $username }} na Drafto',
                url: url
            });
        } else {
            navigator.clipboard.writeText(url);
            alert('Link copiado para a área de transferência!');
        }
    }
}">
    <button
        @click="share"
        class="flex h-12 w-12 items-center justify-center rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 shadow-sm transition hover:bg-zinc-50"
        title="Compartilhar Perfil"
    >
        <x-lucide-share-2 class="h-5 w-5" />
    </button>
</div>
