<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\ModuleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Module;
use App\Models\User;
use App\Services\Profile\ProfileSeoGenerator;

function makeProfileUser(array $userAttrs = [], array $profileAttrs = []): User
{
    $user = User::factory()->writer()->withProfile()->create($userAttrs);
    $user->profile->update(array_merge(['is_searchable' => true], $profileAttrs));

    return $user->fresh('profile');
}

it('marks an active, searchable, non-banned profile as indexable', function () {
    $user = makeProfileUser();

    $seo = ProfileSeoGenerator::generate($user->profile);

    expect($seo->robots)->toBe('index, follow');
});

it('marks a non-searchable profile as noindex', function () {
    $user = makeProfileUser(profileAttrs: ['is_searchable' => false]);

    expect(ProfileSeoGenerator::generate($user->profile)->robots)
        ->toBe('noindex, nofollow');
});

it('marks an inactive user profile as noindex', function () {
    $user = makeProfileUser(['status' => UserStatusEnum::SUSPENDED]);

    expect(ProfileSeoGenerator::generate($user->profile)->robots)
        ->toBe('noindex, nofollow');
});

it('marks a banned user profile as noindex', function () {
    $user = makeProfileUser(['banned_until' => now()->addDay()]);

    expect(ProfileSeoGenerator::generate($user->profile)->robots)
        ->toBe('noindex, nofollow');
});

it('does not attach schema when the profile SEO module setting is disabled', function () {
    $user = makeProfileUser();

    expect(ProfileSeoGenerator::generate($user->profile)->schema)->toBeNull();
});

it('attaches Person schema only when indexable and the SEO module setting is enabled', function () {
    $user = makeProfileUser();

    $module = Module::query()->where('slug', ModuleEnum::PROFILE->value)->firstOrFail();
    $user->modules()->syncWithoutDetaching([
        $module->id => ['is_enabled' => true, 'settings' => json_encode(['enable_seo' => true])],
    ]);

    $seo = ProfileSeoGenerator::generate($user->fresh('profile')->profile);

    expect($seo->schema)->not->toBeNull();
});

it('falls back to the username when the profile has no display name', function () {
    $user = makeProfileUser(profileAttrs: ['name' => null]);

    $seo = ProfileSeoGenerator::generate($user->profile);

    expect($seo->title)->toContain('@' . $user->profile->username);
});
