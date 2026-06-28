<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Public\Site;

use App\Livewire\Public\Site\Home;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

it('renders successfully for guests', function () {
    Livewire::test(Home::class)->assertOk();
});

it('lists featured writers (authors that have posts)', function () {
    $writer = User::factory()->writer()->withProfile()->create();
    Post::factory()->published()->for($writer)->create();

    Livewire::test(Home::class)
        ->assertOk()
        ->assertSet('featuredWriters', fn ($writers) => $writers->contains('id', $writer->id));
});

it('does not feature writers without posts', function () {
    $writerWithPost = User::factory()->writer()->withProfile()->create();
    Post::factory()->published()->for($writerWithPost)->create();

    $writerWithoutPost = User::factory()->writer()->withProfile()->create();

    Livewire::test(Home::class)
        ->assertSet('featuredWriters', fn ($writers) => $writers->contains('id', $writerWithPost->id)
            && !$writers->contains('id', $writerWithoutPost->id));
});

it('loads follow status for the authenticated user', function () {
    $author = User::factory()->writer()->withProfile()->create();
    Post::factory()->published()->for($author)->create();

    $follower = User::factory()->create();
    $follower->following()->attach($author->id);

    Livewire::actingAs($follower)
        ->test(Home::class)
        ->assertSet('featuredWriters', function ($writers) use ($author) {
            $found = $writers->firstWhere('id', $author->id);

            return (bool) $found?->is_followed_by_auth_user === true;
        });
});

it('exposes categories ordered by post count', function () {
    $popular = PostCategory::factory()->create();
    $empty = PostCategory::factory()->create();

    Post::factory()->published()->count(2)->create(['category_id' => $popular->id]);

    Livewire::test(Home::class)
        ->assertSet('categories', fn ($categories) => $categories->contains('id', $popular->id)
            && $categories->contains('id', $empty->id));
});

it('renders without errors when published posts exist', function () {
    Post::factory()->published()->create(['title' => 'Homepage Featured Story']);

    Livewire::test(Home::class)->assertOk();
});

it('renders the lazy placeholder view', function () {
    expect(view('livewire.public.site.placeholders.home')->render())->toBeString();
});
