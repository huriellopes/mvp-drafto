<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use App\Notifications\SocialInteractionNotification;

final class ToggleFollowAction
{
    public function exec(User $follower, User $target): bool
    {
        if ($follower->id === $target->id) return false;

        $result = $follower->following()->toggle($target->id);
        $isAttached = count($result['attached']) > 0;

        if ($isAttached) {
            $target->notify(new SocialInteractionNotification('follow', $target, $follower));
        }

        return $isAttached;
    }
}
