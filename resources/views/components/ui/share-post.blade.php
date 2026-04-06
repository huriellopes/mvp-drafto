@props(['post'])

<div x-data="{
    share() {
        const url = '{{ route('posts.show', $post->slug) }}';
        if (navigator.share) {
            navigator.share({
                title: '{{ $post->title }}',
                text: 'Confira este conteúdo na Drafto',
                url: url
            });
        } else {
            navigator.clipboard.writeText(url);
            alert('Link copiado!');
        }
    }
}" class="flex items-center gap-2">
    <button @click="share" class="flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:text-profile-primary transition shadow-sm">
        <x-lucide-share-2 class="h-5 w-5" />
    </button>
</div>
