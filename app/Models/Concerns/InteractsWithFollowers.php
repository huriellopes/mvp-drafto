<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Relação de seguidores/seguindo e helpers de "follow".
 */
trait InteractsWithFollowers
{
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'followers',
            'follower_id',
            'followed_id',
        )->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'followers',
            'followed_id',
            'follower_id',
        )->withTimestamps();
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()
            ->where('followed_id', $user->id)
            ->exists();
    }

    public function scopeWithFollowStatus(Builder $query): Builder
    {
        return $query->when(auth()->check(), function ($q): void {
            $q->withExists(['followers as is_followed_by_auth_user' => function ($q): void {
                $q->where('follower_id', auth()->id());
            }]);
        });
    }
}
