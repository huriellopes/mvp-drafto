<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\PostStatusEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PostVisibilityEnum;
use Spatie\LaravelData\Data;

class SavePostData extends Data
{
    public function __construct(
        public string $title,
        public string $slug,
        public int|string $category_id,
        public string $content,
        public ?string $excerpt = null,
        public array $tags = [],
        public PostTypeEnum $type = PostTypeEnum::POST,
        public PostVisibilityEnum $visibility = PostVisibilityEnum::PUBLIC,
        public PostStatusEnum $status = PostStatusEnum::DRAFT,
        public bool $comments_enabled = true,
        public bool $seo_enabled = true,
        public ?string $seo_title = null,
        public ?string $seo_description = null,
        public ?string $cover_image_path = null,
    ) {}
}
