<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Jobs\ProcessProfileMediaJob;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\Image\ImageManager;

final class UpdateProfileMediaAction
{
    /**
     * Handles immediate definitive upload and DB update for profile avatar.
     */
    public function updateAvatar(User $user, UploadedFile $file): string
    {
        $oldPath = $user->profile?->avatar_path;

        $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('avatars', $fileName, 'public');

        $user->profile()->update(['avatar_path' => $path]);

        ProcessProfileMediaJob::dispatch($user->profile, $oldPath, null);

        return $path;
    }

    /**
     * Handles immediate definitive upload, cropping, and DB update for profile cover.
     * @throws InvalidArgumentException
     */
    public function updateCover(User $user, UploadedFile $file, array $cropData): string
    {
        $oldPath = $user->profile?->cover_path;

        $fileName = hexdec(uniqid()) . '.webp';
        $path = 'covers/' . $fileName;

        $manager = ImageManager::usingDriver(new Driver());
        $image = $manager->decodePath($file->getRealPath());

        // Perform crop based on CropperJS coordinates
        $image->crop(
            width: (int) $cropData['width'],
            height: (int) $cropData['height'],
            x: (int) $cropData['x'],
            y: (int) $cropData['y']
        );

        // Resize to standard cover dimensions (3:1 ratio)
        $image->resize(1200, 400);

        // Save definitely as WebP
        Storage::disk('public')->put($path, (string) $image->encodeUsingFileExtension('webp', 90));

        $user->profile()->update(['cover_path' => $path]);

        // Dispatch background cleanup
        ProcessProfileMediaJob::dispatch($user->profile, null, $oldPath);

        return $path;
    }
}
