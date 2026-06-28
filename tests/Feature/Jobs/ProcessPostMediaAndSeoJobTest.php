<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessPostMediaAndSeoJob;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('converts a non-webp cover image to webp and removes the original', function (): void {
    $file = UploadedFile::fake()->image('cover.jpg', 800, 600);
    $path = $file->storeAs('posts', 'cover.jpg', 'public');

    $post = Post::factory()->create(['cover_image_path' => $path]);

    app()->call([new ProcessPostMediaAndSeoJob($post), 'handle']);

    $webpPath = 'posts/cover.webp';

    Storage::disk('public')->assertExists($webpPath);
    Storage::disk('public')->assertMissing($path);
    expect($post->fresh()->cover_image_path)->toBe($webpPath);
});

it('does not reprocess an image already in webp format', function (): void {
    $file = UploadedFile::fake()->image('cover.webp', 400, 400);
    $path = $file->storeAs('posts', 'cover.webp', 'public');

    $post = Post::factory()->create(['cover_image_path' => $path]);

    app()->call([new ProcessPostMediaAndSeoJob($post), 'handle']);

    Storage::disk('public')->assertExists($path);
    expect($post->fresh()->cover_image_path)->toBe($path);
});

it('deletes the old image path during cleanup', function (): void {
    $old = UploadedFile::fake()->image('old.webp')->storeAs('posts', 'old.webp', 'public');
    $current = UploadedFile::fake()->image('new.webp')->storeAs('posts', 'new.webp', 'public');

    $post = Post::factory()->create(['cover_image_path' => $current]);

    app()->call([new ProcessPostMediaAndSeoJob($post, [], $old), 'handle']);

    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($current);
});

it('creates an seo record when seo data is provided', function (): void {
    $post = Post::factory()->create(['cover_image_path' => null]);

    app()->call([
        new ProcessPostMediaAndSeoJob($post, [
            'title' => 'My SEO title',
            'description' => 'My SEO description',
        ]),
        'handle',
    ]);

    $seo = $post->fresh()->seo;

    expect($seo->title)->toBe('My SEO title')
        ->and($seo->description)->toBe('My SEO description');
});

it('does nothing when the post no longer exists', function (): void {
    $post = Post::factory()->create(['cover_image_path' => null]);
    $id = $post->id;
    $post->forceDelete();

    app()->call([new ProcessPostMediaAndSeoJob($post, ['title' => 'X']), 'handle']);

    expect(Post::find($id))->toBeNull();
});
