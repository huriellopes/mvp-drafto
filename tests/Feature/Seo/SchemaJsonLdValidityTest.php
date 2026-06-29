<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\Post;
use App\Models\User;
use App\Services\Post\PostSeoGenerator;
use App\Services\Profile\ProfileSeoGenerator;

/**
 * Extracts and json_decodes every <script type="application/ld+json"> block.
 *
 * @return array<int, array<string, mixed>>
 */
function extractJsonLd(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

    return array_map(function (string $json): array {
        $decoded = json_decode($json, true);

        // Falha explícita se algum bloco não for JSON válido.
        expect(json_last_error())->toBe(JSON_ERROR_NONE, "JSON-LD inválido: {$json}");

        return $decoded;
    }, $matches[1]);
}

/**
 * @param  array<int, array<string, mixed>>  $schemas
 * @return array<string, mixed>|null
 */
function findSchema(array $schemas, string $type): ?array
{
    foreach ($schemas as $schema) {
        if (($schema['@type'] ?? null) === $type) {
            return $schema;
        }
    }

    return null;
}

function enableAuthorSeoModule(User $user, ModuleEnum $module): void
{
    $record = Module::query()->where('slug', $module->value)->firstOrFail();

    $user->modules()->syncWithoutDetaching([
        $record->id => ['is_enabled' => true, 'settings' => json_encode(['enable_seo' => true])],
    ]);
}

it('emits valid Article JSON-LD with the required fields', function () {
    $post = Post::factory()->published()->public()->create([
        'seo_enabled' => true,
        'title' => 'Título Canônico',
        'excerpt' => 'Resumo do artigo.',
    ]);
    enableAuthorSeoModule($post->author, ModuleEnum::MY_POSTS);

    $schemas = extractJsonLd((string) seo(PostSeoGenerator::generate($post->fresh('author'))));
    $article = findSchema($schemas, 'Article');

    expect($article)->not->toBeNull()
        ->and($article['@context'])->toBe('https://schema.org')
        ->and($article['headline'])->toBe('Título Canônico')
        ->and($article['description'])->toBe('Resumo do artigo.')
        ->and($article['mainEntityOfPage']['@id'])->toBe(route('posts.show', $post->slug))
        ->and($article['author']['@type'])->toBe('Person')
        ->and($article['author']['name'])->toBe($post->author->display_name);
});

it('emits valid BreadcrumbList JSON-LD as an ordered ListItem trail', function () {
    $post = Post::factory()->published()->public()->create(['seo_enabled' => true]);
    enableAuthorSeoModule($post->author, ModuleEnum::MY_POSTS);

    $schemas = extractJsonLd((string) seo(PostSeoGenerator::generate($post->fresh('author'))));
    $breadcrumbs = findSchema($schemas, 'BreadcrumbList');

    expect($breadcrumbs)->not->toBeNull()
        ->and($breadcrumbs['itemListElement'])->toBeArray()
        ->and(count($breadcrumbs['itemListElement']))->toBeGreaterThanOrEqual(2);

    $first = $breadcrumbs['itemListElement'][0];
    $last = end($breadcrumbs['itemListElement']);

    expect($first['@type'])->toBe('ListItem')
        ->and($first['position'])->toBe(1)
        ->and($first['name'])->toBe('Início')
        ->and($first['item'])->toBe(route('home'))
        ->and($last['item'])->toBe(route('posts.show', $post->slug));

    // Posições contíguas começando em 1.
    foreach ($breadcrumbs['itemListElement'] as $index => $item) {
        expect($item['position'])->toBe($index + 1);
    }
});

it('emits valid ProfilePage/Person JSON-LD with an accessible identity', function () {
    $user = User::factory()->writer()->withProfile()->create();
    enableAuthorSeoModule($user, ModuleEnum::PROFILE);
    $profile = $user->fresh()->profile;

    $schemas = extractJsonLd((string) seo(ProfileSeoGenerator::generate($profile)));
    $page = findSchema($schemas, 'ProfilePage');

    expect($page)->not->toBeNull()
        ->and($page['@context'])->toBe('https://schema.org')
        ->and($page['mainEntity']['@type'])->toBe('Person')
        ->and($page['mainEntity']['name'])->toBe($profile->name ?? $profile->username)
        ->and($page['mainEntity']['alternateName'])->toBe('@' . $profile->username)
        ->and($page['mainEntity']['url'])->toBe(route('profile.show', $profile->username));
});

it('emits valid Organization and WebSite/SearchAction JSON-LD in the public head', function () {
    $schemas = extractJsonLd($this->get(route('home'))->assertOk()->getContent());

    $organization = findSchema($schemas, 'Organization');
    $website = findSchema($schemas, 'WebSite');

    expect($organization)->not->toBeNull()
        ->and($organization['@context'])->toBe('https://schema.org')
        ->and($organization['name'])->toBe(config('app.name'))
        ->and($organization['url'])->toBe(route('home'))
        ->and($organization['logo'])->toContain('android-chrome-512x512.png');

    expect($website)->not->toBeNull()
        ->and($website['url'])->toBe(route('home'))
        ->and($website['potentialAction']['@type'])->toBe('SearchAction')
        ->and($website['potentialAction']['target']['urlTemplate'])->toContain('search={search_term_string}')
        ->and($website['potentialAction']['query-input'])->toBe('required name=search_term_string');
});
