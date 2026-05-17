<?php

declare(strict_types=1);

namespace App\Actions\Saved;

use App\Models\User;

final class MoveToCollectionAction
{
    public function exec(User $user, int $postId, ?int $collectionId): void
    {
        /**
         * Sênior: Usamos updateExistingPivot para uma manipulação mais limpa e segura
         * da tabela intermediária, garantindo que o vínculo pertence ao usuário.
         */
        $user->savedPosts()->updateExistingPivot($postId, [
            'collection_id' => $collectionId,
        ]);
    }
}
