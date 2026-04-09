<?php

declare(strict_types=1);

namespace App\Services\Post;

use App\Models\Post;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PostSeoGenerator
{
    /**
     * Generates SEO Data for a given post.
     * Returns a SEOData object compatible with ralphjsmit/laravel-seo.
     */
    public static function generate(Post $post): SEOData
    {
        if (!$post->seo_enabled) {
            return new SEOData(
                robots: 'noindex, nofollow',
            );
        }

        return new SEOData(
            title: $post->title,
            description: $post->excerpt ?? Str::limit(strip_tags($post->content), 160),
            author: $post->author->display_name,
            image: $post->cover_image_url,
            published_time: $post->published_at,
            modified_time: $post->updated_at,
            section: $post->category?->name,
            tags: $post->tags->pluck('name')->toArray(),
            type: $post->type->value,
            robots: 'index, follow',
            canonical_url: route('posts.show', $post->slug),
        );
    }
}
