<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Post;

it('skips posts whose content is empty after stripping tags', function () {
    // Content is non-empty (passes the query filter) but strip_tags leaves nothing,
    // so the post is skipped via the continue branch (lines 48-51).
    $post = Post::factory()->published()->create([
        'excerpt' => null,
        'content' => '<p></p><br>',
    ]);

    $this->artisan('app:generate-missing-excerpts')
        ->expectsOutputToContain('Successfully generated excerpts for 0 posts')
        ->assertExitCode(0);

    expect($post->fresh()->excerpt)->toBeNull();
});
