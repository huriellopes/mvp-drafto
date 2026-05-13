<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\UserStatusEnum;
use App\Models\User;

final class ToggleUserStatusAction
{
    public function exec(User $user, ?UserStatusEnum $targetStatus = null): bool
    {
        $newStatus = $targetStatus ?? (
            $user->status === UserStatusEnum::ACTIVE
            ? UserStatusEnum::BLOCKED
            : UserStatusEnum::ACTIVE
        );

        return $user->forceFill(['status' => $newStatus])->save();
    }
}
