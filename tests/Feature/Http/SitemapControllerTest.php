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
        ->assertSee($post->slug, false);
});
