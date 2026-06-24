<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\Modules\GenerateShortLinkAction;
use App\Enums\ModuleEnum;
use App\Enums\PostStatusEnum;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

#[Fillable([
    'name',
    'email',
    'password',
    'must_change_password',
    'role',
    'status',
    'ip_address',
    'last_login_at',
    'wants_reengagement_emails',
    'wants_product_updates',
    'reengagement_sent_at',
    'reengagement_stage',
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
    /** @use HasFactory<UserFactory> */
    use HasFactory, KeepsDeletedModels, Notifiable, \OwenIt\Auditing\Auditable;

    /**
     * Flag para pular o envio do e-mail de verificação padrão (usado no WelcomeNotification).
     */
    public bool $skipVerificationEmail = false;

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

    /** @return HasOne<Profile, $this> */
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

    public function shortLinks(): MorphMany
    {
        return $this->morphMany(ShortLink::class, 'shortable');
    }

    /**
     * Sênior: Retorna a URL de compartilhamento do perfil, encurtada se o módulo estiver ativo.
     */
    public function getShareUrl(): string
    {
        return app(GenerateShortLinkAction::class)->exec(
            user: auth()->user() ?? $this,
            shortable: $this,
        );
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
        return (bool) $this->profile?->is_verified;
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

    /**
     * Data da última atividade considerada para inatividade: o mais recente
     * entre o último login, a última escrita (post) e a criação da conta.
     * Usa `posts_max_created_at` se já vier carregado via subquery (evita N+1).
     */
    public function lastActivityAt(): Carbon
    {
        $lastPostAt = $this->getAttribute('posts_max_created_at')
            ?? $this->posts()->max('created_at');

        $dates = array_filter([
            $this->last_login_at,
            $lastPostAt ? Carbon::parse($lastPostAt) : null,
            $this->created_at,
        ]);

        return empty($dates) ? now() : Carbon::parse(max($dates));
    }

    /**
     * Quantos dias o usuário está inativo (sem logar e sem escrever).
     */
    public function inactiveDays(): int
    {
        return (int) $this->lastActivityAt()->diffInDays(now());
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()
            ->where('followed_id', $user->id)
            ->exists();
    }

    public function hasVerificationExpired(): bool
    {
        if ($this->hasVerifiedEmail()) {
            return false;
        }

        return $this->created_at->addDays(15)->isPast();
    }

    public function daysLeftToVerify(): int
    {
        return (int) max(0, now()->diffInDays($this->created_at->addDays(15), false));
    }

    public function sendEmailVerificationNotification(): void
    {
        if ($this->skipVerificationEmail) {
            return;
        }

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

    public function isModuleAvailable(string|ModuleEnum $slug): bool
    {
        if ($slug instanceof ModuleEnum) {
            $slug = $slug->value;
        }

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

    /**
     * Sênior: Retorna uma configuração específica de um módulo para o usuário.
     */
    public function getModuleSetting(string|ModuleEnum $module, string $key, mixed $default = null): mixed
    {
        if ($module instanceof ModuleEnum) {
            $module = $module->value;
        }

        if ($this->loadedModules === null) {
            $this->loadedModules = $this->modules;
        }

        $userModule = $this->loadedModules->firstWhere('slug', $module);

        if (!$userModule) {
            return $default;
        }

        $settings = is_string($userModule->pivot->settings)
            ? json_decode($userModule->pivot->settings, true)
            : $userModule->pivot->settings;

        return $settings[$key] ?? $default;
    }

    /**
     * Sênior: Como a plataforma é gratuita, o slug do plano é sempre 'free'.
     */
    public function getPlanSlug(): string
    {
        return 'free';
    }

    /**
     * Sênior: Nome amigável do plano (sempre Gratuito agora).
     */
    public function getPlanName(): string
    {
        return 'Gratuito';
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
            'wants_reengagement_emails' => 'boolean',
            'wants_product_updates' => 'boolean',
            'reengagement_sent_at' => 'datetime',
            'reengagement_stage' => 'integer',
            'banned_until' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:json',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
