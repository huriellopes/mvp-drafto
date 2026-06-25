<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Actions\Profile\GetSocialPlatformsAction;
use App\DTOs\SocialPlatformData;
use App\Enums\SocialPlatformEnum;
use App\Models\Profile;
use App\Models\ProfileLink;
use App\Models\User;

it('keeps the config and the enum in sync (no drift)', function () {
    $configKeys = array_keys(config('profile_links.platforms'));
    $enumValues = SocialPlatformEnum::values();

    // Toda chave da config tem um case no enum e vice-versa.
    expect(array_diff($configKeys, $enumValues))->toBeEmpty()
        ->and(array_diff($enumValues, $configKeys))->toBeEmpty();
});

it('resolves label, icon and color from the config for every platform', function () {
    foreach (SocialPlatformEnum::cases() as $platform) {
        $meta = config("profile_links.platforms.{$platform->value}");

        expect($platform->label())->toBe($meta['label'])
            ->and($platform->icon())->toBe($meta['icon'])
            ->and($platform->color())->toBe($meta['color']);
    }
});

it('falls back to website for unknown platform values', function () {
    expect(SocialPlatformEnum::tryFrom('myspace'))->toBeNull()
        ->and(SocialPlatformEnum::fallback())->toBe(SocialPlatformEnum::WEBSITE);
});

it('exposes every platform as a DTO through the action', function () {
    $platforms = app(GetSocialPlatformsAction::class)->exec();

    expect($platforms)->toHaveCount(count(SocialPlatformEnum::cases()))
        ->and($platforms->first())->toBeInstanceOf(SocialPlatformData::class);

    $instagram = $platforms->firstWhere('value', 'instagram');
    expect($instagram->label)->toBe('Instagram')
        ->and($instagram->icon)->toBe('instagram')
        ->and($instagram->color)->toBe('#E4405F');
});

it('resolves brand metadata from the model via the enum', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $link = $profile->links()->create(['platform' => 'github', 'url' => 'https://github.com/foo'])->refresh();

    expect($link->platformEnum())->toBe(SocialPlatformEnum::GITHUB)
        ->and($link->icon())->toBe('github')
        ->and($link->brandColor())->toBe('#181717')
        ->and($link->label())->toBe('GitHub');
});

it('resolves a safe fallback when the stored platform is unknown', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $link = $profile->links()->create(['platform' => 'website', 'url' => 'https://example.com']);
    // Simula um valor legado/desconhecido direto no banco, contornando o cast.
    ProfileLink::query()->where('id', $link->id)->update(['platform' => 'orkut']);

    $fresh = ProfileLink::query()->find($link->id);

    expect($fresh->platformEnum())->toBe(SocialPlatformEnum::WEBSITE)
        ->and($fresh->icon())->toBe('link');
});
