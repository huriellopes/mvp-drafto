<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ReportStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Report $report): bool
    {
        return $report->reporter_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Report $report): bool
    {
        return false;
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN);
    }

    public function review(User $user, Report $report): bool
    {
        return $user->isAdmin() && $report->status !== ReportStatusEnum::ACTION_TAKEN;
    }
}
