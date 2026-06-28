<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessPostMediaAndSeoJob;
use App\Models\Post;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;

afterEach(function () {
    Mockery::close();
});

it('returns early in optimizeImage when the source cover file is missing', function () {
    Storage::fake('public');

    // Non-webp path that was never stored: optimizeImage hits the file_exists
    // guard (lines 94-96) and returns without touching the cover path.
    $post = Post::factory()->create(['cover_image_path' => 'posts/ghost.jpg']);

    app()->call([new ProcessPostMediaAndSeoJob($post), 'handle']);

    expect($post->fresh()->cover_image_path)->toBe('posts/ghost.jpg');
});

it('logs a warning but does not fail when deleting the old image throws', function () {
    // Mock the public disk so delete() throws, exercising the cleanup catch (56-58).
    $disk = Mockery::mock();
    $disk->shouldReceive('delete')->andThrow(new Exception('disk error'));
    Storage::shouldReceive('disk')->with('public')->andReturn($disk);

    Log::spy();

    // Already-webp cover so optimizeImage is skipped; old image differs so cleanup runs.
    $post = Post::factory()->create(['cover_image_path' => 'posts/current.webp']);

    app()->call([new ProcessPostMediaAndSeoJob($post, [], 'posts/old.jpg'), 'handle']);

    Log::shouldHaveReceived('warning')
        ->withArgs(fn ($message) => str_contains($message, 'Erro ao deletar imagem antiga'));
});

it('logs through the failed hook', function () {
    Storage::fake('public');
    Log::spy();

    $post = Post::factory()->create();
    $job = new ProcessPostMediaAndSeoJob($post);

    $job->failed(new RuntimeException('seo boom'));

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message) => str_contains($message, 'ProcessPostMediaAndSeoJob falhou'));
});
