<?php

declare(strict_types=1);

namespace App\Services\Post;

use App\Enums\ModuleEnum;
use App\Models\Post;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Schema\CustomSchema;
use RalphJSmit\Laravel\SEO\SchemaCollection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class PostSeoGenerator
{
    private function __construct()
    {
        // Static helper class
    }

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

        $seoData = new SEOData(
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

        // Sênior: Se o plano permitir SEO, adicionamos Dados Estruturados (Schema.org)
        if ($post->author->getModuleSetting(ModuleEnum::MY_POSTS, 'enable_seo', false)) {
            $seoData->schema = SchemaCollection::initialize([
                new CustomSchema([
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => $post->title,
                    'description' => $seoData->description,
                    'datePublished' => $post->published_at?->toIso8601String(),
                    'dateModified' => $post->updated_at?->toIso8601String(),
                    'author' => [
                        '@type' => 'Person',
                        'name' => $post->author->display_name,
                    ],
                    'image' => $post->cover_image_url,
                ]),
            ]);
        }

        return $seoData;
    }
}
