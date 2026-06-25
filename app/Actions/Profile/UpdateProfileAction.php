<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Actions\Posts\UploadCoverImageAction;
use App\DTOs\UpdateProfileData;
use App\Enums\LinkVisibilityEnum;
use App\Jobs\ProcessProfileMediaJob;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final class UpdateProfileAction
{
    public function exec(User $user, UpdateProfileData $data, ?array $coverCropData = null): void
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
            'visibility' => $data->visibility,
            'show_email_publicly' => $data->show_email_publicly,
            'is_searchable' => $data->is_searchable,
        ];

        $oldAvatarPath = $user->profile?->avatar_path;
        $oldCoverPath = $user->profile?->cover_path;

        if ($data->avatar) {
            $profileData['avatar_path'] = $data->avatar->store('avatars', 'public');
        }

        if ($data->cover) {
            if ($coverCropData) {
                $profileData['cover_path'] = app(UploadCoverImageAction::class)->exec($data->cover, $coverCropData);
            } else {
                $profileData['cover_path'] = $data->cover->store('covers', 'public');
            }
        }

        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData,
        );

        // Despacha o processamento de mídia apenas se houver novas imagens
        if ($data->avatar || $data->cover) {
            ProcessProfileMediaJob::dispatch($profile, $oldAvatarPath, $oldCoverPath);
        }

        // Sênior: Limpa o cache do perfil público e da listagem de escritores
        Cache::tags(['profiles', "profile_{$profile->username}", 'writers', 'explore'])->flush();

        // Sênior: Atualiza ou cria as configurações de UI do perfil
        $profile->settings()->updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'button_style' => $data->button_style,
                'card_style' => $data->card_style,
                'layout_type' => $data->layout_type,
                'font_family' => $data->font_family,
                'secondary_color' => $data->secondary_color,
                'text_color' => $data->text_color,
                'background_color' => $data->background_color,
                'show_subscriber_count' => $data->show_subscriber_count,
                'show_view_count' => $data->show_view_count,
            ],
        );

        // Sincroniza os links do perfil (redes sociais e páginas avulsas)
        $this->syncLinks($profile, $data->links);

        // Sênior: Sincroniza o nome na model User para consistência
        if ($data->name && $user->name !== $data->name) {
            $user->update(['name' => $data->name]);
        }

        if ($data->seo_title || $data->seo_description) {
            $profile->seo()->updateOrCreate(
                ['model_id' => $profile->id, 'model_type' => $profile->getMorphClass()],
                [
                    'title' => $data->seo_title,
                    'description' => $data->seo_description,
                ],
            );
        }
    }

    /**
     * Recria os links do perfil a partir do array sanitizado, preservando a ordem.
     *
     * @param  array<int, array{platform: string, url: string, visibility: string}>  $links
     */
    private function syncLinks(Profile $profile, array $links): void
    {
        $profile->links()->delete();

        foreach (array_values($links) as $index => $link) {
            $profile->links()->create([
                'platform' => $link['platform'],
                'url' => $link['url'],
                'visibility' => $link['visibility'] ?? LinkVisibilityEnum::PUBLIC->value,
                'sort_order' => $index,
            ]);
        }
    }
}
