<?php

declare(strict_types=1);

namespace App\Services\Post;

use App\Models\Post;
use Illuminate\Support\Str;

class PostSeoGenerator
{
    public static function generate(Post $post): array
    {
        if (!$post->seo_enabled) {
            return ['robots' => 'noindex, nofollow'];
        }

        return [
            'title' => $post->title,
            'description' => $post->excerpt ?? Str::limit(strip_tags($post->content), 160),
            'og:title' => $post->title,
            'og:description' => $post->excerpt,
            'og:image' => $post->cover_image_url,
            'og:type' => 'article',
            'article:published_time' => $post->published_at->toIso8601String(),
            'article:author' => $post->author->name,
            'article:section' => $post->category->name,
            'article:tag' => $post->tags->pluck('name')->toArray(),
            'robots' => 'index, follow',
        ];
    }
}
