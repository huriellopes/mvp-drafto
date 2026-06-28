<?php

declare(strict_types=1);

namespace Tests\Feature\DTOs;

use App\DTOs\UpdateProfileData;
use App\Enums\ProfileVisibilityEnum;
use App\Enums\ThemePlatformEnum;

function makeProfileData(array $overrides = []): UpdateProfileData
{
    return new UpdateProfileData(...array_merge([
        'name' => 'John Doe',
        'username' => 'johndoe',
        'email' => 'john@example.com',
        'bio' => 'bio',
        'location' => 'BR',
        'website_url' => 'https://example.com',
        'primary_color' => '#111111',
        'accent_color' => '#222222',
        'theme_mode' => ThemePlatformEnum::LIGHT,
        'visibility' => ProfileVisibilityEnum::PUBLIC,
        'show_email_publicly' => false,
        'is_searchable' => true,
    ], $overrides));
}

it('keeps colors that already start with a hash', function () {
    $dto = makeProfileData([
        'primary_color' => '#abcdef',
        'accent_color' => '#fedcba',
    ]);

    expect($dto->primary_color)->toBe('#abcdef')
        ->and($dto->accent_color)->toBe('#fedcba');
});

it('prefixes colors that are missing the hash', function () {
    $dto = makeProfileData([
        'primary_color' => 'abcdef',
        'accent_color' => 'fedcba',
    ]);

    expect($dto->primary_color)->toBe('#abcdef')
        ->and($dto->accent_color)->toBe('#fedcba');
});

it('normalizes the optional secondary color when provided', function () {
    $dto = makeProfileData(['secondary_color' => '00ff00']);

    expect($dto->secondary_color)->toBe('#00ff00');
});

it('leaves the secondary color null when not provided', function () {
    expect(makeProfileData()->secondary_color)->toBeNull();
});

it('applies the documented style defaults', function () {
    $dto = makeProfileData();

    expect($dto->button_style)->toBe('rounded-md')
        ->and($dto->card_style)->toBe('bordered')
        ->and($dto->layout_type)->toBe('default')
        ->and($dto->font_family)->toBe('sans')
        ->and($dto->show_subscriber_count)->toBeTrue()
        ->and($dto->show_view_count)->toBeFalse()
        ->and($dto->links)->toBe([]);
});

it('carries the enum value objects', function () {
    $dto = makeProfileData([
        'theme_mode' => ThemePlatformEnum::DARK,
        'visibility' => ProfileVisibilityEnum::PRIVATE,
    ]);

    expect($dto->theme_mode)->toBe(ThemePlatformEnum::DARK)
        ->and($dto->visibility)->toBe(ProfileVisibilityEnum::PRIVATE);
});
