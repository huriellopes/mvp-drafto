<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\UserStatusEnum;
use App\Models\User;

final class ToggleUserStatusAction
{
    public function exec(User $user, ?UserStatusEnum $targetStatus = null): bool
    {
        $status = $targetStatus ?: (
            $user->status === UserStatusEnum::ACTIVE
                ? UserStatusEnum::SUSPENDED
                : UserStatusEnum::ACTIVE
        );

        return $user->forceFill(['status' => $status])->save();
    }
}
