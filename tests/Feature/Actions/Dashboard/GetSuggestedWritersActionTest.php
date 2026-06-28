<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Dashboard;

use App\Actions\Dashboard\GetSuggestedWritersAction;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Cria um escritor com um post publicado na categoria informada e faz o leitor
 * curtir esse post — o que torna a categoria "favorita" do leitor.
 */
function writerLikedBy(User $reader, PostCategory $category): User
{
    $writer = User::factory()->writer()->withProfile()->create();
    $post = Post::factory()->published()->create([
        'user_id' => $writer->id,
        'category_id' => $category->id,
    ]);
    $post->likedByUsers()->attach($reader->id);

    return $writer;
}

beforeEach(function () {
    Cache::flush();
});

it('suggests writers from the categories the reader interacted with', function () {
    $reader = User::factory()->create();
    $category = PostCategory::factory()->create();
    $writer = writerLikedBy($reader, $category);

    $result = app(GetSuggestedWritersAction::class)->exec($reader);

    expect($result->pluck('id')->all())->toContain($writer->id);
});

it('never suggests the reader nor writers already followed', function () {
    $reader = User::factory()->create();
    $category = PostCategory::factory()->create();
    $followed = writerLikedBy($reader, $category);

    $reader->following()->attach($followed->id);

    $result = app(GetSuggestedWritersAction::class)->exec($reader);

    expect($result->pluck('id')->all())
        ->not->toContain($followed->id)
        ->not->toContain($reader->id);
});

it('respects the requested limit', function () {
    $reader = User::factory()->create();
    $category = PostCategory::factory()->create();

    foreach (range(1, 4) as $ignored) {
        writerLikedBy($reader, $category);
    }

    $result = app(GetSuggestedWritersAction::class)->exec($reader, limit: 2);

    expect($result)->toHaveCount(2);
});

it('caches the discovery so results stay stable within the TTL', function () {
    $reader = User::factory()->create();
    $category = PostCategory::factory()->create();
    $writer = writerLikedBy($reader, $category);

    // 1ª chamada popula o cache de IDs.
    $first = app(GetSuggestedWritersAction::class)->exec($reader);
    expect($first->pluck('id')->all())->toContain($writer->id);

    // O leitor passa a seguir o escritor — o que normalmente o removeria da lista.
    $reader->following()->attach($writer->id);

    // Sem limpar o cache, o resultado permanece o mesmo (descoberta cacheada).
    $cached = app(GetSuggestedWritersAction::class)->exec($reader);
    expect($cached->pluck('id')->all())->toContain($writer->id);

    // Após invalidar o cache, a regra de "não seguidos" volta a valer.
    Cache::flush();
    $fresh = app(GetSuggestedWritersAction::class)->exec($reader);
    expect($fresh->pluck('id')->all())->not->toContain($writer->id);
});
