<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\DTOs\UpdateProfileData;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class UpdateProfileAction
{
    public function exec(User $user, UpdateProfileData $data): void
    {
        $profileData = [
            'name' => $data->name,
            'username' => $data->username,
            'email' => $data->email,
            'bio' => $data->bio,
            'location' => $data->location,
            'website_url' => $data->website_url,
            'primary_color' => $data->primary_color,
            'accent_color' => $data->accent_color,
            'theme_mode' => $data->theme_mode,
            'show_email_publicly' => $data->show_email_publicly,
            'is_searchable' => $data->is_searchable,
        ];

        if ($data->avatar) {
            $this->cleanupOldFile($user->profile?->avatar_path);
            $profileData['avatar_path'] = $data->avatar->store('avatars', 'public');
        }

        if ($data->cover) {
            $this->cleanupOldFile($user->profile?->cover_path);
            $profileData['cover_path'] = $data->cover->store('covers', 'public');
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );
    }

    private function cleanupOldFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
