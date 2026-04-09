<?php

declare(strict_types=1);

namespace App\Actions\Saved;

use App\Models\SavedPost;
use App\Models\User;

final class MoveToCollectionAction
{
    public function exec(User $user, int $postId, ?int $collectionId): void
    {
        $savedPost = SavedPost::query()
            ->where('user_id', $user->id)
            ->where('post_id', $postId)
            ->firstOrFail();

        $savedPost->update([
            'collection_id' => $collectionId,
        ]);
    }
}
