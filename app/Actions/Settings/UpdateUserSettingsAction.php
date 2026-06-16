<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\DTOs\UpdateUserSettingsData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class UpdateUserSettingsAction
{
    public function exec(User $user, UpdateUserSettingsData $data): void
    {
        $updateData = [
            'name' => $data->name,
            'email' => $data->email,
            'wants_reengagement_emails' => $data->wants_reengagement_emails,
            'wants_product_updates' => $data->wants_product_updates,
        ];

        if ($user->email !== $data->email) {
            $updateData['email_verified_at'] = null;
            $user->sendEmailVerificationNotification();
        }

        if ($data->password) {
            $updateData['password'] = Hash::make($data->password);
        }

        $user->update($updateData);
    }
}
