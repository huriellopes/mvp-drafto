<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

final class PostObserver
{
    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        $this->clearCache($post);
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        $this->clearCache($post);
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        $this->clearCache($post);
    }

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        $this->clearCache($post);
    }

    /**
     * Sênior: Centraliza a limpeza de cache para evitar duplicação.
     */
    private function clearCache(Post $post): void
    {
        Cache::forget("post_show_{$post->slug}");

        // Se o slug mudou, limpa o antigo também (necessário se o slug for editável)
        if ($post->wasChanged('slug')) {
            Cache::forget("post_show_{$post->getOriginal('slug')}");
        }
    }
}
