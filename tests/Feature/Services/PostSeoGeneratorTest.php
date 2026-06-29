<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\Post;
use App\Services\Post\PostSeoGenerator;

it('returns a noindex payload when SEO is disabled for the post', function () {
    $post = Post::factory()->create(['seo_enabled' => false]);

    $seo = PostSeoGenerator::generate($post);

    expect($seo->robots)->toBe('noindex, nofollow')
        ->and($seo->title)->toBeNull()
        ->and($seo->schema)->toBeNull();
});

it('builds an indexable payload using the post excerpt as description', function () {
    $post = Post::factory()->create([
        'seo_enabled' => true,
        'title' => 'Meu Artigo',
        'excerpt' => 'Resumo objetivo do artigo.',
    ]);

    $seo = PostSeoGenerator::generate($post);

    expect($seo->robots)->toBe('index, follow')
        ->and($seo->title)->toBe('Meu Artigo')
        ->and($seo->description)->toBe('Resumo objetivo do artigo.');
});

it('uses a valid Open Graph type (article)', function () {
    $post = Post::factory()->create(['seo_enabled' => true]);

    expect(PostSeoGenerator::generate($post)->type)->toBe('article');
});

it('falls back to a stripped, truncated content excerpt when none is provided', function () {
    $post = Post::factory()->create([
        'seo_enabled' => true,
        'excerpt' => null,
        'content' => '<p>' . str_repeat('Lorem ipsum ', 50) . '</p>',
    ]);

    $seo = PostSeoGenerator::generate($post);

    expect($seo->description)->not->toContain('<p>')
        ->and($seo->description)->toStartWith('Lorem ipsum')
        ->and(mb_strlen($seo->description))->toBeLessThanOrEqual(164); // 160 + reticências
});

it('does not attach schema when the author SEO module setting is disabled', function () {
    $post = Post::factory()->create(['seo_enabled' => true]);

    expect(PostSeoGenerator::generate($post)->schema)->toBeNull();
});

it('attaches Article schema when the author has the SEO module setting enabled', function () {
    $post = Post::factory()->create(['seo_enabled' => true]);

    $module = Module::query()->where('slug', ModuleEnum::MY_POSTS->value)->firstOrFail();
    $post->author->modules()->syncWithoutDetaching([
        $module->id => ['is_enabled' => true, 'settings' => json_encode(['enable_seo' => true])],
    ]);

    $seo = PostSeoGenerator::generate($post->fresh('author'));

    expect($seo->schema)->not->toBeNull();
});
