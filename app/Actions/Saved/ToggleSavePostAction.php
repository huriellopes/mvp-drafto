<?php

declare(strict_types=1);

namespace App\Actions\Saved;

use App\Models\User;
use App\Models\Post;

final class ToggleSavePostAction
{
    public function exec(User $user, Post $post): bool
    {
        $result = $user->savedPosts()->toggle($post->id);

        return count($result['attached']) > 0;
    }
}
