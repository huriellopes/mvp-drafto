<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ProfileVisibilityEnum;
use App\Enums\ThemePlatformEnum;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class UpdateProfileData extends Data
{
    public function __construct(
        public ?string $name,
        public string $username,
        public ?string $email,
        public ?string $bio,
        public ?string $location,
        public ?string $website_url,
        public string $primary_color,
        public string $accent_color,
        public ThemePlatformEnum $theme_mode,
        public ProfileVisibilityEnum $visibility,
        public bool $show_email_publicly,
        public bool $is_searchable,
        public ?UploadedFile $avatar = null,
        public ?UploadedFile $cover = null,
    ) {
        $this->primary_color = str_starts_with($primary_color, '#') ? $primary_color : "#{$primary_color}";
        $this->accent_color = str_starts_with($accent_color, '#') ? $accent_color : "#{$accent_color}";
    }
}
