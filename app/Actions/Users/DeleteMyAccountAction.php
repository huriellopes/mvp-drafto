<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

final class DeleteMyAccountAction
{
    /**
     * @throws Throwable
     */
    public function exec(User $user): bool
    {
        return DB::transaction(fn (): bool => (bool) $user->delete());
    }
}
