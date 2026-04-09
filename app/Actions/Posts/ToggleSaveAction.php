<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\Models\Post;
use App\Models\User;

final class ToggleSaveAction
{
    public function exec(User $user, Post $post): bool
    {
        $result = $user->savedPosts()->toggle($post->id);

        return count($result['attached']) > 0;
    }
}
