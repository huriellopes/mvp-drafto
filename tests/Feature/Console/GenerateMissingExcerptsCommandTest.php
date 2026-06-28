<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Post;

it('fills missing excerpts for published posts from their content', function () {
    $post = Post::factory()->published()->create([
        'excerpt' => '',
        'content' => str_repeat('Conteúdo relevante do post. ', 20),
    ]);

    $this->artisan('app:generate-missing-excerpts')
        ->assertExitCode(0);

    expect($post->fresh()->excerpt)->not->toBeEmpty();
});

it('does nothing when there are no posts with missing excerpts', function () {
    Post::factory()->published()->create(['excerpt' => 'Já tenho um resumo.']);

    $this->artisan('app:generate-missing-excerpts')
        ->expectsOutputToContain('No published posts found with missing excerpts.')
        ->assertExitCode(0);
});
