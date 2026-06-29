<?php

declare(strict_types=1);

namespace App\Actions\PostCollections;

use App\Models\Post;

final class SyncPostCollectionsAction
{
    /**
     * Sincroniza as coleções de um post (usado pelo editor).
     *
     * Segurança: só vincula a coleções que pertencem ao autor do post,
     * descartando IDs forjados que não sejam do dono.
     *
     * @param  array<int, int|string>  $collectionIds
     */
    public function exec(Post $post, array $collectionIds): void
    {
        $ownedIds = $post->user
            ->postCollections()
            ->whereIn('id', array_map(intval(...), $collectionIds))
            ->pluck('id')
            ->all();

        $post->collections()->sync($ownedIds);
    }
}
