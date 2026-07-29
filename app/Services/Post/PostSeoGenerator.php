<?php

declare(strict_types=1);

namespace App\Services\Post;

use App\Enums\ModuleEnum;
use App\Models\Post;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Schema\BreadcrumbListSchema;
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

        $url = route('posts.show', $post->slug);

        $seoData = new SEOData(
            title: $post->title,
            description: $post->excerpt ?? Str::limit(strip_tags($post->content), 160),
            author: $post->author->display_name,
            image: $post->cover_image_url,
            // `url` alimenta os schemas (Article.mainEntityOfPage e o último
            // item do BreadcrumbList) — precisa ser não-nulo quando há schema.
            url: $url,
            published_time: $post->published_at,
            modified_time: $post->updated_at,
            section: $post->category?->name,
            tags: $post->tags->pluck('name')->toArray(),
            // og:type válido para conteúdo editorial ('post'/'article' do enum
            // não são tipos Open Graph válidos).
            type: 'article',
            robots: 'index, follow',
            canonical_url: $url,
        );

        // Sênior: Se o plano permitir SEO, adicionamos Dados Estruturados (Schema.org)
        // reais — Article + trilha de navegação (BreadcrumbList).
        if ($post->author->getModuleSetting(ModuleEnum::MY_POSTS, 'enable_seo', false)) {
            $seoData->schema = SchemaCollection::initialize()
                ->addArticle()
                ->addBreadcrumbs(function (BreadcrumbListSchema $schema) use ($post): BreadcrumbListSchema {
                    $crumbs = ['Início' => route('home')];

                    if ($post->category) {
                        $crumbs[$post->category->name] = route('posts.explore', ['category' => $post->category->slug]);
                    }

                    // O item final (o próprio post) já é semeado pelo SEOData->url.
                    return $schema->prependBreadcrumbs($crumbs);
                });
        }

        return $seoData;
    }
}
