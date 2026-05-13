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
        public ?string $button_style = 'rounded-md',
        public ?string $card_style = 'bordered',
        public ?string $layout_type = 'default',
        public ?string $font_family = 'sans',
        public ?string $secondary_color = null,
        public ?string $text_color = null,
        public ?string $background_color = null,
        public bool $show_badges = true,
        public bool $show_subscriber_count = true,
        public bool $show_view_count = false,
        public ?string $seo_title = null,
        public ?string $seo_description = null,
        public ?UploadedFile $avatar = null,
        public ?UploadedFile $cover = null,
    ) {
        $this->primary_color = str_starts_with($primary_color, '#') ? $primary_color : "#{$primary_color}";
        $this->accent_color = str_starts_with($accent_color, '#') ? $accent_color : "#{$accent_color}";

        if ($this->secondary_color) {
            $this->secondary_color = str_starts_with($this->secondary_color, '#') ? $this->secondary_color : "#{$this->secondary_color}";
        }
    }
}
