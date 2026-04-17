<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModuleEnum;
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    'plan_id',
    'status',
    'is_lifetime',
    'trial_ends_at',
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

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
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

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)
            ->withPivot('is_enabled', 'settings')
            ->withTimestamps();
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
        if ($this->isAdmin() || $this->is_lifetime || $this->onTrial()) {
            return true;
        }

        return $this->subscribed();
    }

    /**
     * Checks if the user is currently on an active free trial.
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
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

    /**
     * Sênior: Cálculo de limite baseado no mês corrente.
     * Isso impede que o usuário delete posts antigos para publicar novos se o plano for mensal.
     */
    public function hasReachedPostLimit(): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        $limit = $this->getModuleSetting(ModuleEnum::MY_POSTS, 'max_monthly_posts');

        if ($limit === -1) {
            return false;
        }

        $publishedThisMonth = $this->posts()
            ->where('status', PostStatusEnum::PUBLISHED)
            ->whereMonth('published_at', now()->month)
            ->count();

        return $publishedThisMonth >= $limit;
    }

    public function isModuleAvailable(string $slug): bool
    {
        if ($this->hasRole(RoleEnum::SUPER_ADMIN)) {
            return true;
        }

        $module = $this->modules->firstWhere('slug', $slug);

        if (!$module) {
            return false;
        }

        return $module->is_enabled && (bool) $module->pivot->is_enabled;
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
            'trial_ends_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'banned_until' => 'datetime',
        ];
    }
}
