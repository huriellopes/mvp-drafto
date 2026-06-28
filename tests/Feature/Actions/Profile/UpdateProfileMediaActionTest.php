<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\UpdateProfileMediaAction;
use App\Jobs\ProcessProfileMediaJob;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Queue::fake();
    $this->action = app(UpdateProfileMediaAction::class);
});

it('stores the avatar, updates the profile and dispatches cleanup', function () {
    $user = User::factory()->withProfile()->create();
    $file = UploadedFile::fake()->image('avatar.png');

    $path = $this->action->updateAvatar($user, $file);

    expect($path)->toStartWith('avatars/');
    Storage::disk('public')->assertExists($path);
    expect($user->profile->fresh()->avatar_path)->toBe($path);

    Queue::assertPushed(ProcessProfileMediaJob::class);
});

it('crops, stores the cover as webp and dispatches cleanup', function () {
    $user = User::factory()->withProfile()->create();
    $file = UploadedFile::fake()->image('cover.jpg', 1600, 900);

    $path = $this->action->updateCover($user, $file, [
        'width' => 800,
        'height' => 300,
        'x' => 0,
        'y' => 0,
    ]);

    expect($path)->toStartWith('covers/')->toEndWith('.webp');
    Storage::disk('public')->assertExists($path);
    expect($user->profile->fresh()->cover_path)->toBe($path);

    Queue::assertPushed(ProcessProfileMediaJob::class);
});
