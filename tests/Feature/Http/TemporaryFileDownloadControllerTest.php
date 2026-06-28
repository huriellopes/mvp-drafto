<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('requires authentication', function () {
    $this->get(route('dashboard.temporary-file.download', ['path' => 'temp/file.csv']))
        ->assertRedirect(route('login'));
});

it('downloads a temporary file and deletes it after sending', function () {
    Storage::fake('local');
    Storage::disk('local')->put('temp/export.csv', 'id,name');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(
        route('dashboard.temporary-file.download', ['path' => 'temp/export.csv']),
    );

    $response->assertOk()
        ->assertDownload('export.csv');
});

it('returns 404 when the path is missing', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard.temporary-file.download'))
        ->assertStatus(404);
});

it('returns 404 when the file does not exist', function () {
    Storage::fake('local');

    $user = User::factory()->create();

    $this->actingAs($user)->get(
        route('dashboard.temporary-file.download', ['path' => 'temp/missing.csv']),
    )->assertStatus(404);
});

it('forbids downloading files outside the temp directory', function () {
    Storage::fake('local');
    Storage::disk('local')->put('secret/keys.txt', 'secret');

    $user = User::factory()->create();

    $this->actingAs($user)->get(
        route('dashboard.temporary-file.download', ['path' => 'secret/keys.txt']),
    )->assertStatus(403);
});
