<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProfileVisibilityEnum;
use App\Enums\ThemePlatformEnum;
use App\Traits\GeneratesUsername;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;

#[Fillable([
    'user_id',
    'name',
    'username',
    'email',
    'bio',
    'avatar_path',
    'cover_path',
    'website_url',
    'location',
    'theme_mode',
    'visibility',
    'primary_color',
    'accent_color',
    'show_email_publicly',
    'is_searchable',
    'is_verified',
    'profile_views',
])]
class Profile extends Model implements Auditable
{
    use GeneratesUsername;

    /** @use HasFactory<ProfileFactory> */
    use HasFactory, \OwenIt\Auditing\Auditable;

    use HasSEO;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(ProfileSetting::class)->withDefault([
            'button_style' => 'rounded-md',
            'card_style' => 'bordered',
            'layout_type' => 'default',
            'font_family' => 'sans',
        ]);
    }

    /** @return HasMany<ProfileLink, $this> */
    public function links(): HasMany
    {
        return $this->hasMany(ProfileLink::class)->orderBy('sort_order');
    }

    public function getColors(): object
    {
        return (object) [
            'primary' => $this->primary_color ?? '#18181b',
            'accent' => $this->accent_color ?? '#3f3f46',
        ];
    }

    /**
     * Get the completion percentage of the profile.
     */
    public function getCompletionPercentage(): int
    {
        $totalFields = 7;
        $missingCount = count($this->getRecommendedMissingFields());
        $completedCount = $totalFields - $missingCount;

        return (int) (($completedCount / $totalFields) * 100);
    }

    /**
     * Identify missing critical fields (Mandatory for release).
     */
    public function getMissingFields(): array
    {
        $missing = [];

        if (empty($this->name)) {
            $missing['name'] = 'Nome';
        }

        if (empty($this->username)) {
            $missing['username'] = 'Username (@)';
        }

        if (empty($this->email)) {
            $missing['email'] = 'E-mail';
        }

        return $missing;
    }

    /**
     * Get all missing fields including recommended ones.
     */
    public function getRecommendedMissingFields(): array
    {
        $missing = $this->getMissingFields();

        if (empty($this->bio)) {
            $missing['bio'] = 'Biografia';
        }

        if (empty($this->avatar_path)) {
            $missing['avatar_path'] = 'Foto de Perfil';
        }

        if (empty($this->cover_path)) {
            $missing['cover_path'] = 'Capa';
        }

        if (empty($this->location)) {
            $missing['location'] = 'Localização';
        }

        return $missing;
    }

    /**
     * Check if critical fields are completed.
     */
    public function isComplete(): bool
    {
        return empty($this->getMissingFields());
    }

    /**
     * Garante que todo perfil nasça com um username (gerado a partir do nome),
     * evitando UrlGenerationException em rotas que dependem do username.
     * O usuário pode atualizá-lo depois em Editar Perfil.
     */
    protected static function booted(): void
    {
        static::creating(function (Profile $profile): void {
            if (blank($profile->username)) {
                $base = $profile->name ?: ($profile->user?->name ?? 'usuario');
                $profile->username = $profile->generateUniqueUsername($base);
            }
        });
    }

    protected function handle(): Attribute
    {
        return Attribute::make(
            get: fn () => "@{$this->username}",
        );
    }

    protected function username(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::lower(Str::replace('@', '', $value)),
        );
    }

    protected function primaryColor(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ?? '#18181b', // Default Zinc-900
        );
    }

    protected function accentColor(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ?? '#3f3f46', // Default Zinc-700
        );
    }

    protected function casts(): array
    {
        return [
            'theme_mode' => ThemePlatformEnum::class,
            'visibility' => ProfileVisibilityEnum::class,
            'show_email_publicly' => 'boolean',
            'is_searchable' => 'boolean',
            'is_verified' => 'boolean',
            'profile_views' => 'integer',
        ];
    }
}
