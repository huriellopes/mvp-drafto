<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Override;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Register the Horizon gate.
     *
     * The dashboard (/horizon) exposes job payloads and lets operators retry
     * or delete jobs, so outside the local environment only super admins get in.
     */
    #[Override]
    protected function gate(): void
    {
        Gate::define('viewHorizon', fn (?User $user = null): bool => $user?->hasRole(RoleEnum::SUPER_ADMIN) ?? false);
    }
}
