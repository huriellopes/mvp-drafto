<?php

declare(strict_types=1);

namespace App\Actions\Engagement;

use App\Enums\RoleEnum;
use App\Enums\UpdateAudienceEnum;
use App\Enums\UserStatusEnum;
use App\Mail\ProductUpdateMail;
use App\Models\PlatformUpdate;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

final class SendProductUpdateAction
{
    /**
     * @return int total de destinatários enfileirados.
     */
    public function exec(PlatformUpdate $update): int
    {
        $count = 0;

        User::query()
            ->where('status', UserStatusEnum::ACTIVE)
            ->whereNotNull('email_verified_at')
            ->where('wants_product_updates', true)
            ->where(fn ($q) => $q->whereNull('banned_until')->orWhere('banned_until', '<=', now()))
            ->when(
                $update->audience === UpdateAudienceEnum::WRITERS,
                fn (Builder $q) => $q->where('role', RoleEnum::WRITER),
            )
            ->when(
                $update->audience === UpdateAudienceEnum::READERS,
                fn (Builder $q) => $q->where('role', RoleEnum::READER),
            )
            ->select(['id', 'name', 'email'])
            ->chunkById(200, function ($users) use ($update, &$count) {
                foreach ($users as $user) {
                    Mail::to($user->email)->send(new ProductUpdateMail($user, $update));
                    $count++;
                }
            });

        $update->update([
            'sent_at' => now(),
            'recipients_count' => $count,
        ]);

        return $count;
    }
}
