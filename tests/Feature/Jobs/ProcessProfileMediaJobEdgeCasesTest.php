<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessProfileMediaJob;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

beforeEach(function (): void {
    Storage::fake('public');
});

it('keeps the original avatar path when the source file does not exist on disk', function () {
    $user = User::factory()->withProfile()->create();
    $profile = $user->profile;

    // Non-webp path pointing at a file that was never stored: optimizeImage hits
    // the file_exists guard (line 75) and returns the original path unchanged.
    $profile->update(['avatar_path' => 'avatars/missing.png', 'cover_path' => null]);

    app()->call([new ProcessProfileMediaJob($profile), 'handle']);

    expect($profile->fresh()->avatar_path)->toBe('avatars/missing.png');
});

it('logs through the failed hook', function () {
    Log::spy();

    $user = User::factory()->withProfile()->create();
    $job = new ProcessProfileMediaJob($user->profile);

    $job->failed(new RuntimeException('media boom'));

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message) => str_contains($message, 'ProcessProfileMediaJob falhou'));
});
