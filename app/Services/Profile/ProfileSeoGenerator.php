<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Enums\ModuleEnum;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use RalphJSmit\Laravel\SEO\SchemaCollection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class ProfileSeoGenerator
{
    private function __construct()
    {
        // Static helper class
    }

    public static function generate(Profile $profile): SEOData
    {
        $displayName = $profile->name ?? $profile->username;

        $canIndex = $profile->is_searchable &&
            $profile->user?->isActive() &&
            !$profile->user->banned_until;

        $url = route('profile.show', $profile->username);
        $image = $profile->avatar_path ? Storage::url($profile->avatar_path) : null;
        $description = $profile->bio ?? "Explore as publicações e o perfil de {$displayName} na Drafto.";

        $seoData = new SEOData(
            title: "{$displayName} (@{$profile->username}) | Drafto",
            description: $description,
            author: $profile->name,
            image: $image,
            url: $url,
            type: 'profile',
            robots: $canIndex ? 'index, follow' : 'noindex, nofollow',
            canonical_url: $url,
        );

        // Sênior: Se o plano permitir SEO E o usuário permitir indexação, adicionamos
        // Dados Estruturados (Schema.org) reais — ProfilePage com a entidade Person.
        if ($canIndex && $profile->user->getModuleSetting(ModuleEnum::PROFILE, 'enable_seo', false)) {
            $seoData->schema = SchemaCollection::initialize()
                ->push(fn (): array => array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'ProfilePage',
                    'mainEntity' => array_filter([
                        '@type' => 'Person',
                        'name' => $displayName,
                        'alternateName' => '@' . $profile->username,
                        'description' => $description,
                        'image' => $image,
                        'url' => $url,
                    ]),
                ]));
        }

        return $seoData;
    }
}
