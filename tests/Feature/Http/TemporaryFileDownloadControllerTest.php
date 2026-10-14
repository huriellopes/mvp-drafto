<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('requires authentication', function () {
    $this->get(route('dashboard.temporary-file.download', ['path' => 'temp/exports/1/file.csv']))
        ->assertRedirect(route('login'));
});

it('downloads the owner temporary file and deletes it after sending', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $path = "temp/exports/{$user->id}/export.csv";
    Storage::disk('local')->put($path, 'id,name');

    $response = $this->actingAs($user)->get(
        route('dashboard.temporary-file.download', ['path' => $path]),
    );

    $response->assertOk()
        ->assertDownload('export.csv');
});

it('forbids downloading another user file even when the name is known', function () {
    Storage::fake('local');

    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $path = "temp/exports/{$owner->id}/drafto-owner-01-01-2026.txt";
    Storage::disk('local')->put($path, 'RECOVERY-CODES');

    $this->actingAs($intruder)->get(
        route('dashboard.temporary-file.download', ['path' => $path]),
    )->assertForbidden();

    Storage::disk('local')->assertExists($path);
});

it('forbids path traversal out of the owner directory', function () {
    Storage::fake('local');

    $user = User::factory()->create();

    $this->actingAs($user)->get(
        route('dashboard.temporary-file.download', ['path' => "temp/exports/{$user->id}/../../../secret/keys.txt"]),
    )->assertForbidden();
});

it('forbids a request without a path', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard.temporary-file.download'))
        ->assertForbidden();
});

it('returns 404 when the owner file does not exist', function () {
    Storage::fake('local');

    $user = User::factory()->create();

    $this->actingAs($user)->get(
        route('dashboard.temporary-file.download', ['path' => "temp/exports/{$user->id}/missing.csv"]),
    )->assertNotFound();
});

it('forbids downloading files outside the exports directory', function () {
    Storage::fake('local');
    Storage::disk('local')->put('secret/keys.txt', 'secret');

    $user = User::factory()->create();

    $this->actingAs($user)->get(
        route('dashboard.temporary-file.download', ['path' => 'secret/keys.txt']),
    )->assertForbidden();
});
