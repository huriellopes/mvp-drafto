<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Seo;

use App\Actions\Seo\GenerateSitemapAction;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->action = app(GenerateSitemapAction::class);
    $this->sitemapPath = public_path('sitemap.xml');

    if (file_exists($this->sitemapPath)) {
        $this->backup = file_get_contents($this->sitemapPath);
    }
});

afterEach(function () {
    if (isset($this->backup)) {
        file_put_contents($this->sitemapPath, $this->backup);
    } elseif (file_exists($this->sitemapPath)) {
        @unlink($this->sitemapPath);
    }
});

it('writes a sitemap file with static, writer and post urls', function () {
    $writer = User::factory()->writer()->active()->withProfile()->create();
    $post = Post::factory()->published()->public()->create();

    $this->action->exec();

    expect(file_exists($this->sitemapPath))->toBeTrue();

    $contents = file_get_contents($this->sitemapPath);

    expect($contents)->toContain('https://drafto.pro')
        ->and($contents)->toContain($post->slug)
        ->and($contents)->toContain($writer->profile->username);
});
