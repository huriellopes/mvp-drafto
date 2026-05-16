<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;

it('generates excerpts for published posts with missing excerpts', function () {
    $user = User::factory()->create();
    
    $post1 = Post::factory()->create([
        'user_id' => $user->id,
        'content' => 'This is a long content that should be summarized.',
        'excerpt' => null,
        'status' => \App\Enums\PostStatusEnum::PUBLISHED,
    ]);

    $post2 = Post::factory()->create([
        'user_id' => $user->id,
        'content' => 'Another content for summary.',
        'excerpt' => '',
        'status' => \App\Enums\PostStatusEnum::PUBLISHED,
    ]);

    $post3 = Post::factory()->create([
        'user_id' => $user->id,
        'content' => 'This one already has an excerpt.',
        'excerpt' => 'Existing excerpt',
        'status' => \App\Enums\PostStatusEnum::PUBLISHED,
    ]);

    $post4 = Post::factory()->create([
        'user_id' => $user->id,
        'content' => 'Draft post should be ignored.',
        'excerpt' => null,
        'status' => \App\Enums\PostStatusEnum::DRAFT,
    ]);

    $this->artisan('app:generate-missing-excerpts')
        ->expectsOutput('Starting excerpt generation...')
        ->assertExitCode(0);

    expect($post1->fresh()->excerpt)->toBe(Str::limit(strip_tags($post1->content), 160))
        ->and($post2->fresh()->excerpt)->toBe(Str::limit(strip_tags($post2->content), 160))
        ->and($post3->fresh()->excerpt)->toBe('Existing excerpt')
        ->and($post4->fresh()->excerpt)->toBeNull();
});
