<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Profile;
use App\Models\User;

function makeProfile(array $attributes = []): Profile
{
    $user = User::factory()->create();

    return Profile::factory()->make(array_merge([
        'user_id' => $user->id,
        'name' => 'Maria',
        'email' => 'maria@example.com',
        'bio' => 'A bio',
        'avatar_path' => 'avatars/a.jpg',
        'cover_path' => 'covers/c.jpg',
        'location' => 'Lisbon',
    ], $attributes));
}

it('returns default colors when none are set', function () {
    $profile = Profile::factory()->make(['primary_color' => null, 'accent_color' => null]);

    $colors = $profile->getColors();

    expect($colors->primary)->toBe('#18181b')
        ->and($colors->accent)->toBe('#3f3f46');
});

it('returns the configured colors via getColors', function () {
    $profile = Profile::factory()->make([
        'primary_color' => '#ffffff',
        'accent_color' => '#000000',
    ]);

    $colors = $profile->getColors();

    expect($colors->primary)->toBe('#ffffff')
        ->and($colors->accent)->toBe('#000000');
});

it('reports no missing critical fields for a complete profile', function () {
    $profile = makeProfile();

    expect($profile->getMissingFields())->toBe([])
        ->and($profile->isComplete())->toBeTrue();
});

it('detects missing critical fields', function () {
    $profile = makeProfile(['name' => '', 'username' => '', 'email' => '']);

    $missing = $profile->getMissingFields();

    expect($missing)->toHaveKeys(['name', 'username', 'email'])
        ->and($profile->isComplete())->toBeFalse();
});

it('includes recommended fields in the recommended missing list', function () {
    $profile = makeProfile([
        'bio' => '',
        'avatar_path' => '',
        'cover_path' => '',
        'location' => '',
    ]);

    $missing = $profile->getRecommendedMissingFields();

    expect($missing)->toHaveKeys(['bio', 'avatar_path', 'cover_path', 'location']);
});

it('computes 100% completion for a fully filled profile', function () {
    $profile = makeProfile();

    expect($profile->getCompletionPercentage())->toBe(100);
});

it('computes a partial completion percentage', function () {
    // 7 fields total, 4 recommended ones missing => 3/7 ~= 42%
    $profile = makeProfile([
        'bio' => '',
        'avatar_path' => '',
        'cover_path' => '',
        'location' => '',
    ]);

    expect($profile->getCompletionPercentage())->toBe(42);
});

it('exposes the @handle accessor', function () {
    $profile = Profile::factory()->make(['username' => 'maria']);

    expect($profile->handle)->toBe('@maria');
});

it('normalizes the username on set', function () {
    $profile = Profile::factory()->make();
    $profile->username = '@MariaSouza';

    expect($profile->username)->toBe('mariasouza');
});

it('falls back to default primary and accent colors via accessors', function () {
    $profile = Profile::factory()->make(['primary_color' => null, 'accent_color' => null]);

    expect($profile->primary_color)->toBe('#18181b')
        ->and($profile->accent_color)->toBe('#3f3f46');
});
