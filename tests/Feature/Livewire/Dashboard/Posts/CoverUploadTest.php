<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Posts;

use App\Livewire\Dashboard\Posts\CoverUpload;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->writer()->create();
    $this->actingAs($this->user);
});

it('mounts with the current cover of an existing post', function () {
    $post = Post::factory()->draft()->for($this->user)->create([
        'cover_image_path' => 'covers/existing.webp',
    ]);

    Livewire::test(CoverUpload::class, ['post' => $post])
        ->assertSet('isCropped', true)
        ->assertSet('currentCoverPath', 'covers/existing.webp');
});

it('mounts without a cover when none is set', function () {
    Livewire::test(CoverUpload::class)
        ->assertSet('isCropped', false)
        ->assertSet('currentCoverPath', null);
});

it('validates that the uploaded file is an image', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    Livewire::test(CoverUpload::class)
        ->set('image', $file)
        ->assertHasErrors('image');
});

it('accepts a valid image upload and dispatches image-uploaded', function () {
    $file = UploadedFile::fake()->image('cover.jpg', 1200, 400);

    Livewire::test(CoverUpload::class)
        ->set('image', $file)
        ->assertHasNoErrors()
        ->assertSet('isCropped', false)
        ->assertDispatched('image-uploaded');
});

it('handles a failure while processing the crop gracefully', function () {
    $file = UploadedFile::fake()->image('cover.jpg', 1200, 400);

    // Crop data inválido força exceção no Action, exercitando o branch de catch.
    Livewire::test(CoverUpload::class)
        ->set('image', $file)
        ->call('saveCrop', ['width' => 'invalid'])
        ->assertSet('isCropped', false);
});

it('removes the current cover and updates the post', function () {
    Storage::disk('public')->put('covers/old.webp', 'fake');

    $post = Post::factory()->draft()->for($this->user)->create([
        'cover_image_path' => 'covers/old.webp',
    ]);

    Livewire::test(CoverUpload::class, ['post' => $post])
        ->call('removeCover')
        ->assertSet('currentCoverPath', null)
        ->assertSet('isCropped', false)
        ->assertDispatched('cover-prepared', coverPath: null);

    expect($post->fresh()->cover_image_path)->toBeNull();
    Storage::disk('public')->assertMissing('covers/old.webp');
});

it('returns early from removeCover when there is no cover', function () {
    Livewire::test(CoverUpload::class)
        ->call('removeCover')
        ->assertSet('currentCoverPath', null);
});

it('does not try to delete external cover urls from storage', function () {
    $post = Post::factory()->draft()->for($this->user)->create([
        'cover_image_path' => 'https://example.com/cover.png',
    ]);

    Livewire::test(CoverUpload::class, ['post' => $post])
        ->call('removeCover')
        ->assertSet('currentCoverPath', null);

    expect($post->fresh()->cover_image_path)->toBeNull();
});
