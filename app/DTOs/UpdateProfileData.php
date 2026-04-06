<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ProfileVisibilityEnum;
use App\Enums\ThemePlatformEnum;
use Illuminate\Http\UploadedFile;

readonly class UpdateProfileData
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
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            username: $data['username'],
            email: $data['email'] ?? null,
            bio: $data['bio'] ?? null,
            location: $data['location'] ?? null,
            website_url: $data['website_url'] ?? null,
            primary_color: str_starts_with($data['primary_color'], '#') ? $data['primary_color'] : "#{$data['primary_color']}",
            accent_color: str_starts_with($data['accent_color'], '#') ? $data['accent_color'] : "#{$data['accent_color']}",
            theme_mode: $data['theme_mode'] instanceof ThemePlatformEnum
                ? $data['theme_mode']
                : ThemePlatformEnum::from($data['theme_mode']),
            visibility: $data['visibility'] instanceof ProfileVisibilityEnum
                ? $data['visibility']
                : ProfileVisibilityEnum::from($data['visibility']),
            show_email_publicly: (bool) ($data['show_email_publicly'] ?? false),
            is_searchable: (bool) ($data['is_searchable'] ?? true),
            avatar: $data['avatar'] ?? null,
            cover: $data['cover'] ?? null,
        );
    }
}
