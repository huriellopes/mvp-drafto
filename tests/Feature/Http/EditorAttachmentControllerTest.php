<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('requires authentication to upload an attachment', function () {
    $this->post(route('editor.attachments.store'))
        ->assertRedirect(route('login'));
});

it('stores an uploaded image and returns its url and type', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('editor.attachments.store'), [
        'file' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertOk()
        ->assertJsonStructure(['url', 'type'])
        ->assertJson(['type' => 'image']);

    expect(Storage::disk('public')->allFiles(config('editor.upload_path')))->not->toBeEmpty();
});

it('detects video uploads as the video type', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('editor.attachments.store'), [
        'file' => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
    ]);

    $response->assertOk()->assertJson(['type' => 'video']);
});

it('rejects a request without a file', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('editor.attachments.store'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

it('rejects a disallowed mimetype', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('editor.attachments.store'), [
        'file' => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
    ])->assertStatus(422)->assertJsonValidationErrors(['file']);
});
