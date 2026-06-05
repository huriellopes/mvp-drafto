<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\DTOs\AdminResetPasswordData;
use App\Models\User;
use App\Notifications\Users\AdminResetPasswordNotification;
use Illuminate\Support\Facades\Hash;

final class ResetUserPasswordAction
{
    public function exec(AdminResetPasswordData $data): bool
    {
        $user = User::findOrFail($data->userId);

        $user->update([
            'password' => Hash::make($data->password),
            'must_change_password' => true,
        ]);

        $user->notify(new AdminResetPasswordNotification($data->password));

        return true;
    }
}
