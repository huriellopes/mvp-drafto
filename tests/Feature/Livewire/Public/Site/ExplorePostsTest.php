<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Public\Site;

use App\Enums\PostTypeEnum;
use App\Livewire\Public\Site\ExplorePosts;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

it('renders successfully with default filters', function () {
    Livewire::test(ExplorePosts::class)
        ->assertOk()
        ->assertSet('search', '')
        ->assertSet('category', '')
        ->assertSet('tag', '')
        ->assertSet('type', '')
        ->assertSet('sort', 'latest');
});

it('filters posts by search term', function () {
    $match = Post::factory()->published()->create(['title' => 'Unique Searchable Headline']);
    $other = Post::factory()->published()->create(['title' => 'Completely Different Title']);

    Livewire::test(ExplorePosts::class)
        ->set('search', 'Unique Searchable')
        ->assertSee($match->title)
        ->assertDontSee($other->title);
})->skip('ListPublicPostsAction uses MySQL fulltext search, unsupported by the SQLite test DB.');

it('filters posts by category', function () {
    $category = PostCategory::factory()->create();
    $inCategory = Post::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Post Inside Category',
    ]);
    $outside = Post::factory()->published()->create(['title' => 'Post Outside Category']);

    Livewire::test(ExplorePosts::class)
        ->set('category', $category->slug)
        ->assertSee($inCategory->title)
        ->assertDontSee($outside->title);
});

it('filters posts by tag', function () {
    $tag = Tag::factory()->create();
    $tagged = Post::factory()->published()->create(['title' => 'Tagged Post Title']);
    $tagged->tags()->attach($tag->id);

    $untagged = Post::factory()->published()->create(['title' => 'Untagged Post Title']);

    Livewire::test(ExplorePosts::class)
        ->set('tag', $tag->slug)
        ->assertSee($tagged->title)
        ->assertDontSee($untagged->title);
});

it('filters posts by type', function () {
    $article = Post::factory()->published()->article()->create(['title' => 'An Article Piece']);
    $post = Post::factory()->published()->post()->create(['title' => 'A Quick Post']);

    Livewire::test(ExplorePosts::class)
        ->set('type', PostTypeEnum::ARTICLE->value)
        ->assertSee($article->title)
        ->assertDontSee($post->title);
});

it('accepts the popular and commented sort options', function () {
    Post::factory()->published()->create();

    Livewire::test(ExplorePosts::class)
        ->set('sort', 'popular')
        ->assertOk()
        ->set('sort', 'commented')
        ->assertOk();
});

it('resets the page when a filter is updated', function () {
    Post::factory()->published()->count(3)->create();

    Livewire::test(ExplorePosts::class)
        ->call('setPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('sort', 'popular')
        ->assertSet('paginators.page', 1);
});

it('resets all filters with resetFilters', function () {
    Livewire::test(ExplorePosts::class)
        ->set('search', 'foo')
        ->set('category', 'bar')
        ->set('tag', 'baz')
        ->set('type', PostTypeEnum::POST->value)
        ->set('sort', 'popular')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('category', '')
        ->assertSet('tag', '')
        ->assertSet('type', '')
        ->assertSet('sort', 'latest');
});

it('exposes the categories computed property', function () {
    $category = PostCategory::factory()->create();
    Post::factory()->published()->create(['category_id' => $category->id]);

    Livewire::test(ExplorePosts::class)
        ->assertSet('categories', fn ($categories) => $categories->contains('id', $category->id));
});

it('exposes the tags computed property limited to tags with posts', function () {
    $usedTag = Tag::factory()->create();
    $post = Post::factory()->published()->create();
    $post->tags()->attach($usedTag->id);

    $unusedTag = Tag::factory()->create();

    Livewire::test(ExplorePosts::class)
        ->assertSet('tags', fn ($tags) => $tags->contains('id', $usedTag->id)
            && !$tags->contains('id', $unusedTag->id));
});

it('renders the lazy placeholder view', function () {
    expect(view('livewire.public.site.placeholders.explore-posts')->render())->toBeString();
});
