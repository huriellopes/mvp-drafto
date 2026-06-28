<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Posts;

use App\Livewire\Dashboard\Posts\CoverUpload;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->writer()->create();
    $this->actingAs($this->user);
});

/**
 * Covers the success path of saveCrop() (lines 61-69): the action runs,
 * currentCoverPath is updated, temp state cleared and the cover-prepared
 * event dispatched.
 */
it('processes a valid crop and dispatches cover-prepared with the new path', function () {
    $file = UploadedFile::fake()->image('cover.jpg', 1200, 400);

    $component = Livewire::test(CoverUpload::class)
        ->set('image', $file)
        ->call('saveCrop', ['x' => 0, 'y' => 0, 'width' => 1200, 'height' => 400])
        ->assertSet('isCropped', true)
        ->assertSet('image', null)
        ->assertSet('imageUrl', null)
        ->assertDispatched('cover-prepared');

    expect($component->get('currentCoverPath'))->not->toBeNull();
});

/**
 * Covers the catch branch of removeCover() (lines 96-97). We force the
 * Storage delete to throw so the failure toaster path runs.
 */
it('handles a storage failure while removing the cover gracefully', function () {
    Storage::shouldReceive('disk')
        ->with('public')
        ->andThrow(new RuntimeException('disk offline'));

    $post = Post::factory()->draft()->for($this->user)->create([
        'cover_image_path' => 'covers/old.webp',
    ]);

    Livewire::test(CoverUpload::class, ['post' => $post])
        ->call('removeCover')
        // O catch engole a exceção; currentCoverPath permanece pois a deleção falhou antes de limpar.
        ->assertSet('currentCoverPath', 'covers/old.webp');
});
