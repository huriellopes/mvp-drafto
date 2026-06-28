<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Public\Posts;

use App\Enums\RoleEnum;
use App\Livewire\Public\Posts\ShowPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

function showPostAuthor(): User
{
    return User::factory()->writer()->withProfile()->create();
}

it('renders a published public post', function () {
    $post = Post::factory()->published()->public()->for(showPostAuthor())->create([
        'title' => 'A Public Story',
    ]);

    Livewire::test(ShowPost::class, ['slug' => $post->slug])
        ->assertOk()
        ->assertSet('post.id', $post->id)
        ->assertSet('canReadContent', true);
});

it('throws a 404 for an unknown slug', function () {
    Livewire::test(ShowPost::class, ['slug' => 'does-not-exist']);
})->throws(ModelNotFoundException::class);

it('does not render unpublished posts', function () {
    $post = Post::factory()->draft()->for(showPostAuthor())->create();

    Livewire::test(ShowPost::class, ['slug' => $post->slug]);
})->throws(ModelNotFoundException::class);

it('allows reading unlisted posts', function () {
    $post = Post::factory()->published()->unlisted()->for(showPostAuthor())->create();

    Livewire::test(ShowPost::class, ['slug' => $post->slug])
        ->assertSet('canReadContent', true);
});

it('blocks followers-only posts for guests', function () {
    $post = Post::factory()->published()->followersOnly()->for(showPostAuthor())->create();

    Livewire::test(ShowPost::class, ['slug' => $post->slug])
        ->assertSet('canReadContent', false);
});

it('blocks followers-only posts for non-followers', function () {
    $post = Post::factory()->published()->followersOnly()->for(showPostAuthor())->create();
    $stranger = User::factory()->create();

    Livewire::actingAs($stranger)
        ->test(ShowPost::class, ['slug' => $post->slug])
        ->assertSet('canReadContent', false);
});

it('allows followers-only posts for followers', function () {
    $author = showPostAuthor();
    $post = Post::factory()->published()->followersOnly()->for($author)->create();

    $follower = User::factory()->create();
    $follower->following()->attach($author->id);

    Livewire::actingAs($follower)
        ->test(ShowPost::class, ['slug' => $post->slug])
        ->assertSet('canReadContent', true);
});

it('allows followers-only posts for the author', function () {
    $author = showPostAuthor();
    $post = Post::factory()->published()->followersOnly()->for($author)->create();

    Livewire::actingAs($author)
        ->test(ShowPost::class, ['slug' => $post->slug])
        ->assertSet('canReadContent', true);
});

it('allows followers-only posts for super admins', function () {
    $post = Post::factory()->published()->followersOnly()->for(showPostAuthor())->create();
    $admin = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);

    Livewire::actingAs($admin)
        ->test(ShowPost::class, ['slug' => $post->slug])
        ->assertSet('canReadContent', true);
});

it('wraps youtube iframes in a responsive container', function () {
    $content = '<iframe src="https://www.youtube.com/embed/abc123"></iframe>';
    $post = Post::factory()->published()->for(showPostAuthor())->create(['content' => $content]);

    $rendered = Livewire::test(ShowPost::class, ['slug' => $post->slug])->get('renderedContent');

    expect($rendered)->toContain('aspect-video')
        ->and($rendered)->toContain('referrerpolicy');
});

it('wraps vimeo iframes but leaves other iframes untouched', function () {
    $content = '<iframe src="https://player.vimeo.com/video/12345"></iframe>'
        . '<iframe src="https://evil.example.com/embed/x"></iframe>';
    $post = Post::factory()->published()->for(showPostAuthor())->create(['content' => $content]);

    $rendered = Livewire::test(ShowPost::class, ['slug' => $post->slug])->get('renderedContent');

    expect($rendered)->toContain('player.vimeo.com')
        ->and($rendered)->toContain('aspect-video')
        ->and($rendered)->toContain('evil.example.com');
});

it('decodes escaped youtube iframe entities', function () {
    $content = '&lt;iframe src="https://www.youtube.com/embed/xyz"&gt;&lt;/iframe&gt;';
    $post = Post::factory()->published()->for(showPostAuthor())->create(['content' => $content]);

    $rendered = Livewire::test(ShowPost::class, ['slug' => $post->slug])->get('renderedContent');

    expect($rendered)->toContain('aspect-video');
});

it('exposes related posts as a collection', function () {
    $post = Post::factory()->published()->for(showPostAuthor())->create();

    Livewire::test(ShowPost::class, ['slug' => $post->slug])
        ->assertSet('relatedPosts', fn ($related) => $related instanceof Collection
            || $related instanceof \Illuminate\Database\Eloquent\Collection);
});
