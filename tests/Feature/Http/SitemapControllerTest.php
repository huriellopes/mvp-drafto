<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Post;
use App\Models\User;

it('renders the sitemap as xml', function () {
    $response = $this->get(route('sitemap'));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/xml');
});

it('includes published posts and writers in the sitemap', function () {
    $writer = User::factory()->writer()->withProfile()->create();
    $post = Post::factory()->published()->create(['user_id' => $writer->id]);

    $response = $this->get(route('sitemap'));

    $response->assertOk()
        ->assertSee($post->slug, false)
        ->assertSee($writer->profile->username, false);
});

it('excludes non-public posts (unlisted/followers-only) from the sitemap', function () {
    $writer = User::factory()->writer()->withProfile()->create();

    $public = Post::factory()->published()->public()->create(['user_id' => $writer->id]);
    $unlisted = Post::factory()->published()->unlisted()->create(['user_id' => $writer->id]);
    $followers = Post::factory()->published()->followersOnly()->create(['user_id' => $writer->id]);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee($public->slug, false)
        ->assertDontSee($unlisted->slug, false)
        ->assertDontSee($followers->slug, false);
});

it('excludes posts with SEO disabled from the sitemap', function () {
    $writer = User::factory()->writer()->withProfile()->create();
    $noindex = Post::factory()->published()->public()->create([
        'user_id' => $writer->id,
        'seo_enabled' => false,
    ]);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertDontSee($noindex->slug, false);
});

it('excludes non-searchable, inactive and banned writers from the sitemap', function () {
    $hidden = User::factory()->writer()->withProfile()->create();
    $hidden->profile->update(['is_searchable' => false]);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertDontSee($hidden->profile->username, false);
});
