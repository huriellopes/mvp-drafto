<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ThemePlatformEnum;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'username',
    'bio',
    'avatar_path',
    'cover_path',
    'website_url',
    'location',
    'theme_mode',
    'primary_color',
    'accent_color',
    'show_email_publicly',
])]
class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'theme_mode' => ThemePlatformEnum::class,
            'show_email_publicly' => 'boolean',
        ];
    }
}
