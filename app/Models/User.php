<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\Modules\GenerateShortLinkAction;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Concerns\HasModules;
use App\Models\Concerns\HasRelationships;
use App\Models\Concerns\HasTwoFactorAuthentication;
use App\Models\Concerns\InteractsWithFollowers;
use App\Models\Concerns\ManagesEmailVerification;
use App\Models\Concerns\TracksActivity;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Date;
use Override;
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

    use HasModules;
    use HasRelationships;
    use HasTwoFactorAuthentication;
    use InteractsWithFollowers;
    use ManagesEmailVerification;
    use TracksActivity;

    protected array $auditExclude = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'ip_address',
        'last_login_at',
    ];

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
     * Sênior: Retorna a URL de compartilhamento do perfil, encurtada se o módulo estiver ativo.
     */
    public function getShareUrl(): string
    {
        return resolve(GenerateShortLinkAction::class)->exec(
            user: auth()->user() ?? $this,
            shortable: $this,
        );
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

    public function greeting(): string
    {
        $hour = Date::now()->hour;

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

    #[Override]
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
