<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessProfileMediaJob;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('optimizes avatar and cover to webp and persists the new paths', function (): void {
    $user = User::factory()->withProfile()->create();
    $profile = $user->profile;

    $avatar = UploadedFile::fake()->image('avatar.png', 600, 600)->storeAs('avatars', 'avatar.png', 'public');
    $cover = UploadedFile::fake()->image('cover.jpg', 1600, 600)->storeAs('covers', 'cover.jpg', 'public');

    $profile->update(['avatar_path' => $avatar, 'cover_path' => $cover]);

    app()->call([new ProcessProfileMediaJob($profile), 'handle']);

    $profile->refresh();

    expect($profile->avatar_path)->toBe('avatars/avatar.webp')
        ->and($profile->cover_path)->toBe('covers/cover.webp');

    Storage::disk('public')->assertExists('avatars/avatar.webp');
    Storage::disk('public')->assertExists('covers/cover.webp');
    Storage::disk('public')->assertMissing($avatar);
    Storage::disk('public')->assertMissing($cover);
});

it('leaves webp images untouched', function (): void {
    $user = User::factory()->withProfile()->create();
    $profile = $user->profile;

    $avatar = UploadedFile::fake()->image('avatar.webp', 400, 400)->storeAs('avatars', 'avatar.webp', 'public');
    $profile->update(['avatar_path' => $avatar, 'cover_path' => null]);

    app()->call([new ProcessProfileMediaJob($profile), 'handle']);

    expect($profile->fresh()->avatar_path)->toBe($avatar);
    Storage::disk('public')->assertExists($avatar);
});

it('cleans up the old avatar file when a new one was processed', function (): void {
    $user = User::factory()->withProfile()->create();
    $profile = $user->profile;

    $oldAvatar = UploadedFile::fake()->image('old.webp')->storeAs('avatars', 'old.webp', 'public');
    $newAvatar = UploadedFile::fake()->image('new.png', 400, 400)->storeAs('avatars', 'new.png', 'public');

    $profile->update(['avatar_path' => $newAvatar, 'cover_path' => null]);

    app()->call([new ProcessProfileMediaJob($profile, $oldAvatar), 'handle']);

    Storage::disk('public')->assertMissing($oldAvatar);
    Storage::disk('public')->assertExists('avatars/new.webp');
});

it('does nothing when the profile no longer exists', function (): void {
    $user = User::factory()->withProfile()->create();
    $profile = $user->profile;
    $profile->delete();

    app()->call([new ProcessProfileMediaJob($profile), 'handle']);

    expect(true)->toBeTrue();
});
