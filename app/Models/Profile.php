<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProfileVisibilityEnum;
use App\Enums\ThemePlatformEnum;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
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
])]
class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    use HasSEO;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getColors(): object
    {
        return (object) [
            'primary' => $this->primary_color ?? '#18181b',
            'accent' => $this->accent_color ?? '#3f3f46',
        ];
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
        ];
    }
}
