<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class SavePostDTO
{
    public function __construct(
        public string $title,
        public string $slug,
        public int $category_id,
        public string $content,
        public ?string $excerpt = null,
        public string $type = 'post',
        public string $visibility = 'public',
        public bool $comments_enabled = true,
        public ?string $cover_image_path = null,
        public int $reading_time = 1,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'slug' => $this->slug,
            'category_id' => $this->category_id,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'type' => $this->type,
            'visibility' => $this->visibility,
            'comments_enabled' => $this->comments_enabled,
            'cover_image_path' => $this->cover_image_path,
            'reading_time' => $this->reading_time,
        ], fn($value) => !is_null($value));
    }
}
