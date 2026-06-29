<?php

declare(strict_types=1);

namespace App\Actions\Seo;

use App\Enums\PostStatusEnum;
use App\Enums\PostVisibilityEnum;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Post;
use App\Models\User;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemapAction
{
    public function exec(): void
    {
        $baseUrl = 'https://drafto.pro';
        config(['app.url' => $baseUrl]);
        \Illuminate\Support\Facades\URL::forceRootUrl($baseUrl);
        \Illuminate\Support\Facades\URL::forceScheme('https');

        $sitemap = Sitemap::create();

        // 1. Static Routes
        $sitemap->add(
            Url::create(route('home'))
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY),
        );

        $sitemap->add(
            Url::create(route('posts.explore'))
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY),
        );

        $sitemap->add(
            Url::create(route('writers.explore'))
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY),
        );

        // 2. Indexable writers — mesmos filtros do SitemapController para que
        //    o arquivo estático e a rota dinâmica nunca divirjam.
        User::query()
            ->where('role', RoleEnum::WRITER)
            ->where('status', UserStatusEnum::ACTIVE)
            ->whereNull('banned_until')
            ->whereHas('profile', fn ($query) => $query->where('is_searchable', true))
            ->each(function (User $user) use ($sitemap) {
                $sitemap->add($user);
            });

        // 3. Published, public and indexable posts
        Post::query()
            ->where('status', PostStatusEnum::PUBLISHED)
            ->where('visibility', PostVisibilityEnum::PUBLIC)
            ->where('seo_enabled', true)
            ->each(function (Post $post) use ($sitemap) {
                $sitemap->add($post);
            });

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }
}
