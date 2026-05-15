<?php

declare(strict_types=1);

namespace App\Actions\Support;

use App\DTOs\SupportData;
use App\Enums\SupportStatusEnum;
use App\Models\Support;
use App\Models\User;
use App\Notifications\SupportResponseNotification;

final class UpdateSupportAction
{
    public function exec(User $admin, Support $support, SupportData $data): Support
    {
        $support->update([
            'status' => SupportStatusEnum::from($data->status),
            'admin_response' => $data->admin_response,
            'responded_at' => $data->admin_response ? now() : $support->responded_at,
            'responded_by' => $admin->id,
        ]);

        if ($data->admin_response) {
            $support->user->notify(new SupportResponseNotification($support));
        }

        return $support;
    }
}
