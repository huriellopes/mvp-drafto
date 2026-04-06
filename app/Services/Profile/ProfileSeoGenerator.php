<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Models\Profile;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class ProfileSeoGenerator
{
    public static function generate(Profile $profile): SEOData
    {
        $displayName = $profile->name ?? $profile->username;

        return new SEOData(
            title: "{$displayName} (@{$profile->username}) | Drafto",
            description: $profile->bio ?? "Explore as publicações e o perfil de {$displayName} na Drafto.",
            author: $profile->name,
            image: $profile->avatar_path ? \Storage::url($profile->avatar_path) : null,
            type: 'profile',
            robots: $profile->is_searchable ? 'index, follow' : 'noindex, nofollow',
            canonical_url: route('profile.show', $profile->username),
        );
    }
}
