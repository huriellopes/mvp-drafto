<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlanEnum;
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
use Illuminate\Database\Eloquent\Builder;
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
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'plan_id',
    'status',
    'is_lifetime',
    'trial_ends_at',
    'trial_notification_sent_at',
    'ip_address',
    'last_login_at',
    'email_verified_at',
    'banned_until',
    'ban_reason',
])]
#[Hidden([
    'password',
    'remember_token',
    'two_factor_secret',
    'two_factor_recovery_codes',
])]
class User extends Authenticatable implements Auditable, MustVerifyEmail, Sitemapable
{
    use Billable;

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPlanLimits, KeepsDeletedModels, Notifiable, \OwenIt\Auditing\Auditable;

    protected array $auditExclude = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'ip_address',
        'last_login_at',
    ];

    /**
     * Cache em tempo de execução para os módulos disponíveis do usuário.
     */
    protected ?\Illuminate\Database\Eloquent\Collection $loadedModules = null;

    public function toSitemapTag(): Url|string|array
    {
        if (!$this->profile || !$this->isActive()) {
            return [];
        }

        return Url::create(route('profile.show', $this->profile->username))
            ->setLastModificationDate($this->updated_at)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.7);
    }

    /**
     * Determine if the user has two factor authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) &&
               !is_null($this->two_factor_confirmed_at);
    }

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

    public function scopeWithFollowStatus(Builder $query): Builder
    {
        return $query->when(auth()->check(), function ($q) {
            $q->withExists(['followers as is_followed_by_auth_user' => function ($q) {
                $q->where('follower_id', auth()->id());
            }]);
        });
    }

    public function isVerified(): bool
    {
        // 1. Super Admin sempre verificado
        if ($this->isAdmin()) {
            return true;
        }

        // 2. Verificação Manual (campo no profile)
        if ($this->profile?->is_verified) {
            return true;
        }

        // 3. Plano Pro Ativo (Exclui Trial)
        // Sênior: Usamos o slug do plano carregado para evitar queries extras
        return ($this->plan?->slug === PlanEnum::PRO->value) && !$this->onTrial();
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
        if ($this->hasRole(RoleEnum::SUPER_ADMIN) || $this->isAdmin() || $this->is_lifetime || $this->onTrial()) {
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

    public function isModuleAvailable(string $slug): bool
    {
        if ($this->hasRole(RoleEnum::SUPER_ADMIN)) {
            return true;
        }

        // Sênior: Usamos cache em memória para evitar re-processamento da relação
        if ($this->loadedModules === null) {
            $this->loadedModules = $this->modules;
        }

        $module = $this->loadedModules->firstWhere('slug', $slug);

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
            'trial_notification_sent_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'banned_until' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:json',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
