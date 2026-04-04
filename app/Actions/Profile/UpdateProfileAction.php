<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class UpdateProfileAction
{
    /**
     * @param array{
     * username: string,
     * bio: ?string,
     * location: ?string,
     * website_url: ?string,
     * primary_color: string,
     * theme_mode: string,
     * avatar?: mixed,
     * cover?: mixed
     * } $data
     */
    public function exec(User $user, array $data): void
    {
        $profileData = collect($data)->except(['avatar', 'cover'])->toArray();

        if (isset($data['avatar'])) {
            if ($user->profile?->avatar_path) Storage::disk('public')->delete($user->profile->avatar_path);
            $profileData['avatar_path'] = $data['avatar']->store('avatars', 'public');
        }

        if (isset($data['cover'])) {
            if ($user->profile?->cover_path) Storage::disk('public')->delete($user->profile->cover_path);
            $profileData['cover_path'] = $data['cover']->store('covers', 'public');
        }

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);
    }
}
