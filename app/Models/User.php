<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostStatusEnum;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Concerns\HasPlanLimits;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Billable;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'status',
    'is_lifetime',
    'ip_address',
    'last_login_at',
    'email_verified_at',
    'banned_until',
    'ban_reason',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable implements MustVerifyEmail
{
    use Billable;

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPlanLimits, KeepsDeletedModels, Notifiable;

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function publishedPosts(): HasMany
    {
        return $this->posts()->where('status', PostStatusEnum::PUBLISHED);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

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

    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'post_likes',
            'user_id',
            'post_id',
        )->withTimestamps();
    }

    public function likedComments(): BelongsToMany
    {
        return $this->belongsToMany(
            Comment::class,
            'comment_likes',
            'user_id',
            'comment_id',
        )->withTimestamps();
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function savedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'saved_posts')
            ->using(SavedPost::class)
            ->withPivot('collection_id', 'id')
            ->withTimestamps();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reviewedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reviewed_by');
    }

    public function postViews(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    public function hasRole(RoleEnum $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->role === RoleEnum::SUPER_ADMIN;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatusEnum::ACTIVE;
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()
            ->where('followed_id', $user->id)
            ->exists();
    }

    /**
     * Checks if the user has an active subscription or is a lifetime member.
     */
    public function hasPremiumAccess(): bool
    {
        if ($this->isAdmin() || $this->is_lifetime) {
            return true;
        }

        return $this->subscribed('default') || $this->subscribed('pro');
    }

    public function hasVerificationExpired(): bool
    {
        if ($this->hasVerifiedEmail()) {
            return false;
        }

        return $this->created_at->addDays(7)->isPast();
    }

    public function daysLeftToVerify(): int
    {
        return (int) max(0, now()->diffInDays($this->created_at->addDays(7), false));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function greeting(): string
    {
        $hour = Carbon::now()->hour;

        return match (true) {
            $hour >= 5 && $hour < 12 => 'Bom dia',
            $hour >= 12 && $hour < 18 => 'Boa tarde',
            default => 'Boa noite',
        };
    }

    protected function displayName(): Attribute
    {
        return Attribute::get(function () {
            $rawName = match (true) {
                request()->routeIs('dashboard.index') => $this->name,
                default => $this->profile?->name ?: $this->name,
            };

            return format_display_name($rawName);
        });
    }

    protected function casts(): array
    {
        return [
            'role' => RoleEnum::class,
            'status' => UserStatusEnum::class,
            'is_lifetime' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'banned_until' => 'datetime',
        ];
    }
}
