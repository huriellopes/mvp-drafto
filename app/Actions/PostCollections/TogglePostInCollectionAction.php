<?php

declare(strict_types=1);

namespace App\Actions\PostCollections;

use App\Models\Post;
use App\Models\PostCollection;

final class TogglePostInCollectionAction
{
    /**
     * Adiciona ou remove um post de uma coleção (ação rápida nas listas e
     * na página dedicada). Retorna true se ficou anexado, false se removido.
     *
     * A posse (coleção e post pertencem ao mesmo usuário) deve ser garantida
     * pela camada que chama (policy/Livewire).
     */
    public function exec(PostCollection $collection, Post $post): bool
    {
        $result = $collection->posts()->toggle($post->id);

        return in_array($post->id, $result['attached'], true);
    }
}
