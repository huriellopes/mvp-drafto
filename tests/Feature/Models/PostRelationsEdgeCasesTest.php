<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\PostView;
use App\Models\Report;
use App\Models\ShortLink;
use App\Models\User;

it('exposes the reports morph relationship', function () {
    $post = Post::factory()->published()->create();
    $report = Report::factory()->create([
        'reportable_type' => $post->getMorphClass(),
        'reportable_id' => $post->id,
    ]);

    expect($post->reports()->count())->toBe(1)
        ->and($post->reports->pluck('id')->all())->toContain($report->id);
});

it('exposes the views relationship', function () {
    $post = Post::factory()->published()->create();
    PostView::factory()->count(2)->create(['post_id' => $post->id]);

    expect($post->views()->count())->toBe(2);
});

it('exposes the post shortLinks morph relationship', function () {
    $post = Post::factory()->published()->create();

    $shortLink = ShortLink::create([
        'user_id' => User::factory()->create()->id,
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
        'code' => 'pstlnk',
    ]);

    expect($post->shortLinks()->count())->toBe(1)
        ->and($post->shortLinks->pluck('id')->all())->toContain($shortLink->id);
});
