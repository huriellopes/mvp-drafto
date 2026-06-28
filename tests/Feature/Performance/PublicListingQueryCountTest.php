<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Actions\Public\ListPublicPostsAction;
use App\DTOs\Public\PostFilterData;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Simula o que o post-card acessa por post (autor, perfil, categoria, tags),
 * forçando o disparo de qualquer lazy-load que indicaria N+1.
 */
function renderPublicListing(): void
{
    $posts = app(ListPublicPostsAction::class)->exec(
        PostFilterData::from(['perPage' => 50]),
    );

    foreach ($posts as $post) {
        $post->category?->name;
        $post->author->name;
        $post->author->isVerified();           // lê author.profile?->is_verified
        $post->author->profile?->username;
        $post->tags->each(fn ($tag) => $tag->name);
    }
}

it('does not run N+1 queries on the public post listing (query count scales flat with post count)', function () {
    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    // Cada post tem autor e categoria distintos (factory) — cenário ideal p/ N+1.
    Post::factory()->published()->count(3)->create();
    Cache::flush();

    $queries = [];
    renderPublicListing();
    $few = count($queries);

    // Mais posts (autores/categorias distintos). Sem eager loading, o nº de
    // queries cresceria proporcionalmente.
    Post::factory()->published()->count(12)->create();

    $queries = [];
    renderPublicListing();
    $many = count($queries);

    // Com eager loading (author, author.profile, category, tags) o custo é ~constante.
    expect($many - $few)->toBeLessThanOrEqual(1)
        ->and($many)->toBeLessThanOrEqual(12); // teto de sanidade
});
