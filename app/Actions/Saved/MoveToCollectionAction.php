<?php

declare(strict_types=1);

namespace App\Actions\Saved;

use App\Models\Collection;
use App\Models\User;

final class MoveToCollectionAction
{
    public function exec(User $user, int $postId, ?int $collectionId): void
    {
        // Segurança: garante que a coleção de destino pertence ao usuário —
        // sem isto, um ID de coleção de outro usuário (adivinhado/trocado
        // no request) criava um vínculo cross-user inconsistente. `null`
        // (mover para a lista geral) é sempre permitido.
        if ($collectionId !== null
            && !Collection::query()->whereKey($collectionId)->where('user_id', $user->id)->exists()) {
            return;
        }

        /**
         * Sênior: Usamos updateExistingPivot para uma manipulação mais limpa e segura
         * da tabela intermediária, garantindo que o vínculo pertence ao usuário.
         */
        $user->savedPosts()->updateExistingPivot($postId, [
            'collection_id' => $collectionId,
        ]);
    }
}
